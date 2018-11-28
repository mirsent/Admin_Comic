<?php
namespace Home\Controller;
use Think\Controller;

class ReaderController extends Controller {

    /**
     * 消费记录
     */
    public function get_consume_note()
    {
        $cond = [
            'openid' => I('openid'),
            'method' => 2
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '消费记录', $data);
    }

    /**
     * 充值记录
     */
    public function get_recharge_note()
    {
        $cond = [
            'openid' => I('openid'),
            'method' => 1
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '充值记录', $data);
    }
}