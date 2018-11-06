<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ComicsModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('created_at','get_datetime',1,'callback'),
        array('updated_at','get_datetime',3,'callback')
    );

    public function getComicNumber($cond){
        $data = $this
            ->alias('c')
            ->join('__RELEASE_TYPE__ rt ON rt.id = c.release_type_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getComicData($cond){
        $data = $this
            ->alias('c')
            ->join('__RELEASE_TYPE__ rt ON rt.id = c.release_type_id')
            ->field('c.*,release_type_name')
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
        }
        return $data;
    }

    public function addComic($data){
        if (!$data = $this->create($data)) {
            return false;
        } else {
            $data['type_ids'] = implode(',', $data['type_ids']);
            $res = $this
                ->add($data);
            return $res;
        }
    }

    public function editComic($cond, $data){
        if (!$data = $this->create($data)) {
            return false;
        } else {
            $data['type_ids'] = implode(',', $data['type_ids']);
            $res = $this
                ->where($cond)
                ->save($data);
            return $res;
        }
    }

}
