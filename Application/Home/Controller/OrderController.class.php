<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller {
    /**
     * 生成充值订单
     * @param openid 读者openid
     * @param activity_id 活动ID
     * @param channel 渠道
     */
    public function createRechargeOrder()
    {
        $recharge = D('RechargeOrder');
        $recharge->create();

        $activityId = I('activity_id');
        $activityInfo = M('recharge_activity')->find($activityId); // 活动信息

        $recharge->activity_content = $activityInfo['activity_title'];
        $recharge->money = $activityInfo['money'];
        $recharge->amount = $activityInfo['amount'];
        $recharge->gift = $activityInfo['gift'];

        $res = $recharge->add();

        if ($res === false) {
            ajax_return(0, '生成充值订单失败');
        }
        ajax_return(1);
    }
}