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

}