<?php
namespace Common\Model;
use Common\Model\BaseModel;
class RecommendModel extends BaseModel{

    public function getRecommendData($cond=[])
    {
        $data = $this
            ->alias('r')
            ->join('__COMICS__ c ON c.id = r.comic_id')
            ->field('r.*,head,cover,title,brief,popularity')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    public function getRecommendApi($cond=[])
    {
        $data = $this
            ->alias('r')
            ->join('__COMICS__ c ON c.id = r.comic_id')
            ->field('c.id,head,cover,title,brief,popularity')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}