<?php


// 全自动更新  http://bp3-plus.52dixiaowo.com/bp3-main.zip

    require_once("../functions.php");
    
    force_login();

    // 下载最新版代码
    $fp = easy_file_get_content($update_url);
    
    $temp_uri = TEMP_DIR.DIRECTORY_SEPARATOR."bp3-main.zip";
    
    file_put_contents($temp_uri,$fp);
    
    // 调用自动更新代码
    $type = os_type();
    
    require("./up_core_$type.php");
