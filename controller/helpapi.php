<?php
/**
 * 后台基本帮助接口
 */
require_once("../functions.php");

force_login();

$method = force_get_param("method");

// 导出配置文件
if($method=="getconfig"){
    // 开始下载
    $filename = "../config.php";

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename='.basename($filename)); //文件名
    header("Content-Type: application/zip"); //zip格式的
    header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
    header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小
    @readfile($filename);  // 输出内容

}
// 导入配置文件
elseif($method=="setconfig"){
    // 接收上传的config文件
    $temp_uri = "config_cache.php";
    move_uploaded_file($_FILES["file"]["tmp_name"],$temp_uri);

    // 获取该文件
    $config_cache = require($temp_uri);

    // 尝试合并
    $config = arr2_merge($config_cache,$base);

    // 尝试更新版本号（版本信息以base文件为准）
    $config['version'] = $base['version'];

    save_config();

    unlink($temp_uri);

    build_success();
}
// 还原基础配置
elseif($method=="resetbasic"){
    $config = arr2_merge($base,$config);
    save_config();
    build_success();
}
// 重置系统（删除config）
elseif($method=="resetsys"){

    if (!unlink("../config.php")){
        build_err();
    }
    else{
        build_success();
    }
}
// 整站导出压缩包
elseif($method=="backup"){

    // 是否排除config
    $skip = $_GET['skip'];
    // 指定缓存文件名
    $filename = TEMP_DIR.DIRECTORY_SEPARATOR."bp3-main-back.zip";
    if($skip){
        $filename = TEMP_DIR.DIRECTORY_SEPARATOR."bp3-main.zip";  // 源码名称，用于简单区分是否包含config
    }
    $file_ctime = file_exists($filename)? filectime($filename) : 0;
    if((time() - $file_ctime)>3){  // 判断文件创建时间，在极短时间内不会重复创建
        // 整站备份，zip的子目录为bp3-main
        if(empty($skip) && !file_exists($filename)){
            ExtendedZip::zipTree(get_base_root(), $filename, ZipArchive::CREATE,"bp3-main");
        }
        elseif(!file_exists($filename)){
            ExtendedZip::zipTree(get_base_root(), $filename, ZipArchive::CREATE,"bp3-main",["config.php"]);
        }
        // 文件已存在，则覆盖该文件
        elseif(empty($skip)){
            ExtendedZip::zipTree(get_base_root(), $filename, ZipArchive::OVERWRITE,"bp3-main");
        }else{
            ExtendedZip::zipTree(get_base_root(), $filename, ZipArchive::OVERWRITE,"bp3-main",["config.php"]);
        }
    }
    easy_read_file($filename,true);
    unlink($filename);

}
// 保存配置文件
elseif($method=="savesettings"){
    $check = true;

    $config['site']['title'] = $_POST['s1'];
    $config['site']['subtitle'] = $_POST['s2'];
    $config['user']['name'] = $_POST['s3'];
    $config['user']['pwd'] = $_POST['s4'];
    $config['user']['lock'] = $_POST['s5'];
    $config['connect']['app_id'] = $_POST['s6'];
    $config['connect']['secret_key'] = $_POST['s7'];
    $config['connect']['redirect_uri'] = $_POST['s8'];
    $config['control']['pre_dir'] = $_POST['s9'];
    $config['site']['blog'] = $_POST['s10'];
    $config['site']['github'] = $_POST['s11'];
    $config['baidu']['baidu_account'] = $_POST['s12'];
    $config['baidu']['baidu_pwd'] = $_POST['s13'];
    $config['control']['close_dlink'] = (int)$_POST['s14'];
    $config['control']['close_dload'] = (int)$_POST['s15'];
    $config['control']['open_grant'] = $_POST['s16'];
    $config['identify']['grant_url'] = $_POST['s17'];
    $config['control']['grant_type'] = $_POST['s17s'];
    $config['control']['open_grant2'] = (int)$_POST['s18'];
    $config['control']['open_session'] = $_POST['s19'];
    $config['site']['description'] = $_POST['s20'];
    $config['site']['keywords'] = $_POST['s21'];
    $config['inner']['app_id'] = $_POST['s22'];
    $config['inner']['secret_key'] = $_POST['s23'];
    $config['control']['update_type'] = $_POST['s24'];
    $config['control']['update_url'] = $_POST['s24u'];
//    $config['control']['dn_limit'] = $_POST['s25'];
//    $config['control']['dn_speed'] = $_POST['s26'];
//    if(!is_numeric($_POST['s26'])){
//        $check = false;
//    }
    $config['control']['theme'] = $_POST['s27'];
    $config['mail']['user'] = $_POST['s28'];  // 发送用户
    $config['mail']['pass'] = $_POST['s29'];  // 应用密钥
    $config['mail']['server'] = $_POST['s30'];  // 邮件服务器
    $config['mail']['port'] = $_POST['s31'];  // 邮件端口
    $config['mail']['receiver'] = $_POST['s32'];  // 收件人
    $config['mail']['refresh'] = $_POST['s33'];  // 收件人


    if($check){
        save_config();
        build_success();
    }else{
        build_err("请填写正确的数据格式！");
    }

}
//测试邮件是否正常
elseif($method=="testMail"){
    $res = send_mail("bp3系统测试邮件","这是一封测试邮件，来自您的站点：<a href='$base_url'>{$config['site']['title']}</a>");
    if ($res){
        build_success("邮件发送成功");
    }else{
        build_err("邮件发送失败");
    }
}
else{
    build_err("无效method");
}