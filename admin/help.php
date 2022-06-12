<?php
/**
 * 普通用户帮助文档
 */
    require_once("../functions.php");
    force_login();


    $bp3_tag->assign("grant",$grant);
    $bp3_tag->assign("grant2",$grant2);
    $bp3_tag->assign("open_url",$open_url);
    display();