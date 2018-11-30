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

    /**
     * 订单统计页面
     */
    public function orders()
    {
        $today = date('Y-m-d');
        $month = date('Y-m');

        $recharge = D('RechargeOrder');
        $cond_recharge_d['recharge_date'] = $today;
        $cond_recharge_m['recharge_month'] = $month;

        // 充值金额
        $d['money'] = $recharge->caclRechargeMoney($cond_recharge_d);
        $m['money'] = $recharge->caclRechargeMoney($cond_recharge_m);
        $a['money'] = $recharge->caclRechargeMoney();

        // 总订单
        $d['n_all'] = $recharge->where($cond_recharge_d)->count();
        $m['n_all'] = $recharge->where($cond_recharge_m)->count();
        $a['n_all'] = $recharge->count();

        // 已支付
        $cond_pay['status'] = C('ORDER_S_P');
        $d['n_pay'] = $recharge->where($cond_recharge_d)->where($cond_pay)->count();
        $m['n_pay'] = $recharge->where($cond_recharge_m)->where($cond_pay)->count();
        $a['n_pay'] = $recharge->where($cond_pay)->count();

        // 未支付
        $d['n_no'] = $d['n_all'] - $d['n_pay'];
        $m['n_no'] = $m['n_all'] - $m['n_pay'];
        $a['n_no'] = $a['n_all'] - $a['n_pay'];

        // 完成率
        $d['rate'] = $d['n_all'] == 0 ? 100 : $d['n_pay']/$d['n_all']*100;
        $m['rate'] = $m['n_all'] == 0 ? 100 : $m['n_pay']/$m['n_all']*100;
        $a['rate'] = $a['n_all'] == 0 ? 100 : $a['n_pay']/$a['n_all']*100;

        $this->assign(compact('d','m','a'));
        $this->display();
    }

    /**
     * 获取漫画排行信息
     */
    public function get_comic_rank_info()
    {
        $ms = D('Comics');
        $cond['status'] = C('STATUS_Y');
        $title = I('title');
        if ($title) {
            $cond['title'] = array('like', '%'.$title.'%');
        }
        $infos = $ms->page($page, $limit)->getComicRank($cond);
        echo json_encode(array(
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }
}