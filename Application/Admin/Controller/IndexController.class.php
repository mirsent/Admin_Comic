<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class IndexController extends AdminBaseController{
    public function index(){
        $today = date('Y-m-d');
        $month = date('Y-m');

        $recharge = D('RechargeOrder');
        $cond_recharge_d['recharge_date'] = $today;
        $cond_recharge_m['recharge_month'] = $month;

        $rechargeD = $recharge->caclRechargeMoney($cond_recharge_d);
        $rechargeM = $recharge->caclRechargeMoney($cond_recharge_m);
        $rechargeA = $recharge->caclRechargeMoney();

        $this->assign(compact('rechargeD','rechargeM','rechargeA'));
        $this->display();
    }
}
