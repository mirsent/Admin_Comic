<?php
namespace Common\Model;
use Common\Model\BaseModel;
class CollectModel extends BaseModel{

    public function getCollectNumber($cond)
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.openid = ct.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCollectData($cond)
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.openid = ct.openid')
            ->field('ct.*,c.title as comic_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}