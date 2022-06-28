<?php

    // session域解析版
    require_once("../functions.php");

    // 未登录，重定向至登录页面
    if(!check_session("access_token")){
        redirect("./login.php");
    }
    // 正在注销
    if($_GET['logout']){
        $_SESSION['access_token'] = null;
        redirect("./login.php");
    }

    $bp3_tag->assign("baidu_name",$_SESSION['baidu_name']);
    $bp3_tag->assign("netdisk_name",$_SESSION['netdisk_name']);

    display();