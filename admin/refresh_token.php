<?php
    // 刷新token接口
    define("TOKEN_REFRESH","1");  // 定义常量，再引入functions，即可强制刷新
    require_once("../functions.php");
    // 调用内置函数，强制刷新
    if(check_session()){  // 已登录

        if($access_token){
            build_success($access_token);
        }else{
            build_err("刷新失败");
        }
    }else{  // 未登录

        if($access_token){
            build_success("刷新成功");
        }else{
            build_err("刷新失败");
        }
    }


