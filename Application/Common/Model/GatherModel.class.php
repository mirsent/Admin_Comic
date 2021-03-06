<?php
namespace Common\Model;
use Common\Model\BaseModel;
class GatherModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('likes','0'),
        array('publish_time','get_datetime',1,'callback')
    );

    public function getGatherNumber($cond=[])
    {
        $data = $this
            ->alias('g')
            ->join('__READER__ r ON r.id = g.publisher_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getGatherData($cond=[])
    {
        $data = $this
            ->alias('g')
            ->join('__READER__ r ON r.id = g.publisher_id')
            ->field('g.*,g.id as gather_id,head,nickname')
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['url_arr'] = explode(',',$value['url']);
        }
        return $data;
    }

    public function getGatherDetail($id)
    {
        $cond['g.id'] = $id;
        $data = $this
            ->alias('g')
            ->join('__READER__ r ON r.id = g.publisher_id')
            ->field('g.*,head,nickname')
            ->where($cond)
            ->find();
        return $data;
    }

}