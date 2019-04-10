<?php
return array(

    /*附加设置*/
    'LOAD_EXT_CONFIG'   => 'db,webconfig',                  // 加载扩展配置文件
    'TMPL_PARSE_STRING' => array(                           // 模板相关配置
        '__PUBLIC__'    => __ROOT__.'/Public',
        '__BOWER__'     => __ROOT__.'/Public/bower_components',
        '__STATICS__'   => __ROOT__.'/Public/statics',

        '__HOME_CSS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/css',
        '__HOME_JS__'   => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/js',
        '__HOME_IMG__'  => __ROOT__.trim(TMPL_PATH,'.').'/Home/Public/img',
        '__ADMIN_CSS__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/css',
        '__ADMIN_JS__'  => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/js',
        '__ADMIN_IMG__' => __ROOT__.trim(TMPL_PATH,'.').'/Admin/Public/img',
    ),

    /*auth设置*/
    'AUTH_CONFIG'       => array(
        'AUTH_USER'     => 'user'                           // 用户信息表
    ),

    //***********************************邮件服务器**********************************

    'EMAIL_FROM_NAME'        => 'iman',   // 发件人
    'EMAIL_SMTP'             => 'smtp.qq.com',   // smtp
    'EMAIL_USERNAME'         => 'mirsent@vip.qq.com',   // 账号
    'EMAIL_PASSWORD'         => 'meztzbulysmtbffa',   // 密码  注意: 163和QQ邮箱是授权码；不是登录的密码
    'EMAIL_SMTP_SECURE'      => 'ssl',   // 链接方式 如果使用QQ邮箱；需要把此项改为  ssl
    'EMAIL_PORT'             => '465', // 端口 如果使用QQ邮箱；需要把此项改为  465

);
