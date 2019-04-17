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

    /**
     * 购买漫画
     * 1.判断余额
     * 2.生成订单
     * 3.积分明细
     * 4.用户余额
     * @param int reader_id 读者ID
     * @param int comic_id 漫画ID
     * @param int catalog 章节
     */
    public function buy_comic()
    {
        $reader = M('reader');
        $readerId = I('reader_id');
        $comicId = I('comic_id');
        $catalog = I('catalog');

        $readerInfo = $reader->where(['id'=>$readerId])->find();
        $balance = $readerInfo['integral']; // 余额
        $comicInfo = M('comics')->where(['id'=>$comicId])->find();
        $money = $comicInfo['pre_chapter_pay']; // 章节费用

        // 1.判断余额
        if ($balance < $money) {
            ajax_return(9, '积分不足');
        }

        // 2.生成订单
        $order = D('ConsumeOrder');
        $order->create();
        $order->order_number = generateOrderNo(C('ORDER_C'));
        $order->target_type = 1;
        $order->target_id = $comicId;
        $order->consumption = $money;
        $res = $order->add();

        // 3.积分详情
        $data_integral = [
            'reader_id' => $readerId,
            'content'   => '兑换漫画《'.$comicInfo['title'].'》第'.$catalog.'章',
            'method'    => 2,
            'points'    => $money,
            'create_at' => date('Y-m-d H:i:s')
        ];
        M('integral')->add($data_integral);

        // 4.用户余额
        $reader->where(['id'=>$readerId])->setDec('integral', $money);

        if ($res === false) {
            ajax_return(0, '购买失败');
        }
        ajax_return(1);
    }

    /**
     * 购买小说
     * 1.判断余额
     * 2.生成订单
     * 3.积分明细
     * 4.用户余额
     * @param int reader_id 读者ID
     * @param int novel_id 小说ID
     * @param int catalog 章节
     */
    public function buy_novel()
    {
        $reader = M('reader');
        $readerId = I('reader_id');
        $novelId = I('novel_id');
        $catalog = I('catalog');

        $readerInfo = $reader->where(['id'=>$readerId])->find();
        $balance = $readerInfo['integral']; // 余额
        $novelInfo = M('novel')->where(['id'=>$novelId])->find();
        $money = $novelInfo['pre_chapter_pay']; // 章节费用

        // 1.判断余额
        if ($balance < $money) {
            ajax_return(9, '积分不足');
        }

        // 2.生成订单
        $order = D('ConsumeOrder');
        $order->create();
        $order->order_number = generateOrderNo(C('ORDER_C'));
        $order->target_type = 2;
        $order->target_id = $novelId;
        $order->consumption = $money;
        $res = $order->add();

        // 3.积分详情
        $data_integral = [
            'reader_id' => $readerId,
            'content'   => '兑换小说《'.$novelInfo['title'].'》第'.$catalog.'章',
            'method'    => 2,
            'points'    => $money,
            'create_at' => date('Y-m-d H:i:s')
        ];
        M('integral')->add($data_integral);

        // 4.用户余额
        $reader->where(['id'=>$readerId])->setDec('integral', $money);

        if ($res === false) {
            ajax_return(0, '购买失败');
        }
        ajax_return(1);
    }
}