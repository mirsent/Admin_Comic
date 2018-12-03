<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ReaderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('registered_date','get_date',1,'callback'),
        array('registered_time','get_datetime',3,'callback'),
    );

    public function getReaderNumber($cond){
        $data = $this
            ->alias('r')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getReaderData($cond){
        $data = $this
            ->alias('r')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 指定日期新增用户
     */
    public function getNewReaderNumber($date)
    {
        $cond = [
            'status' => C('STATUS_Y'),
            'registered_date' => $date
        ];
        $data = $this
            ->where($cond)
            ->count();
        return $data;
    }

    /**
     * 截止指定日期全部用户
     */
    public function getAllReaderNumber($date)
    {
        $cond = [
            'status' => C('STATUS_Y'),
            'registered_date' => array('elt', $date)
        ];
        $data = $this
            ->where($cond)
            ->count();
        return $data;
    }

}
