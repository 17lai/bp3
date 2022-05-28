<?php
    require('./functions.php');

    if($check_login){
        $action = '管理';
    }else{
        $action = '登录';
    }

    // 捕获dir查询参数
    $dir = isset($_GET['dir'])?$_GET['dir']: ''; // 少了前缀
    $real_dir = "";  // 真实路径
    // 捕获查询参数
    $key = isset($_GET['s'])?$_GET['s']: null;
    // 捕获分页参数
    $page = empty($_GET['page'])? 1 : $_GET['page'];
    // data数据，优先查询，然后是dir
    $data = null;
    if(isset($key)){
        $data = m_file_search($pre_dir,$page,$key,$access_token);
    }else{
        //处理前台路径
        if(!$dir){ // 访问网页首页
            if($pre_dir==""){
                $dir = "/";
                $real_dir="/";
            }else{
                $real_dir = $pre_dir;
            }
        }else{
            $real_dir = $pre_dir.$dir;
        }
        $data = m_file_list($real_dir,$access_token);
    }
    // 是否还有下一页(仅搜索接口）
    $has_more = isset($data['has_more'])?$data['has_more']:null;

    $bp3_tag->assign("action",$action);

    $nav = array();
    if($dir!=""){ // 非根目录，一个或多个
        $dirs = explode('/',$dir); // 取得路径
        $dir_path = '';  // 新的访问路径
        $dir_paths = [null,]; // 存储新路径组
        for($i=1;$i<count($dirs);$i++){
            if($dir[$i]==""){
                continue;
            }
            $dir_path.='/';
            $dir_path.=$dirs[$i];
            $dir_paths[$i] = $dir_path;
            $dir_link = urlencode($dir_path);
            $item = ["enc"=>$dir_link,"name"=>$dirs[$i]];
            array_push($nav,$item);
//            $nav .= "<li><a href='?dir=$dir_link'>$dirs[$i]</a></li>";
        }
    }
    $bp3_tag->assign("nav",$nav);

    $bp3_tag->assign("key",$key);
    // 处理data
    foreach ($data['list'] as & $row){
        if($row['isdir']==1){
            // 去掉前缀
            $path = substr($row['path'],strlen($pre_dir));
            // 编码后的地址
            $encode_path = urlencode($path);
            // 存储变更
            $row['path'] =  $path;
            $row['encode_path'] =  $encode_path;
        }else{
            // 显示大小
            $row['show_size'] = height_show_size($row['size']);
            // 去掉前缀的title
            $row['title'] = substr($row['path'],strlen($pre_dir));
        }
    }
//    easy_dump($nav);

    $bp3_tag->assign("data",$data);

    display();