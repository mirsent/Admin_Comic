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

    ////////
    // 画册 //
    ////////

    public function get_gather_info()
    {
        $cond['g.status'] = C('APPLY_P');
        $data = D('Gather')->order('publish_time desc')->getGatherData($cond);
        ajax_return(1, '画册', $data);
    }

    /**
     * 发布画册
     */
    public function publish_gather()
    {
        $gather = D('Gather');
        $gather->create();
        $res = $gather->add();

        if ($res === false) {
            ajax_return(0, '发布画册失败');
        }
        ajax_return(1);
    }
}