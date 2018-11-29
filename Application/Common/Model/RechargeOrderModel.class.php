<?php
namespace Common\Model;
use Common\Model\BaseModel;
class RechargeOrderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('recharge_time','get_datetime',1,'callback'),
        array('recharge_date','get_date',1,'callback'),
        array('recharge_month','get_month',1,'callback')
    );

    public function getRechargeNumber($cond=[])
    {
        $data = $this
            ->alias('ro')
            ->join('__READER__ r ON r.openid = ro.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getRechargeData($cond=[])
    {
        $data = $this
            ->alias('ro')
            ->join('__READER__ r ON r.openid = ro.openid')
            ->field('ro.*,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 计算充值金额
     */
    public function caclRechargeMoney($cond=[])
    {
        $cond['status'] = C('ORDER_S_P'); // 已支付
        $data = $this->where($cond)->sum('money');
        return $data ?: 0;
    }

}