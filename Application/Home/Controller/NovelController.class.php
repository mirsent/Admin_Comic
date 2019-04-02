<?php
namespace Home\Controller;
use Think\Controller;

class NovelController extends Controller {

    /**
     * 获取novel页面数据
     */
    public function get_novel_data()
    {
        $cond['status'] = C('STATUS_Y');
        $banner = M('novel_banner')->where($cond)->select();

        $cond_recommend['r.status'] = C('STATUS_Y');
        $recommend = D('NovelRecommend')->getRecommendApi($cond_recommend);

        $data = [
            'banner'    => $banner,
            'recommend' => $recommend
        ];
        ajax_return(1, 'novel data', $data);
    }

    /**
     * 获取novel-list页面数据
     */
    public function get_novel_list()
    {
        $cond['n.status'] = C('STATUS_Y');
        $type = I('type');
        switch (I('type')) {
            case 3: // 免费
                $cond['s_fee'] = C('C_FEE_N');
                break;
            case 4: // 完结
                $cond['s_serial'] = C('C_SERIAL_W');
                break;

            default:
                break;
        }
        $data = D('Novel')->getNovelData($cond);

        ajax_return(1, 'novel list', $data);
    }

    /**
     * 获取novel-info页面数据
     * @param int novel_id 小说ID
     * @param int reader_id 读者ID
     */
    public function get_novel_info()
    {
        $novelId = I('novel_id');
        $data = D('Novel')->getNovelInfo($novelId);

        // 读者历史记录
        $cond = [
            'novel_id' => $novelId,
            'reader_id' => I('reader_id')
        ];
        $history = M('novel_history')->where($cond)->find();
        if ($history) {
            $data['history_chapter'] = $history['chapter'];
        } else {
            $data['history_chapter'] = 1;
        }

        // 读者收藏信息
        $collect = M('novel_collect')->where($cond)->where(['status'=>C('STATUS_Y')])->find();
        if ($collect) {
            $data['is_collect'] = 1;
        }

        // 评分
        $comment = M('novel_comment');
        $cond_comment = [
            'status'   => C('STATUS_Y'),
            'novel_id' => $novelId
        ];
        $score = $comment->where($cond_comment)->sum('score');
        $scoreCount = $comment->where($cond_comment)->count();
        $scoreAvg = round($score/$scoreCount,1);
        $data['score_count'] = $scoreCount;
        $data['score_avg'] = $scoreAvg;

        // 阅读数+1
        $cond_novel['id'] = $novelId;
        D('Novel')->where($cond_novel)->setInc('popularity');

        ajax_return(1, 'novel info', $data);
    }

    /**
     * 评论
     * @param int reader_id 读者ID
     * @param int novel_id 小说Id
     * @param varchar comment_content 评论内容
     */
    public function comment()
    {
        $comment = D('NovelComment');
        $comment->create();
        $comment->pid = 0;
        $comment->add();

        ajax_return(1, 'comment');
    }

    /**
     * 获取小说章节信息
     */
    public function get_chapter_data()
    {
        $cond['novel_id'] = I('novel_id');
        $data = M('novel_chapter')->where($cond)->order('catalog')->select();

        ajax_return(1, 'chapter', $data);
    }

    /**
     * 收藏小说
     * @param int reader_id 读者ID
     * @param int novel_id 小说ID
     */
    public function collect_novel()
    {
        $cond = [
            'reader_id' => I('reader_id'),
            'novel_id'  => I('novel_id')
        ];
        $collect = D('NovelCollect');
        $res = $collect->where($cond)->find();

        if ($res) {
            $collect->where($cond)->save(['status'=>C('STATUS_Y')]);
        } else {
            $collect->create();
            $collect->add();
        }

        ajax_return(1, 'collect novel');
    }

    /**
     * 取消收藏小说
     * @param int reader_id 读者ID
     * @param int novel_id 小说ID
     */
    public function cancel_collect_novel()
    {
        $cond = [
            'reader_id' => I('reader_id'),
            'novel_id'  => I('novel_id')
        ];
        $res = M('novel_collect')->where($cond)->save(['status'=>C('STATUS_N')]);

        ajax_return(1, 'cancel collect novel');
    }

    /**
     * 小说正文
     */
    public function get_novel_content()
    {
        $cond = [
            'novel_id' => I('novel_id'),
            'catalog'  => I('catalog')
        ];
        $data = D('NovelChapter')->getChapterInfo($cond);

        ajax_return(1, 'novel content', $data);
    }
}