<?php
    // 检测更新
    require_once("../functions.php");

    $url = "https://api.github.com/repos/zhufenghua1998/bp3/releases/latest";

    echo easy_file_get_content($url,easy_build_opt("GET",null,["User-Agent:zhufenghua1998"]));
