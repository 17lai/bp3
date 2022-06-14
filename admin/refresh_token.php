<?php
    // 刷新token接口
    require_once("../functions.php");
    // 调用内置函数，强制刷新
    $token = m_token_refresh(null,true);

    if(check_session()){  // 已登录

        if($token){
            build_success($token);
        }else{
            build_err("刷新失败");
        }
    }else{  // 未登录

        if($token){
            build_success("刷新成功");
        }else{
            build_err("刷新失败");
        }
    }


