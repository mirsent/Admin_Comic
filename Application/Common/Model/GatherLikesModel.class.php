<?php
namespace Common\Model;
use Common\Model\BaseModel;
class GatherLikesModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('like_time','get_datetime',1,'callback')
    );

    public function getLikesNumber($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__GATHER__ g ON g.id = l.gather_id')
            ->join('__READER__ r On r.id = l.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getLikesData($cond=[])
    {
        $data = $this
            ->alias('l')
            ->join('__GATHER__ g ON g.id = l.gather_id')
            ->join('__READER__ reader ON reader.id = g.publisher_id')
            ->join('__READER__ r On r.id = l.reader_id')
            ->field('l.*,gather_title,url,r.nickname,reader.nickname as publisher')
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['url_arr'] = explode(',',$value['url']);
        }
        return $data;
    }

}