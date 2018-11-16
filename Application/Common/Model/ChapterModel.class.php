<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ChapterModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getChapterData($cond){
        $data = $this
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['catalog_name'] = '第'.$value['catalog'].'章';
        }
        return $data;
    }

    /**
     * 获取漫画章节信息
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     */
    public function getChapterInfo($comicId, $chapter){
        $cond = [
            'comic_id' => $comicId,
            'catalog'  => $chapter,
        ];
        $data = $this
            ->alias('cp')
            ->join('__COMICS__ c ON c.id = cp.comic_id')
            ->field('chapter_cover,title,brief,total_chapter,pre_chapter_pay,pre_chapter_share,max_share_chapter')
            ->where($cond)
            ->find();

        // 判断是否有封面
        if (!$data['chapter_cover']) {
            $cond_cover = [
                'status'   => C('STATUS_Y'),
                'comic_id' => $comicId
            ];
            $data['chapter_cover'] = M('comic_cover')
                ->where($cond)
                ->order('rand()')
                ->getField('cover_url');
        }
        return $data;
    }

    /**
     * 判断章节封面
     * @param  int $comicId 漫画ID
     * @param  章节 $chapter 章节
     * @return string 设置的章节封面||comic_cover分享库中随机一张
     */
    public function getChapterCover($comicId, $chapter){
        $cond = [
            'comic_id' => $comicId,
            'catalog'  => $chapter
        ];
        $cover = $this->where($cond)->getField('chapter_cover');

        // 判断是否有封面
        if (!$cover) {
            $cond_cover = [
                'status'   => C('STATUS_Y'),
                'comic_id' => $comicId
            ];
            $cover = M('comic_cover')
                ->where($cond)
                ->order('rand()')
                ->getField('cover_url');
        }
        return $cover;
    }

}
