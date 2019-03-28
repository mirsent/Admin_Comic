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
}
