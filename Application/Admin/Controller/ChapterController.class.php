<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ChapterController extends AdminBaseController{

    /**
     * 验证目录
     */
    public function check_chapter(){
        $comicId = I('comic_id');
        $path = "Uploads/comic/".$comicId."/*";
        $chapterN = count(glob($path)); // 章节数量

        $chapter = M('chapter');
        $cond_chapter = [
            'status'   => C('STATUS_Y'),
            'comic_id' => $comicId
        ];
        $chapterNExsist = $chapter->where($cond_chapter)->count();

        $diff = $chapterN - $chapterNExsist;

        if ($diff > 0) {
            // 更新
            for ($i=1; $i <= $diff; $i++) {
                $newChapter = $chapterNExsist + $i;
                $new[] = [
                    'catalog'    => $newChapter,
                    'comic_id'   => $comicId,
                    'popularity' => rand_number(99,999),
                    'create_at'  => date('Y-m-d'),
                    'status'     => C('STATUS_Y')
                ];
            }
            $chapter->addAll($new);
            ajax_return(2, '新增目录');
        } else if ($diff < 0) {
            // 回退
            $cond_back = [
                'status' => C('STATUS_Y'),
                'catalog' => array('gt', $chapterN)
            ];
            $data_back['status'] = C('STATUS_N');
            $chapter->where($cond_back)->save($data_back);
            ajax_return(3, '删减目录');
        }
        ajax_return(1);
    }

    /**
     * 获取章节信息
     */
    public function get_chapter_info(){
        $cond = [
            'status'   => C('STATUS_Y'),
            'comic_id' => I('comic_id')
        ];
        $infos = D('Chapter')->getChapterData($cond);

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 编辑章节信息
     */
    public function input_chapter(){
        $chapter = D('Chapter');
        $chapter->create();
        $id = I('id');
        $cond['id'] = $id;
        $res = $chapter->where($cond)->save();

        if ($res === false) {
            ajax_return(0, '编辑章节信息失败');
        }
        ajax_return(1);
    }
}
