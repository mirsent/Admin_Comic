<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelCollectModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback'),
    );

    public function getCollectNumber($cond=[])
    {
        $data = $this
            ->alias('nc')
            ->join('__NOVEL__ n ON n.id = nc.novel_id')
            ->join('__READER__ r ON r.id = nc.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCollectData($cond=[])
    {
        $data = $this
            ->alias('nc')
            ->join('__NOVEL__ n ON n.id = nc.novel_id')
            ->join('__READER__ r ON r.id = nc.reader_id')
            ->field('nc.*,title,nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}