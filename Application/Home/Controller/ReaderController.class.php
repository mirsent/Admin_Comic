<?php
namespace Home\Controller;
use Think\Controller;

class ReaderController extends Controller {

    /**
     * 消费记录
     */
    public function get_consume_note()
    {
        $cond = [
            'openid' => I('openid'),
            'method' => 2
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '消费记录', $data);
    }

    /**
     * 充值记录
     */
    public function get_recharge_note()
    {
        $cond = [
            'openid' => I('openid'),
            'method' => 1
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '充值记录', $data);
    }

    ////////
    // 画册 //
    ////////

    public function get_gather()
    {
        $cond['g.status'] = C('APPLY_P');
        $gathers = D('Gather')->order('publish_time desc')->getGatherData($cond);

        $left = [];
        $right = [];

        foreach ($gathers as $key => $value) {
            if ($key%2 == 0) {
                $left[] = $gathers[$key];
            } else {
                $right[] = $gathers[$key];
            }
        }

        $data = [
            'left' => $left,
            'right' => $right
        ];

        ajax_return(1, '画册', $data);
    }

    /**
     * 发布画册
     */
    public function add_gather()
    {
        $gather = D('Gather');
        $gather->create();
        $res = $gather->add();

        if ($res === false) {
            ajax_return(0, '发布画册失败');
        }
        ajax_return(1);
    }

    /**
     * 画册点喜欢
     * @param gather_id 画册ID
     * @param reader_id 读者ID
     */
    public function like_gather()
    {
        // 画册like+1
        $cond_gather['id'] = I('gather_id');
        M('gather')->where($cond_gather)->setInc('likes');

        $like = D('GatherLikes');
        $like->create();
        $like->add();

        ajax_return(1);
    }

    /**
     * 获取回复内容
     * @param reply_openid
     */
    public function get_reply()
    {
        $cond['reply_openid'] = I('reply_openid');

        $data = M('comment')
            ->alias('c')
            ->join('__READER__ r ON r.openid = c.openid')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->field('c.*,nickname,r.head,title')
            ->order('comment_time desc')
            ->where($cond)
            ->select();

        ajax_return(1, '回复内容', $data);
    }

    /**
     * 获取通知
     */
    public function get_notice()
    {
        $cond = [
            'status'    => C('STATUS_Y'),
            'reader_id' => I('reader_id')
        ];
        $data = M('notice')->order('notice_time desc')->where($cond)->select();

        ajax_return(1, '通知消息', $data);
    }

    ////////
    // 点赞 //
    ////////

    /**
     * 点赞
     * @param comic_id 漫画ID
     * @param openid 读者ID
     */
    public function like(){
        $likes = D('Likes');
        $likes->create();

        $comicId = I('comic_id');
        $openid = I('openid');

        $cond = [
            'comic_id' => $comicId,
            'openid'   => $openid
        ];
        $likesInfo = $likes->where($cond)->find();

        if ($likesInfo) {
            $likes->status = C('STATUS_Y');
            $res = $likes->where($cond)->save();
        } else {
            $res = $likes->add();
        }

        if ($res === false) {
            ajax_return(0, '点赞失败');
        }
        D('Comics')->likeComic($comicId);
        ajax_return(1);
    }

    /**
     * 取消点赞
     */
    public function cancel_like()
    {
        $cond = [
            'comic_id' => I('comic_id'),
            'openid'   => I('openid')
        ];
        $data = [
            'status' => C('STATUS_N')
        ];
        $res = M('likes')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '取消点赞失败');
        }
        ajax_return(1);
    }

    ////////
    // 收藏 //
    ////////

    /**
     * 收藏
     * @param comic_id 漫画ID
     * @param openid 读者ID
     */
    public function collect(){
        $collect = D('Collect');
        $collect->create();

        $comicId = I('comic_id');
        $openid = I('openid');

        $cond = [
            'comic_id' => $comicId,
            'openid'   => $openid
        ];
        $collectInfo = $collect->where($cond)->find();

        if ($collectInfo) {
            $collect->status = C('STATUS_Y');
            $res = $collect->where($cond)->save($data);
        } else {
            $res = $collect->add();
        }

        if ($res === false) {
            ajax_return(0, '收藏失败');
        }
        ajax_return(1);
    }

    /**
     * 取消收藏
     */
    public function cancel_collect()
    {
        $cond = [
            'comic_id' => I('comic_id'),
            'openid'   => I('openid')
        ];
        $data = [
            'status' => C('STATUS_N')
        ];
        $res = M('collect')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '取消收藏失败');
        }
        ajax_return(1);
    }

    /**
     * 我的收藏
     */
    public function get_collect_comic()
    {
        $cond = [
            'ct.status' => C('STATUS_Y'),
            'openid'    => I('openid')
        ];
        $data = M('collect')
            ->alias('ct')
            ->join('__COMICS__ c ON c.id = ct.comic_id')
            ->field('comic_id,head,title,total_chapter')
            ->where($cond)
            ->select();

        $history = M('history');
        $chapter = M('chapter');
        foreach ($data as $key => $value) {
            // 阅读历史
            $cond_history = [
                'comic_id' => $value['comic_id'],
                'openid'   => I('openid')
            ];
            $lastChapter = $history->where($cond_history)->getField('chapter');
            $data[$key]['remain_chapter'] = $value['total_chapter'] - $lastChapter;

            // 章节标题
            $cond_chapter = [
                'comic_id' => $value['comic_id'],
                'catalog'  => $value['total_chapter']
            ];
            $data[$key]['chapter_title'] = $chapter->where($cond_chapter)->getField('chapter_title');
        }

        ajax_return(1, '我的收藏', $data);
    }
}