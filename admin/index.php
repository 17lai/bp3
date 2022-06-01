<?php
    require_once("../functions.php");
    
    force_login();

    // 统计授权信息时间
    $warning = false;
    $express_day = 0;
    if(isset($config['identify'])){
        $pass_time = time()-$config['identify']['conn_time'];
        $express_time = $config['identify']['expires_in']-$pass_time;
        if($express_time<1296000){ //有效期小于15天，给出警告
            $warning = true;
            $express_day = number_format($express_time/3600/24, 2);
        }
    }

    $bp3_tag->assign("warning",$warning);
    $bp3_tag->assign("express_day",$express_day);

    $bp3_tag->assign("baidu_name",$baidu_name);
    $bp3_tag->assign("netdisk_name",$netdisk_name);
    $bp3_tag->assign("uk",$uk);
    $bp3_tag->assign("vip_type",m_str_vip($vip_type));

    $bp3_tag->assign("connect_grant_url",$connect_grant_url);
    $bp3_tag->assign("bind_account_grant_url",$bind_account_grant_url);


    $bp3_tag->assign("a_baidu_name",$a_baidu_name);
    $bp3_tag->assign("a_netdisk_name",$a_netdisk_name);
    $bp3_tag->assign("a_uk",$a_uk);
    $bp3_tag->assign("a_vip_type",m_str_vip($a_vip_type));

    display();
