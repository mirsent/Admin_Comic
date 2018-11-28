<?php
namespace Common\Model;
use Common\Model\BaseModel;
class CommentModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('comment_time','get_datetime',1,'callback')
    );

    public function getCommentNumber($cond){
        $data = $this
            ->alias('c')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->join('__READER__ r ON r.openid = c.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCommentData($cond){
        $data = $this
            ->alias('c')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->join('__READER__ r ON r.openid = c.openid')
            ->field('c.*,comic.title as comic_title,comic.cover as comic_img,r.nickname,r.head as reader_img')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 根据漫画ID获取评论列表
     */
    public function getCommentInfo($comicId)
    {
        $cond = [
            'c.status' => C('STATUS_Y'),
            'comic_id' => $comicId
        ];
        $data = $this
            ->alias('c')
            ->join('__READER__ r ON r.openid = c.openid')
            ->field('c.*,nickname,head')
            ->order('comment_time desc')
            ->where($cond)
            ->select();
        return $data;
    }

}
