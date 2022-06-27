<?php 
    require_once("../functions.php");

    // 校验session域是否开放
    if($open_session==0){
        force_login();
    }

    // 已登陆，重定向
    if(check_session("access_token")){
        redirect($dir_url);
    }
    
    // 正在登录
    $param = $_GET['param'];
    if(isset($param)){
        $identify = json_decode($param,true);  // 取得身份信息
        
        $access_token = $identify['access_token'];

        $basic = m_basic($access_token);  // 取得 basic
        $_SESSION['access_token'] = $access_token;  // token登录，直接存储token
        $_SESSION['baidu_name'] = $basic['baidu_name'];
        $_SESSION['netdisk_name'] = $basic['netdisk_name'];

        // 如果登录的用户，是系统用户，则更新一次系统的identify
        if($basic['uk']==$uk){
            $config['identify'] = $identify;
            save_config();
        }
        // 重定向到首页
        redirect($dir_url);
    }

    // 未登陆，给出登录方法
    $bp3_tag->assign("enc_page_url",$enc_page_url);
    $bp3_tag->assign("grant_url",$grant_url);
    display();
