<?php
namespace Common\Model;
use Common\Model\BaseModel;
class WishModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('wish_time','get_datetime',1,'callback')
    );

    public function getWishData($cond=[])
    {
        $data = $this
            ->alias('w')
            ->join('__READER__ r ON r.id = w.reader_id')
            ->field('w.*,nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}