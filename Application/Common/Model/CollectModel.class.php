<?php
namespace Common\Model;
use Common\Model\BaseModel;
class CollectModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback')
    );

    public function getCollectNumber($cond=[])
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.id = ct.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCollectData($cond=[])
    {
        $data = $this
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->join('__READER__ r ON r.id = ct.reader_id')
            ->field('ct.*,c.title as comic_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 获取指定读者收藏
     * @param int $readerId
     */
    public function getCollectList($readerId)
    {
        $cond = [
            'c.status'  => C('STATUS_Y'),
            'reader_id' => $readerId
        ];
        $data = $this
            ->alias('c')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->field('comic_id,head,title,total_chapter')
            ->where($cond)
            ->select();

        $history = M('history');
        $chapter = M('chapter');
        foreach ($data as $key => $value) {
            // 阅读历史
            $cond_history = [
                'comic_id'  => $value['comic_id'],
                'reader_id' => $readerId
            ];
            $lastChapter = $history->where($cond_history)->getField('chapter');
            $data[$key]['remain_chapter'] = $value['total_chapter'] - $lastChapter; // 未读章节

            // 章节标题
            $cond_chapter = [
                'comic_id' => $value['comic_id'],
                'catalog'  => $value['total_chapter']
            ];
            $data[$key]['chapter_title'] = $chapter->where($cond_chapter)->getField('chapter_title');
        }

        return $data;
    }

}