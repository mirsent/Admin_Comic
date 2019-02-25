<?php
namespace Common\Model;
use Common\Model\BaseModel;
class GatherLikesModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('like_time','get_datetime',1,'callback')
    );

    public function getLikesNumber($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__GATHER__ g ON g.id = l.gather_id')
            ->join('__READER__ r On r.id = l.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getLikesData($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__GATHER__ g ON g.id = l.gather_id')
            ->join('__READER__ r On r.id = l.reader_id')
            ->field('l.*,gather_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}