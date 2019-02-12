<?php
namespace Common\Model;
use Common\Model\BaseModel;
class HistoryModel extends BaseModel{

    public function getHistoryNumber($cond=[])
    {
        $data = $this
            ->alias('h')
            ->join('__COMICS__ c ON c.id = h.comic_id')
            ->join('__READER__ r ON r.openid = h.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getHistoryData($cond=[])
    {
        $data = $this
            ->alias('h')
            ->join('__COMICS__ c ON c.id = h.comic_id')
            ->join('__READER__ r ON r.openid = h.openid')
            ->field('h.*,c.title as comic_title,r.nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

    /**
     * 获取指定读者历史记录
     * @param  int $openid
     */
    public function getHistoryList($openid)
    {
        $cond['openid'] = $openid;
        $data = $this
            ->alias('h')
            ->join('__COMICS__ c ON c.id = h.comic_id')
            ->field('h.*,cover,title,brief,total_chapter')
            ->where($cond)
            ->select();

        foreach ($data as $key => $value) {
            $data[$key]['rate'] = intval($value['chapter'] / $value['total_chapter'] * 100);
        }

        return $data;
    }

    /**
     * 更新阅读历史
     * @param  int $comicId 漫画ID
     * @param  string $openid  读者openid
     * @param  int $chapter 阅读章节
     * @param  int $channel 途径
     */
    public function updateHistory($comicId, $chapter, $openid, $channel){
        $data = [
            'comic_id'  => $comicId,
            'openid'    => $openid,
            'chapter'   => $chapter,
            'channel'   => $channel,
            'last_time' => date('Y-m-d H:i:s')
        ];

        $cond_comic['id'] = $comicId;
        $typeIds = M('comics')->where($cond_comic)->getField('type_ids');
        $data['type_ids'] = $typeIds;

        $cond = [
            'comic_id' => $comicId,
            'openid'   => $openid,
        ];
        $historyInfo = $this->where($cond)->find();

        if ($historyInfo) {
            $this->where($cond)->save($data);
        } else {
            $this->add($data);
        }
    }
}