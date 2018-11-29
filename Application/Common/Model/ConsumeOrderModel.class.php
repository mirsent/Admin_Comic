<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ConsumeOrderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('consume_time','get_datetime',1,'callback')
    );

    public function getConsumeNumber($cond=[])
    {
        $data = $this
            ->alias('co')
            ->join('__READER__ r ON r.openid = co.openid')
            ->join('__COMICS__ c ON c.id = co.comic_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getConsumeData($cond=[])
    {
        $data = $this
            ->alias('co')
            ->join('__READER__ r ON r.openid = co.openid')
            ->join('__COMICS__ c ON c.id = co.comic_id')
            ->field('co.*,r.nickname,c.title as comic_title')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 指定日期付费人数
     */
    public function caclConsumePerson($date)
    {
        $cond['consume_date'] = $date;
        $data = $this->where($cond)->field('openid')->distinct(true)->select();
        return count($data);
    }

    /**
     * 指定日期付款笔数
     */
    public function caclConsumeNumber($date)
    {
        $cond['consume_date'] = $date;
        $data = $this->where($cond)->count();
        return $data;
    }

}