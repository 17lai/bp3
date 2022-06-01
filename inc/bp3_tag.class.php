<?php

require('smarty/Smarty.class.php');

class bp3_tag extends Smarty {

    function __construct()
    {
        parent::__construct();
        define("BP3_TAG_TEMP",TEMP_DIR."/tag");
        if(!file_exists(BP3_TAG_TEMP)){
            mkdir(BP3_TAG_TEMP);
        }

        $this->left_delimiter = "{#";  // 起始标记
        $this->right_delimiter = "#}"; // 终止标记
        $this->caching = Smarty::CACHING_LIFETIME_CURRENT;

        define("BP3_TEMPLATE_DIR",BP3_ROOT.'/themes/'.THEME.'/tpl/');
        $this->setTemplateDir(BP3_TEMPLATE_DIR);
        $this->setCompileDir(BP3_TAG_TEMP.'/templates_c/');
        $this->setConfigDir(BP3_TAG_TEMP.'/configs/');
        $this->setCacheDir(BP3_TAG_TEMP.'/cache/');

        // 如果是 admin 目录
        if(isAdmin){
            define("BP3_ADMIN_TAG_TEMP",TEMP_DIR."/admin_tag");
            if(!file_exists(BP3_ADMIN_TAG_TEMP)){
                mkdir(BP3_ADMIN_TAG_TEMP);
            }
            $this->setTemplateDir(BP3_ROOT.'/admin/template/');
            $this->setCompileDir(BP3_ADMIN_TAG_TEMP.'/templates_c/');
            $this->setConfigDir(BP3_ADMIN_TAG_TEMP.'/configs/');
            $this->setCacheDir(BP3_ADMIN_TAG_TEMP.'/cache/');
        }
    }

}

