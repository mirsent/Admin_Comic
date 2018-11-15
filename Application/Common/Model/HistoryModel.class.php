<?php
namespace Common\Model;
use Common\Model\BaseModel;
class HistoryModel extends BaseModel{
    /**
     * 更新阅读历史
     * @param  int $comicId 漫画ID
     * @param  string $openid  读者openid
     * @param  int $chapter 阅读章节
     */
    public function updateHistory($comicId, $chapter, $openid){
        $data = [
            'comic_id'  => $comicId,
            'openid'    => $openid,
            'chapter'   => $chapter,
            'last_time' => date('Y-m-d H:i:s')
        ];

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