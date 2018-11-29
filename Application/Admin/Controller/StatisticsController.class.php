<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class StatisticsController extends AdminBaseController{
    /**
     * 当月汇总统计
     */
    public function get_index_statistics()
    {
        $recharge = D('RechargeOrder');
        $consume = D('ConsumeOrder');
        $reader = D('Reader');

        $search = I('search_date');
        if ($search) {
            $dateArr = explode(' - ',$search);
            $date_s = strtotime($dateArr[0]);
            $date_e = strtotime($dateArr[1]);
        } else {
            $date_s = strtotime(date('Y-m-01'));
            $date_e = strtotime(date('Y-m-t'));
        }

        while ($date_s<=$date_e){
            $date = date('Y-m-d',$date_e);
            $cond_recharge['recharge_date'] = $date;

            $data[] = [
                'date' => $date,
                'recharge' => $recharge->caclRechargeMoney($cond_recharge), // 充值金额
                'consume_person' => $consume->caclConsumePerson($date), // 付费人数
                'consume_number' => $consume->caclConsumeNumber($date), // 付款笔数
                'reader_new' => $reader->getNewReaderNumber($date), // 新增用户
                'reader_all' => $reader->getAllReaderNumber($date), // 累计用户
            ];
            $date_e = strtotime('-1 day',$date_e);
        }

        echo json_encode([
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }
}