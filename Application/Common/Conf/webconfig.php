<?php
return array(

    'TITLE'             => '漫画管理系统',

    /*认证相关*/
    'USER_AUTH_KEY'     => 'lte', // 用户认证SESSION标记
    'USER_AUTH_GATEWAY' => 'Public/login', // 默认认证网关

    /*状态*/
    'STATUS_N' => 0, // 删除状态
    'STATUS_Y' => 1, // 正常状态
    'STATUS_B' => 2, // 禁用状态

    /*漫画状态*/
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

);
