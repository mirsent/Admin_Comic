<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class OrdersController extends AdminBaseController{
    /**
     * 获取充值订单信息
     */
    public function get_recharge_info()
    {
        $ms = D('RechargeOrder');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['order_number|nickname|activity_content'] = array('like', '%'.$search.'%');
        }
        $cond['order_number'] = I('order_number');
        $cond['nickname'] = I('nickname');
        $cond['channel'] = I('channel');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['recharge_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getRechargeNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('order_number '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('activity_content '.$orderDir); break;
                case 3: $ms->order('money '.$orderDir); break;
                case 4: $ms->order('recharge_time '.$orderDir); break;
                case 5: $ms->order('channel '.$orderDir); break;
                case 6: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('recharge_time desc','status');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getRechargeData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取充值记录信息
     */
    public function get_recharge_note()
    {
        $ms = D('RechargeOrder');

        $cond['ro.status'] = C('ORDER_S_P'); // 已支付

        $recordsTotal = $ms->alias('ro')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['nickname'] = array('like', '%'.$search.'%');
        }
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['recharge_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getRechargeNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('recharge_time '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('money '.$orderDir); break;
                case 3: $ms->order('amount '.$orderDir); break;
                case 4: $ms->order('gift '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('recharge_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getRechargeData($cond);

        foreach ($infos as $key => $value) {
            $infos[$key]['recharge_date'] = date('Ymd', strtotime($value['recharge_time']));
        }

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取消费订单信息
     */
    public function get_consume_info()
    {
        $ms = D('ConsumeOrder');

        $cond['co.status'] = C('STATUS_Y');

        $recordsTotal = $ms->alias('co')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['order_number|title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['order_number'] = I('order_number');
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $cond['channel'] = I('channel');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['consume_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getConsumeNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('order_number '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('c.title '.$orderDir); break;
                case 3: $ms->order('chapter '.$orderDir); break;
                case 4: $ms->order('consumption '.$orderDir); break;
                case 5: $ms->order('consume_time '.$orderDir); break;
                case 6: $ms->order('channel '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('consume_time');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getConsumeData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }
}