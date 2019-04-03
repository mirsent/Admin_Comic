<?php
namespace Common\Model;
use Common\Model\BaseModel;
class NovelHistoryModel extends BaseModel{

    public function getHistoryNumber($cond=[])
    {
        $data = $this
            ->alias('h')
            ->join('__NOVEL__ n ON n.id = h.novel_id')
            ->join('__READER__ r ON r.id = h.reader_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getHistoryData($cond=[])
    {
        $data = $this
            ->alias('h')
            ->join('__NOVEL__ n ON n.id = h.novel_id')
            ->join('__READER__ r ON r.id = h.reader_id')
            ->field('h.*,title,nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 获取指定读者历史记录
     * @param  int $readerId
     */
    public function getHistoryList($readerId)
    {
        $cond['reader_id'] = $readerId;
        $data = $this
            ->alias('h')
            ->join('__NOVEL__ n ON n.id = h.novel_id')
            ->field('h.*,cover,title,brief,total_chapter')
            ->where($cond)
            ->select();

        $chapter = M('novel_chapter');
        foreach ($data as $key => $value) {
            $cond_chapter = [
                'novel_id' => $value['novel_id'],
                'catalog'  => $value['chapter']
            ];
            $data[$key]['chapter_title'] = $chapter->where($cond_chapter)->getField('chapter_title');
            $data[$key]['chapter_name'] = toChineseNumber($value['chapter']);
            $data[$key]['rate'] = intval($value['chapter'] / $value['total_chapter'] * 100);
        }

        return $data;
    }


    /**
     * 更新阅读历史
     * @param  int $novelId 小说ID
     * @param  int $readerId  读者ID
     * @param  int $chapter 阅读章节
     */
    public function updateHistory($novelId, $chapter, $readerId){
        $data = [
            'novel_id'  => $novelId,
            'reader_id' => $readerId,
            'chapter'   => $chapter,
            'last_time' => date('Y-m-d H:i:s')
        ];

        $cond = [
            'comic_id'  => $novelId,
            'reader_id' => $readerId,
        ];
        $historyInfo = $this->where($cond)->find();

        if ($historyInfo) {
            $this->where($cond)->save($data);
        } else {
            $this->add($data);
        }
    }
}