<?php
namespace Common\Model;
use Common\Model\BaseModel;
class GatherCommentModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('comment_time','get_datetime',1,'callback')
    );

    public function getCommentNumber($cond){
        $data = $this
            ->alias('gc')
            ->join('__GATHER__ g ON g.id = gc.gather_id')
            ->join('__READER__ r ON r.id = gc.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCommentData($cond){
        $data = $this
            ->alias('gc')
            ->join('__GATHER__ g ON g.id = gc.gather_id')
            ->join('__READER__ r ON r.id = gc.reader_id')
            ->field('gc.*,gather_title,url,nickname,head')
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['url'] = explode(',',$value['url'])[0];
        }
        return $data;
    }

    /**
     * 主评论
     */
    public function getComment1st($gatherId)
    {
        $cond_1st = [
            'c.status' => C('STATUS_Y'),
            'pid'      => 0,
            'gather_id' => $gatherId
        ];
        $data = $this
            ->alias('c')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->field('c.*,nickname,head')
            ->where($cond_1st)
            ->select();
        foreach ($data as $key => $value) {
            $cond_2nd = [
                'status' => C('STATUS_Y'),
                'pid'      => $value['id'],
                'gather_id' => $gatherId
            ];
            $data[$key]['replys'] = $this->where($cond_2nd)->count(); // 回复数量
            $data[$key]['comment_time'] = date('m/d', strtotime($value['comment_time']));
        }
        return $data;
    }

    public function getCommentInfo($id)
    {
        $cond['c.id'] = $id;
        $data = $this
            ->alias('c')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->field('c.*,nickname,head')
            ->where($cond)
            ->find();
        $data['comment_time'] = date('m/d', strtotime($data['comment_time']));
        $cond_2nd = [
            'c.status' => C('STATUS_Y'),
            'pid'      => $id
        ];
        $data['child'] = $this
            ->alias('c')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->field('c.*,nickname,head')
            ->where($cond_2nd)
            ->select();
        return $data;
    }
}
