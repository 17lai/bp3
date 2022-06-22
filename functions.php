<?php
    // 开启session
    session_start();

    /* ------------------ 1.环境检测、引入基本函数等、其他内容等 ----------------------- */

    //定义主配置文件路径
    $config_path = "/config.php";

    //安装前允许访问的路径
    $install_paths = ["/install.php","/install_fast.php","/install_inner.php","/install_config.php"];

    // 引入基本函数
    require_once("inc/fun_core.php");

    // 安装检测
    $install = check_install($config_path,$install_paths);

    // 引入http函数
    require_once("inc/fun_http.php");

    // 引入curl模块
    $check_curl = is_callable("curl_init");
    if($check_curl){
        // curl工具类暂未封装
    }else{
        easy_echo("警告：当前环境不存在curl扩展");
    }

    // 检测网站目录是否可写
    if(!is_writeable(get_base_root())){
        easy_echo("警告：当前程序根目录无写入权限");
    }

    // 程序根目录
    define("BP3_ROOT",get_base_root());
    // 缓存根目录
    define("TEMP_DIR",BP3_ROOT."/temp");
    if(!file_exists(TEMP_DIR)){
        mkdir(TEMP_DIR);
    }

    // 引入百度业务函数
    require_once("inc/fun_baidu.php");

    // 引入zip扩展类
    $check_zip = class_exists("ZipArchive");
    if($check_zip){
        require_once("inc/zip.class.php");
    }else{
        easy_echo("警告：当前环境不存在zip扩展");
    }

    /* -------------------- 2.程序需要定义一些全局变量为基本配置 ---------------------- */

    // 网站根目录url
    $site_url = get_site_url();

    // 程序根目录
    $base_url = get_base_url();

    // 目录url
    $dir_url = get_dir_url();

    // 获取当前页面、到根目录的前缀
    define("CONTENT_PATH",substr($dir_url,strlen($base_url)));

    // 页面url
    $page_url = get_page_url();

    // 页面转码url
    $enc_page_url = urlencode($page_url);

    // 定义登录管理员使用的session
    $user = "user";

    // 登录地址url
    $login_url = get_file_url("/login.php");

    // 后台地址
    $admin_url = get_file_url("/admin/");

    // 取得后台connect地址
    $connect_url = get_file_url('/admin/connect.php');

    //获取刷新接口地址（程序中一般不用，开放给外部定时器调用）
    $refresh_url = get_file_url("/admin/refresh_token.php");

    //获取开放token地址（程序中一般不调用，开放给外部调用）
    $open_url = get_file_url("/open.php");

    // 下载地址
    $dn_url = get_file_url("/dn.php");

    // 后台直链地址
    $dlink_url = get_file_url("/admin/dlink.php");

    //免app授权地址
    $grant = get_file_url("/grant/");

    //内置app授权地址
    $grant2 = get_file_url("/grant2/");

    //免app授权系统刷新地址
    $grant_refresh = get_file_url("/grant/refresh.php");

    //内置app授权系统刷新地址
    $grant2_refresh = get_file_url("/grant2/refresh.php");

    //当前时间
    $time = time();

    //年
    $year = date("Y");

    //服务器IP
    $server_ip = get_server_ip();

    //客户端IP
    $remote_ip = get_remote_ip();

    //是否已登录？
    $check_login = check_session();

    /*  --------------------------  3.主配置信息  -------------------------*/

    //取得基础配置文件
    $base = get_config("/conf_base.php");

    //配置信息
    $config = array();
    if(defined("REFRESH_SESSION")){
        $config = get_config("",true);
    }else{
        $config = get_config();
    }

    //版本
    $version = $config['version'] ?? $base['version'];

    //用户信息
    $lock   = $config['user']['lock'] ?? $base['user']['lock'];
    $chance = $config['user']['chance'] ?? $base['user']['chance'];
    $name   = $config['user']['name'] ?? $base['user']['name'];
    $pwd    = $config['user']['pwd']  ??  $base['user']['pwd'];
    //站点信息
    $title          = $config['site']['title'] ?? $base['site']['title'];
    $subtitle       = $config['site']['subtitle'] ?? $base['site']['subtitle'];
    $blog           = $config['site']['blog'] ?? $base['site']['blog'];
    $github         = $config['site']['github'] ?? $base['site']['github'];
    $description    = $config['site']['description'] ?? $base['site']['description'];
    $keywords       = $config['site']['keywords'] ?? $base['site']['keywords'];
    //权限控制信息
    $pre_dir        = $config['control']['pre_dir'] ?? $base['control']['pre_dir'];
    $close_dlink    = $config['control']['close_dlink'] ?? $base['control']['close_dlink'];
    $close_dload    = $config['control']['close_dload'] ?? $base['control']['close_dload'];
    $open_grant     = $config['control']['open_grant']  ?? $base['control']['open_grant'];
    $open_grant2    = $config['control']['open_grant2'] ?? $base['control']['open_grant2'];
    $open_session   = $config['control']['open_session'] ?? $base['control']['open_session'];
    $grant_type     = $config['control']['grant_type'] ?? $base['control']['grant_type'];
    $update_type    = $config['control']['update_type'] ?? $base['control']['update_type'];
    $update_url     = $config['control']['update_url']  ?? $base['control']['update_url'];
    $dn_limit       = $config['control']['dn_limit']  ?? $base['control']['dn_limit'];
    $dn_speed       = $config['control']['dn_speed']  ?? $base['control']['dn_speed'];
    $theme          = $config['control']['theme']  ?? $base['control']['theme'];

    //内置信息, inner
    $inner_app_key      = $config['inner']['app_id']   ?? $base['inner']['app_id'];
    $inner_secret_key   = $config['inner']['secret_key']   ?? $base['inner']['secret_key'];
    $inner_redirect_uri = $config['inner']['redirect_uri'] ?? $base['inner']['redirect_uri'];

    //免app信息，connect
    $app_key        = $config['connect']['app_id'] ?? "";
    $secret_key     = $config['connect']['secret_key'] ?? "";
    $redirect_uri   = $config['connect']['redirect_uri'] ?? "";

    //身份信息identify
    $access_token = "";
    if(defined('TOKEN_REFRESH')){  // 如果在引入前，定义了常量 TOKEN_REFRESH，则强制刷新
        $access_token = m_token_refresh(null,true);
    }else{
        $access_token       = m_token_refresh(); // 同时自动检测有效期
    }
    $expires_in         = $config['identify']['expires_in'] ?? 0;
    $refresh_token      = $config['identify']['refresh_token'] ?? "";
    $session_secret     = $config['identify']['session_secret'] ?? "";
    $session_key        = $config['identify']['session_key'] ?? "";
    $scope              = $config['identify']['scope'] ?? "";
    $grant_url          = $config['identify']['grant_url'] ?? "";
    $refresh_url        = $config['identify']['refresh_url'] ?? "";
    $conn_time          = $config['identify']['conn_time'] ?? 0;

    //basic，连接基础信息
    $baidu_name   = $config['basic']['baidu_name']??"";
    $netdisk_name = $config['basic']['netdisk_name']??"";
    $uk           = $config['basic']['uk']??-1;
    $vip_type     = $config['basic']['vip_type']??-1;

    //绑定的百度信息，account
    $a_baidu_name   = $config['account']['baidu_name']??"";
    $a_netdisk_name = $config['account']['netdisk_name']??"";
    $a_uk           = $config['account']['uk']??  -1;
    $a_vip_type     = $config['account']['vip_type']??-1;

    //自定义存储百度信息，baidu
    $baidu_account      = $config['baidu']['baidu_account']??  "";
    $baidu_pwd          = $config['baidu']['baidu_pwd']?? "";

    /* -------------------  4.常用业务处理  --------------------*/
    // 编码后的connect地址
    $enc_connect_url = urlencode($connect_url);
    // 后台连接授权地址
    $connect_grant_url      = "$grant_url?display=$enc_connect_url";
    // 编码后的绑定百度登录地址
    $enc_bind_account_url   = urlencode(get_file_url("/controller/bind_account.php"));
    // 快速绑定百度登录授权地址
    $bind_account_grant_url = "$grant_url?display=$enc_bind_account_url";

    define("isAdmin",isAdmin());

    // 引入模板
    require_once("inc/bp3_tag.class.php");
    define("THEME_DIR",get_base_root()."/themes"); // 主题目录
    $themes = lsThemes();
    define("THEME",$theme);  // 当前主题
    $bp3_tag = new bp3_tag();

    // 注册config
    $bp3_tag->assign("config",$config);
    // 注册base
    $bp3_tag->assign("base",$base);
    // 其他
    $bp3_tag->assign('app_name', $title);
    $bp3_tag->assign("app_subtitle",$subtitle);
    $bp3_tag->assign("app_blog",$blog);
    $bp3_tag->assign("app_github",$github);
    $bp3_tag->assign("app_description",$description);
    $bp3_tag->assign("app_keywords",$keywords);
    $bp3_tag->assign("year",$year);
    $bp3_tag->assign("check_login",$check_login);







