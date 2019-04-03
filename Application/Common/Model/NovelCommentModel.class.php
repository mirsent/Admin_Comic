<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelCommentModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('comment_time','get_datetime',1,'callback')
    );

    /**
     * 主评论
     */
    public function getComment1st($novelId)
    {
        $cond_1st = [
            'c.status' => C('STATUS_Y'),
            'pid'      => 0,
            'novel_id' => $novelId
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
                'novel_id' => $novelId
            ];
            $data[$key]['replys'] = $this->where($cond_2nd)->count(); // 回复数量
            $data[$key]['comment_time'] = date('m/d', strtotime($value['comment_time']));
        }
        return $data;
    }

    /**
     * 全部评论
     */
    public function getCommentAPi($novelId)
    {
        $cond_1st = [
            'c.status' => C('STATUS_Y'),
            'pid'      => 0,
            'novel_id' => $novelId
        ];
        $data = $this
            ->alias('c')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->field('c.*,nickname,head')
            ->where($cond_1st)
            ->select();
        foreach ($data as $key => $value) {
            $cond_2nd = [
                'c.status' => C('STATUS_Y'),
                'pid'      => $value['id'],
                'novel_id' => $novelId
            ];
            $data[$key]['child'] = $this
                ->alias('c')
                ->join('__READER__ r ON r.id = c.reader_id')
                ->field('c.*,nickname,head')
                ->where($cond_2nd)
                ->select();
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