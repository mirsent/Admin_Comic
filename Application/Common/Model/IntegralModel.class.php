<?php
namespace Common\Model;
use Common\Model\BaseModel;
class IntegralModel extends BaseModel{

    protected $_auto=array(
        array('create_at','get_datetime',1,'callback')
    );

    public function getIntegralNumber($cond=[])
    {
        $data = $this
            ->alias('i')
            ->join('__READER__ r ON r.openid = i.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getIntegralData($cond=[])
    {
        $data = $this
            ->alias('i')
            ->join('__READER__ r ON r.openid = i.openid')
            ->field('i.*,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}