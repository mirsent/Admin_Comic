<?php
namespace Common\Model;
use Common\Model\BaseModel;
class LikesModel extends BaseModel{

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