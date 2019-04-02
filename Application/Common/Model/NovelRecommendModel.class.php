<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelRecommendModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('recommend_time','get_datetime',1,'callback')
    );

    public function getRecommendData($cond=[])
    {
        $data = $this
            ->alias('r')
            ->join('__NOVEL__ n ON n.id = r.novel_id')
            ->field('r.*, n.title')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    public function getRecommendApi($cond=[])
    {
        $data = $this
            ->alias('r')
            ->join('__NOVEL__ n ON n.id = r.novel_id')
            ->field('n.id as novel_id, title, cover, author, brief, type_ids')
            ->where(array_filter($cond))
            ->select();

        $tag = M('comic_type');
        foreach ($data as $key => $value) {
            $cond = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['type_ids'])
            ];
            $tagArr = $tag->where($cond)->getField('comic_type_name', true);
            $data[$key]['tags'] = implode(' ', $tagArr);
        }

        return $data;
    }

}