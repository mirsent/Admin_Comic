<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller {

    /**
     * 获取充值活动信息
     */
    public function get_recharge_activity()
    {
        $cond['status'] = C('STATUS_Y');
        $data = M('recharge_activity')->where($cond)->select();
        ajax_return(1, '充值活动信息', $data);
    }

    /**
     * 生成订单，状态为未支付
     * @param openid 读者openid
     * @param comic_id 漫画ID
     * @param chapter 章节 记录充值漫画
     * @param activity_id 活动ID
     * @param channel 渠道
     */
    public function create_recharge_order()
    {
        // 1.生成订单
        $recharge = D('RechargeOrder');
        $recharge->create();

        $activityId = I('activity_id');
        $activityInfo = M('recharge_activity')->find($activityId); // 活动信息

        $recharge->order_number = generateOrderNo(C('ORDER_R'));
        $recharge->comic_id = I('comic_id');
        $recharge->chapter = I('chapter');
        $recharge->activity_content = $activityInfo['activity_title'];
        $recharge->money = $activityInfo['money'];
        $recharge->amount = $activityInfo['amount'];
        $recharge->gift = $activityInfo['gift'];

        $res = $recharge->add();

        if ($res === false) {
            ajax_return(0, '生成订单失败');
        }
        ajax_return(1);
    }
}