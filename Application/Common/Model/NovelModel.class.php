<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('created_at','get_datetime',1,'callback'),
        array('updated_at','get_datetime',1,'callback')
    );

    public function getNovelNumber($cond){
        $data = $this
            ->alias('n')
            ->join('__RELEASE_TYPE__ rt ON rt.id = n.release_type_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getNovelData($cond){
        $data = $this
            ->alias('n')
            ->join('__RELEASE_TYPE__ rt ON rt.id = n.release_type_id')
            ->field('n.*,release_type_name')
            ->where(array_filter($cond))
            ->select();
        $type = M('comic_type');
        foreach ($data as $key => $value) {
            $cond_type = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['type_ids'])
            ];
            $typeArr = $type->where($cond_type)->getField('comic_type_name', true);
            $data[$key]['type_names'] = implode('；', $typeArr);
            $data[$key]['brief'] = htmlspecialchars_decode($value['brief']);
            $data[$key]['no'] = str_pad($value['id'], 5, 0, STR_PAD_LEFT);
        }
        return $data;
    }

    public function getNovelInfo($id)
    {
        $data = $this->find($id);

        $cond_tag = [
            'status' => C('STATUS_Y'),
            'id'     => array('in', $data['type_ids'])
        ];
        $data['tags'] = M('comic_type')->where($cond_tag)->getField('comic_type_name', true);

        $chapter = M('novel_chapter');
        $cond = [
            'status'   => C('STATUS_Y'),
            'novel_id' => $id
        ];
        $data['words'] = $chapter->where($cond)->sum('words'); // 字数
        $data['chapters'] = $chapter->where($cond)->count(); // 章节数
        $data['collects'] = M('novel_collect')->where($cond)->count(); // 收藏数

        return $data;
    }
}
