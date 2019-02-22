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
            ->join('__READER__ r ON r.openid = c.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCommentData($cond){
        $data = $this
            ->alias('gc')
            ->join('__GATHER__ g ON g.id = gc.gather_id')
            ->join('__READER__ r ON r.openid = c.openid')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 根据画册ID获取评论列表
     */
    public function getCommentInfo($gatherId)
    {

        $cond = [
            'status'    => C('STATUS_Y'),
            'gather_id' => $gatherId
        ];
        $count = $this->where($cond)->count();

        $cond_main = [
            'gc.status' => C('STATUS_Y'),
            'gather_id' => $gatherId,
            'level'     => 1
        ];
        $comment = $this
            ->alias('gc')
            ->join('__READER__ r ON r.openid = gc.openid')
            ->field('gc.*,nickname,head')
            ->order('comment_time')
            ->where($cond_main)
            ->select();

        foreach ($comment as $key => $value) {
            $cond_sub = [
                'gc.status' => C('STATUS_Y'),
                'gc.pid'    => $value['id'],
                'gather_id' => $gatherId,
                'level'     => 2
            ];
            $comment[$key]['sub'] = $this
                ->alias('gc')
                ->join('__READER__ r ON r.openid = gc.openid')
                ->join('__READER__ reply ON reply.openid = gc.reply_openid')
                ->field('gc.*,r.nickname,r.head,reply.nickname as replyname')
                ->order('comment_time')
                ->where($cond_sub)
                ->select();
        }

        $data = [
            'count'   => $count,
            'comment' => $comment
        ];

        return $data;
    }

}
