<?php

// 导入配置文件
    session_start();
    $config = require('../config.php');
    require_once("../functions.php");
    force_login();//强制登录

    // 接收上传的config文件
    $temp_uri = "config_cache.php";
    move_uploaded_file($_FILES["file"]["tmp_name"],$temp_uri);
    
    // 获取该文件
    
    $config_cache = require($temp_uri);
    
    // 尝试合并
    $base = require("../conf_base.php");
    
    $config = $config_cache + $base;
    
    save_config("../config.php");
    
    unlink($temp_uri);
    
    echo '{"errno":0,"errmsg":"success"}';
?>