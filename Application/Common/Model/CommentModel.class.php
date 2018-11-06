<?php
namespace Common\Model;
use Common\Model\BaseModel;
class CommentModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getCommentData($cond){
        $data = $this
            ->alis('c')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->field('c.*,comic.name as comic_name,comic.cover as comic_img,reader.nickname as reader_name,reader.head as reader_img')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}
