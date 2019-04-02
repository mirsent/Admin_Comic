<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelChapterModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getChapterData($cond=[]){
        $data = $this
            ->alias('c')
            ->join('__NOVEL_CHAPTER_DETAIL__ cd ON cd.chapter_id = c.id')
            ->field('c.*,content')
            ->where(array_filter($cond))
            ->order('catalog')
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['catalog_name'] = '第'.$value['catalog'].'章';
            $data[$key]['content'] = htmlspecialchars_decode($value['content']);
        }
        return $data;
    }

    public function getChapterInfo($cond=[])
    {
        $data = $this
            ->alias('c')
            ->join('__NOVEL_CHAPTER_DETAIL__ cd ON cd.chapter_id = c.id')
            ->field('c.*,content')
            ->where(array_filter($cond))
            ->find();
        $data['content'] = htmlspecialchars_decode($data['content']);
        return $data;
    }
}
