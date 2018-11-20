<?php
namespace Common\Model;
use Common\Model\BaseModel;
class LikesModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback')
    );

    public function getLikesNumber($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__COMICS__ c ON c.id = l.comic_id')
            ->join('__READER__ r On r.openid = l.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getLikesData($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__COMICS__ c ON c.id = l.comic_id')
            ->join('__READER__ r On r.openid = l.openid')
            ->field('l.*,c.title as comic_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}