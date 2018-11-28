<?php
return array(

    'TITLE'             => '漫画管理系统',

    /*认证相关*/
    'USER_AUTH_KEY'     => 'lte', // 用户认证SESSION标记
    'USER_AUTH_GATEWAY' => 'Public/login', // 默认认证网关

    'WX_CONFIG' => array(
        'APPID'      => 'wx4af7990f8faed2af',
        'APPSECRET'  => 'bf678846c87c828fe51f008ffd716baa',
        'MCHID'      => '',
        'KEY'        => '',
        'NOTIFY_URL' => '',
        'money'      => 1
    ),

    'FILTER_TEXT' => '我爱iman',

    /*状态*/
    'STATUS_N' => 0, // 删除状态
    'STATUS_Y' => 1, // 正常状态
    'STATUS_B' => 2, // 禁用状态

    'ORDER_S_W' => 1, // 待支付
    'ORDER_S_P' => 2, // 已支付
    'ORDER_S_C' => 3, // 已取消

    'INDEX_SHOW' => 4, // 首页显示漫画数量

    'C_SERIAL_L' => 1, // 连载中
    'C_SERIAL_W' => 2, // 已完结

    'C_FEE_Y' => 1, // 收费
    'C_FEE_N' => 2, // 免费

    'C_TARGET_M' => 1, // 男频
    'C_TARGET_F' => 2, // 女频

    'C_SPACE_C' => 1, // 长篇
    'C_SPACE_D' => 2, // 短篇

    'C_STATUS_U' => 1, // 上架
    'C_STATUS_D' => 2, // 下架
    'C_STATUS_N' => 3, // 未开放

    'CHANNEL_G' => 1, // 公众号
    'CHANNEL_X' => 2, // 小程序

    'ORDER_R' => 1, // 充值
    'ORDER_C' => 2, // 消费

);
