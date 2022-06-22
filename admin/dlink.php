<?php
    require_once('../functions.php');
/**
 *  文件直链模块，访客权限使用时需管理员开启
 */
    // 1.获取fsid
    $fsid = force_get_param("fsid");

    if($close_dlink!=0){
        force_login();  //强制登录
    }
    
    $info = m_file_info($access_token,$fsid);
    
    $dlink =  $info['list'][0]['dlink'];
    $file_size = $info['list'][0]['size'];
    $file_name = $info['list'][0]['filename'];
    $bp3_tag->assign("file_name",$file_name);

    $dlink = $dlink.'&access_token='.$access_token;
    $bp3_tag->assign("dlink",$dlink);

    $show_size = height_show_size($file_size);
    $bp3_tag->assign("show_size",$show_size);

    $check_ua = $_SERVER['HTTP_USER_AGENT']=="pan.baidu.com"?"text-success":"text-danger";
    $bp3_tag->assign("ua",$_SERVER['HTTP_USER_AGENT']);
    $bp3_tag->assign("check_ua",$check_ua);

	$realLink = m_redirect_dlink($dlink);
	$bp3_tag->assign("realLink",$realLink);

	$client_link = $realLink."&filename=|".$file_name;
	$bp3_tag->assign("client_link",$client_link);

	$bp3_tag->assign("current",date("Y-m-d H:i:s"));

	display();