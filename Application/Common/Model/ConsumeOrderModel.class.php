<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ConsumeOrderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('consume_time','get_datetime',1,'callback'),
        array('consume_date','get_date',1,'callback'),
        array('consume_month','get_month',1,'callback')
    );

    public function getConsumeNumber($cond=[])
    {
        $data = $this
            ->alias('co')
            ->join('__READER__ r ON r.id = co.reader_id')
            ->join('__COMICS__ c ON c.id = co.comic_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getConsumeData($cond=[])
    {
        $data = $this
            ->alias('co')
            ->join('__READER__ r ON r.id = co.reader_id')
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
        $data = $this->where($cond)->field('id')->distinct(true)->select();
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

    /**
     * 消费金额
     */
    public function caclConsumeMoney($cond=[])
    {
        $data = $this
            ->alias('co')
            ->join('__READER__ r ON r.id = co.reader_id')
            ->where($cond)
            ->sum('consumption');
        return $data?:'0.00';
    }

}