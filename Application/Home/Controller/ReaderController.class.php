<?php
namespace Home\Controller;
use Think\Controller;

require './vendor/autoload.php';
use DfaFilter\SensitiveHelper;

class ReaderController extends Controller {

    public function get_reader_info()
    {
        $data = M('reader')->find(I('reader_id'));
        ajax_return(1, '读者信息', $data);
    }

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

    /**
     * 获取画册信息
     * @param reader_id 读者ID
     */
    public function get_gather()
    {
        $cond['g.status'] = C('APPLY_P');
        $gathers = D('Gather')->order('publish_time desc')->getGatherData($cond);

        $left = [];
        $right = [];

        $readerId = I('reader_id');
        $like = M('gather_likes');
        $comment = M('gather_comment');
        foreach ($gathers as $key => $value) {
            $gathers[$key]['url'] = explode(',', $value['url'])[0];

            // 是否喜欢
            $cond_like = [
                'gather_id' => $value['id'],
                'reader_id' => $readerId,
                'status'    => C('STATUS_Y')
            ];
            $gathers[$key]['is_like'] = $like->where($cond_like)->find();

            $cond_comment = [
                'gather_id' => $value['id'],
                'status'    => C('STATUS_Y')
            ];
            $gathers[$key]['comments'] = $comment->where($cond_comment)->count();

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
     * 获取画册评论
     * @param gather_id 画册ID
     */
    public function get_gather_comment()
    {
        $gatherId = I('gather_id');
        $data = D('GatherComment')->getCommentInfo($gatherId);
        ajax_return(1, '评论列表', $data);
    }

    /**
     * 评论
     */
    public function comment()
    {
        $comment = D('GatherComment');
        $comment->create();

        $content = I('comment_content');

        // 获取感词库文件路径
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';

        // get one helper
        $handle = SensitiveHelper::init()->setTreeByFile($wordFilePath);

        // 敏感词替换为***为例
        $filterContent = $handle->replace($content, C('FILTER_TEXT'));

        $comment->comment_content = $filterContent;
        $comment->level = 1; // 主评论
        $comment->pid = 0;
        $res = $comment->add();

        if ($res === false) {
            ajax_return(0, '评论失败');
        }
        ajax_return(1);
    }

    /**
     * 回复评论
     * @param level 2
     */
    public function reply()
    {
        $comment = D('GatherComment');
        $comment->create();

        $content = I('comment_content');

        // 获取感词库文件路径
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';

        // get one helper
        $handle = SensitiveHelper::init()->setTreeByFile($wordFilePath);

        // 敏感词替换为***为例
        $filterContent = $handle->replace($content, C('FILTER_TEXT'));

        $comment->comment_content = $filterContent;
        $comment->level = 2;
        $res = $comment->add();

        if ($res === false) {
            ajax_return(0, '评论失败');
        }
        ajax_return(1);
    }

    /**
     * 获取画册详情
     * @param gather_id 画册ID
     */
    public function get_gather_detail()
    {
        $data = D('Gather')->getGatherDetail(I('gather_id'));

        $data['url'] = explode(',',$data['url']);

        $cond_like = [
            'gather_id' => $data['id'],
            'reader_id' => I('reader_id'),
            'status'    => C('STATUS_Y')
        ];
        $data['is_like'] = M('gather_likes')->where($cond_like)->find();

        ajax_return(1, '画册详情', $data);
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
     * 画册取消喜欢
     * @param gather_id 画册ID
     * @param reader_id 读者ID
     */
    public function cancel_like_gather()
    {
        // 画册like+1
        $cond_gather['id'] = I('gather_id');
        M('gather')->where($cond_gather)->setDec('likes');

        $cond_like = [
            'gather_id' => I('gather_id'),
            'reader_id' => I('reader_id')
        ];
        M('gather_likes')->where($cond_like)->save(['status'=>C('STATUS_N')]);

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
            ->field('c.*,nickname,r.head,title,comic.title as comic_title')
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