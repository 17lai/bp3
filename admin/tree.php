<?php
    require_once("../functions.php");

    force_login();  // 强制登录

    $base_dir = force_get_param("base_dir");
    // 一次至多查找10000条
    $limit = 10000;

    // 最大目录层级3层（仅在数据量超过限定数量时，此限制生效，如果数量在可控范围内则无效）
    $limit_deep = 3;

    $is_beyond_deep = false;
    
    $encode_dir = urlencode($base_dir);
    $bp3_tag->assign("base_dir",$base_dir);
    
    $url = "http://pan.baidu.com/rest/2.0/xpan/multimedia?method=listall&path=$encode_dir&access_token=$access_token&order=name&recursion=1&limit=$limit";
    
    $result = easy_file_get_content($url);

    $arr = m_decode($result);


    $record = count($arr['list']);
    $has_more = (bool)$arr['has_more'];
    $bp3_tag->assign("record",$record);  // 取得的记录数
    $bp3_tag->assign("has_more",$has_more);  // 取得的记录数

    $pathStr = explode("/",$base_dir); // 分割根目录
    $base_dir_count = count($pathStr);  // 根目录层级，只记录数量，如 /apps/share，这里取得 2
    $lastPath = $pathStr[count($pathStr)-1];  // 取得目录名称，用于展示，如 /apps/share ，则这里取得 share

    $bp3_tag->assign("lastPath",$lastPath);
    $all_size = 0;  // 根目录大小
    
    // 提取目录
    $dir_arr = [];  // 这个数组，存储所有目录

    $dir_arr[$base_dir] = ['name'=>$lastPath,'deep'=>1,'size'=>0];  // 根目录，深度为 1

    $count_dir = 0;
    foreach ($arr['list'] as $row){
        if($row['isdir']){
            $count_dir++;
            // 生成子目录info
            $deep = count(explode("/",$row['path']))-$base_dir_count+1;  // 计算子目录层级数，深度动态计算得到

            if($has_more && $deep>$limit_deep){  // 如果还有更多数据，且大于限定层级，说明数据量非常大，丢弃部分数据否则容易造成卡死
                $is_beyond_deep = true;
                continue;
            }
            if($count_dir)

            $dir_info = ['name'=>$row['server_filename'],'deep'=>$deep,'size'=>0,'parent'=>0];
            
            $dir_arr[$row['path']] = $dir_info;
        }
    }
    // 前面得到的数据，没有层级关系，现在开始为每个目录寻找父目录，并记录最大层次
    $max_dir=0;
    foreach($dir_arr as $key=>$value)
    {
        // 并记录最大层次
        if($max_dir<$value['deep']){
            $max_dir=$value['deep'];
        }
        // 初始化大小为0
        $dir_arr[$key]['size']=0;
        // 2层以下的父目录均为base_dir
        if($value['deep']<=2){
            $dir_arr[$key]['parent'] = $base_dir;
        }
        // 3层以上的父目录，需要手动寻找上一层
        else{
            // 遍历找出上一层的所有目录
            $before = $value['deep']-1;
            $before_arr = [];
            foreach($dir_arr as $key2=>$value2)
            {
                if($value2['deep']==$before){
                    $before_arr[$key2] = $value2;
                }
            }
            // 遍历该目录，如果该目录中任意一个被当前包含，则说明是当前父目录
            foreach($before_arr as $key3=>$value3)
            {
                if(strpos($key,$key3)!==false){
                    $dir_arr[$key]['parent'] = $key3;
                }
            }
        }
    }

    // 前面得到了层级关系，但是数组本身没有结构关系，现在开始重新排序目录
    $dir_sort = [];

    $dir_sort[$base_dir] = $dir_arr[$base_dir];

    foreach($dir_arr as $key=>$value)
    {
        //从第2层开始，如果有子目录，并找到其子目录
        $start_loop = 2;
        if($value['deep']==$start_loop){

            // 把第2层加入
            $dir_sort[$key] = $dir_arr[$key];
            autoCeil($dir_arr,$start_loop,$key,$dir_sort,$max_dir);
        }
    }

// 递归添加层数
    function autoCeil(& $dir_arr,$start_loop,$key,& $dir_sort,$max_dir){
        if($start_loop>$max_dir){
            return;
        }
        foreach($dir_arr as $key2=>$value2)
        {
            // 查询第三层中，和当前第2层符合父子关系的目录
            if($value2['deep']==$start_loop+1 && $key==$value2['parent']){
                
                $dir_sort[$key2] = $dir_arr[$key2];
                autoCeil($dir_arr,$start_loop+1,$key2,$dir_sort,$max_dir);
            }
        }
    }
    // 目录排序完毕
    $dir_arr = $dir_sort;

    // 给文件夹添加文件，并计算文件夹大小
    foreach ($arr['list'] as $row){
        
        if(!$row['isdir']){
            $deep = count(explode("/",$row['path']))-$base_dir_count+1;  // 计算子目录层级数，深度动态计算得到
            if($has_more && $deep>$limit_deep+1){ // 层级太深的，丢弃；
                continue;
            }
            // 累计根目录文件夹总大小
            $all_size += $row['size'];
            // 查找文件最后一个 / ，以识别它所在的目录
            $index = strrpos($row['path'],"/");
            $dir_path = substr($row['path'],0,$index);
            // 根据它所在的目录，累计其文件夹大小
            $dir_arr[$dir_path]['size'] += $row['size'];
            // 根据所在目录，把文件添加到其list属性中
            $dir_arr[$dir_path]['list'][$row['server_filename']] = $row['size'];
            
        }
    }

    // 递归算法，从最末层次开始，累计父文件夹大小
    fixedDir($dir_arr,$max_dir,$max_dir);
    function fixedDir($dir_arr,$current,$max_dir){
        global $dir_arr;
        if($current<1){
            return;
        }else{
            // 让当前层次的所有大小添加到其父目录中去
            foreach($dir_arr as $key=>$value)
            {
                if($value['deep']==$current && $value['parent']!=$key){
                    $dir_arr[$value['parent']]['size'] += $value['size'];
                }
            }
        }
        fixedDir($dir_arr,$current-1,$max_dir);
    }
    $bp3_tag->assign("all_size",$all_size); // 根文件夹总大小
    $bp3_tag->assign("max_dir",$max_dir);  // 最高层次
    $bp3_tag->assign("is_beyond_deep",$is_beyond_deep);  // 最高层次
    $bp3_tag->assign("data",$dir_arr);  // 数据
    display();

