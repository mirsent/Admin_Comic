<?php
namespace Common\Model;
use Common\Model\BaseModel;
class CollectModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback')
    );

    public function getCollectNumber($cond)
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.id = ct.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCollectData($cond)
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.id = ct.reader_id')
            ->field('ct.*,c.title as comic_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}