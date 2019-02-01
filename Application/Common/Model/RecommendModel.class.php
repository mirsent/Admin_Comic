<?php
namespace Common\Model;
use Common\Model\BaseModel;
class RecommendModel extends BaseModel{

    public function getRecommendData($cond=[])
    {
        $data = $this
            ->alias('r')
            ->join('__COMICS__ c ON c.id = r.comic_id')
            ->field('r.*,c.title')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}