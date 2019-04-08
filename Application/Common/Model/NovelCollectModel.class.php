<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelCollectModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback'),
    );

    public function getCollectNumber($cond=[])
    {
        $data = $this
            ->alias('nc')
            ->join('__NOVEL__ n ON n.id = nc.novel_id')
            ->join('__READER__ r ON r.id = nc.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getCollectData($cond=[])
    {
        $data = $this
            ->alias('nc')
            ->join('__NOVEL__ n ON n.id = nc.novel_id')
            ->join('__READER__ r ON r.id = nc.reader_id')
            ->field('nc.*,title,nickname')
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
            ->join('__NOVEL__ n ON n.id = c.novel_id')
            ->field('novel_id,cover,title,total_chapter')
            ->where($cond)
            ->select();

        $history = M('novel_history');
        $chapter = M('novel_chapter');
        foreach ($data as $key => $value) {
            // 阅读历史
            $cond_history = [
                'novel_id'  => $value['novel_id'],
                'reader_id' => $readerId
            ];
            $lastChapter = $history->where($cond_history)->getField('chapter');
            $data[$key]['remain_chapter'] = $value['total_chapter'] - $lastChapter; // 未读章节

            // 章节标题
            $cond_chapter = [
                'novel_id' => $value['novel_id'],
                'catalog'  => $value['total_chapter']
            ];
            $data[$key]['chapter_title'] = $chapter->where($cond_chapter)->getField('chapter_title');
        }

        return $data;
    }
}