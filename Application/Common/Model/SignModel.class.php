<?php
namespace Common\Model;
use Common\Model\BaseModel;
class SignModel extends BaseModel{
    protected $_auto=array(
        array('create_at','get_datetime',1,'callback')
    );

    public function getSignNumber($cond=[])
    {
        $data = $this
            ->alias('s')
            ->join('__READER__ r ON r.id = s.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getSignData($cond=[])
    {
        $data = $this
            ->alias('s')
            ->join('__READER__ r ON r.id = s.reader_id')
            ->field('s.*,nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}