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
            ->join('__READER__ r ON r.id = ro.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getRechargeData($cond=[])
    {
        $data = $this
            ->alias('ro')
            ->join('__READER__ r ON r.id = ro.reader_id')
            ->join('__COMICS__ c ON c.id = ro.comic_id')
            ->field('ro.*,r.nickname,title')
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
        return $data ?: '0.00';
    }

    /**
     * 计算代理充值金额
     */
    public function caclProxyRechargeMoney($cond=[])
    {
        $cond['ro.status'] = C('ORDER_S_P');
        $data = $this
            ->alias('ro')
            ->join('__READER__ r ON r.id = ro.reader_id')
            ->where($cond)
            ->sum('ro.money');
        return $data ?: '0.00';
    }

}