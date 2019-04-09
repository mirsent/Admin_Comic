<?php
namespace Home\Controller;
use Think\Controller;

require './vendor/autoload.php';
use DfaFilter\SensitiveHelper;

class GatherController extends Controller {

    /**
     * 获取画册页信息
     * @param reader_id 读者ID
     */
    public function get_gather_data()
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
     * 获取画册详情页数据
     * @param int gather_id 画册ID
     * @param int reader_id 读者ID
     */
    public function get_detail_data()
    {
        $gatherId = I('gather_id');

        // 画册信息
        $gatherInfo = D('Gather')->getGatherDetail($gatherId);
        $gatherInfo['url_arr'] = explode(',', $gatherInfo['url']);

        // 判断用户是否点心
        $isLike = false;
        $cond_like = [
            'gather_id' => $gatherId,
            'reader_id' => I('reader_id'),
            'status'    => C('STATUS_Y')
        ];
        $likeInfo = M('gather_likes')->where($cond_like)->find();
        if ($likeInfo) {
            $isLike = true;
        }

        // 评论信息
        $commentInfo = D('GatherComment')->getComment1st($gatherId);

        $data = [
            'gather'  => $gatherInfo,
            'is_like' => $isLike,
            'comment' => $commentInfo
        ];
        ajax_return(1, 'gather detail', $data);
    }

    /**
     * 发布画册
     * @param int publisher_id 发布人
     * @param varchar gather_title 画册标题
     * @param varchar url 画册图片
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
     * 获取某条评论信息
     * @param int comment_id 评论ID
     */
    public function get_comment_info()
    {
        $data = D('GatherComment')->getCommentInfo(I('comment_id'));
        ajax_return(1, 'comment info', $data);
    }

    /**
     * 评论
     * @param int gather_id 画册ID
     * @param int reader_id 读者ID
     * @param varchar comment_content 评论内容
     */
    public function comment()
    {
        $comment = D('GatherComment');
        $comment->create();

        $content = I('comment_content');
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';
        $handle = SensitiveHelper::init()->setTreeByFile($wordFilePath);
        // 敏感词替换
        $filterContent = $handle->replace($content, C('FILTER_TEXT'));

        $comment->comment_content = $filterContent;
        $comment->pid = 0;
        $res = $comment->add();

        if ($res === false) {
            ajax_return(0, '评论失败');
        }
        ajax_return(1);
    }

    /**
     * 回复评论
     * @param int reader_id 读者ID
     * @param int gather_id 画册Id
     * @param varchar comment_content 评论内容
     */
    public function reply()
    {
        $comment = D('GatherComment');
        $comment->create();

        $content = I('comment_content');
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';
        $handle = SensitiveHelper::init()->setTreeByFile($wordFilePath);
        // 敏感词替换
        $filterContent = $handle->replace($content, C('FILTER_TEXT'));

        $comment->comment_content = $filterContent;
        $comment->add();

        // 消息通知
        $data_notice = [
            'reader_id'   => I('reader_id'),
            'type'        => 3,
            'target'      => I('gather_id'),
            'target_type' => 3,
            'content'     => $filterContent,
            'notice_time' => date('Y-m-d H:i:s'),
            'status'      => C('STATUS_Y')
        ];
        M('notice')->add($data_notice);

        ajax_return(1, 'comment');
    }

}