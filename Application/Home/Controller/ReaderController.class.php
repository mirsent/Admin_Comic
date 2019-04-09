<?php
namespace Home\Controller;
use Think\Controller;

require './vendor/autoload.php';
use DfaFilter\SensitiveHelper;

class ReaderController extends Controller {

    /**
     * 获取读者信息
     * @param int reader_id 读者ID
     */
    public function get_reader_info()
    {
        $readerId = I('reader_id');
        $data = M('reader')->find($readerId);

        ajax_return(1, '读者信息', $data);
    }

    /**
     * 获取通知
     * @param int reader_id 读者ID
     */
    public function get_notice_list()
    {
        $notice = M('notice');
        $readerId = I('reader_id');

        // 公告
        $cond_notice_announce = [
            'reader_id' => $readerId,
            'type'      => 1,
            'status'    => C('STATUS_Y')
        ];
        $announceLast = $notice->where($cond_notice_announce)->order('notice_time desc')->getField('notice_time');
        if ($announceLast) {
            $cond_announce = [
                'announce_time' => array('gt', $announceLast),
                'status'        => C('STATUS_Y')
            ];
            $announceData = M('announce')->where($cond_announce)->select();
            foreach ($announceData as $key => $value) {
                $data_notice_announce[] = [
                    'reader_id'   => $readerId,
                    'type'        => 1,
                    'target'      => $value['id'],
                    'content'     => $value['announce_title'],
                    'notice_time' => $value['announce_time'],
                    'status'      => C('STATUS_Y')
                ];
            }
            $notice->addAll($data_notice_announce);
        }

        // 活动
        $cond_notice_activity = [
            'reader_id' => $readerId,
            'type'      => 2,
            'status'    => C('STATUS_Y')
        ];
        $activityLast = $notice->where($cond_notice_announce)->order('notice_time desc')->getField('notice_time');
        if ($activityLast) {
            $cond_announce = [
                'activity_time' => array('gt', $activityLast),
                'status'        => C('STATUS_Y')
            ];
            $activityData = M('recharge_activity')->where($cond_announce)->select();
            foreach ($activityData as $key => $value) {
                $data_notice_activity[] = [
                    'reader_id'   => $readerId,
                    'type'        => 2,
                    'target'      => $value['id'],
                    'content'     => $value['activity_title'],
                    'notice_time' => $value['activity_time'],
                    'status'      => C('STATUS_Y')
                ];
            }
            $notice->addAll($data_notice_activity);
        }

        $cond_notice = [
            'reader_id' => $readerId,
            'status'    => C('STATUS_Y')
        ];
        $data = $notice->where($cond_notice)->order('notice_time desc')->select();
        foreach ($data as $key => $value) {
            $data[$key]['notice_time_text'] = date('m/d', strtotime($value['notice_time']));
        }

        ajax_return(1, '消息', $data);
    }

    /**
     * 获取读者反馈列表
     */
    public function get_feedback_list()
    {
        $cond['reader_id'] = I('reader_id');
        $data = M('feedback')->where($cond)->select();
        foreach ($data as $key => $value) {
            $data[$key]['status_text'] = $value['status'] == C('STATUS_Y') ? '待回复' : '已回复';
            $data[$key]['feedback_time_text'] = date('Y-m-d H:i', strtotime($value['feedback_time']));
        }
        ajax_return(1, 'feedback list', $data);
    }







    /******************************************* 明细 *******************************************/

    /**
     * 积分明细
     * @param int reader_id 读者ID
     */
    public function get_integral_list()
    {
        $cond['reader_id'] = I('reader_id');
        $data = M('integral')->where($cond)->select();
        foreach ($data as $key => $value) {
            $data[$key]['points_text'] = $value['method'] == 1 ? '+'.$value['points'] : '-'.$value['points'];
        }
        ajax_return(1, '积分明细', $data);
    }


    /**
     * 消费记录
     * @param reader_id 读者ID
     */
    public function get_consume_note()
    {
        $cond = [
            'reader_id' => I('reader_id'),
            'method'    => 2
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '消费记录', $data);
    }

    /**
     * 充值记录
     * @param reader_id 读者ID
     */
    public function get_recharge_note()
    {
        $cond = [
            'reader_id' => I('reader_id'),
            'method'    => 1
        ];
        $data = M('integral')
            ->where($cond)
            ->order('create_at desc')
            ->select();
        ajax_return(1, '充值记录', $data);
    }




    /******************************************* 画册 *******************************************/

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
     * 获取读者画册
     * @param reader_id 读者ID
     */
    public function get_raeder_gather_info()
    {
        $readerId = I('reader_id');
        // 发布画册
        $cond_gather = [
            'publisher_id' => $readerId,
            'g.status'     => C('APPLY_P')
        ];
        $publishGather = D('Gather')->getGatherData($cond_gather);
        // 喜欢的画册
        $cond_gather_like = [
            'reader_id' => $readerId,
            'l.status'  => C('STATUS_Y')
        ];
        $likeGather = D('GatherLikes')->getLikesData($cond_gather_like);
        foreach ($likeGather as $key => $value) {
            $cond_like = [
                'status'    => C('STATUS_Y'),
                'gather_id' => $value['gather_id']
            ];
            $likeGather[$key]['like_number'] = D('GatherLikes')->where($cond_like)->count();
        }

        $data = [
            'publish' => $publishGather,
            'like'    => $likeGather
        ];
        ajax_return(1, '读者画册', $data);
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
     * @param reply_reader_id 回复的读者ID
     */
    public function get_reply()
    {
        $cond['reply_reader_id'] = I('reply_reader_id');

        $data = M('comment')
            ->alias('c')
            ->join('__READER__ r ON r.id = c.reader_id')
            ->join('__COMICS__ comic ON comic.id = c.comic_id')
            ->field('c.*,nickname,r.head,title,comic.title as comic_title')
            ->order('comment_time desc')
            ->where($cond)
            ->select();

        ajax_return(1, '回复内容', $data);
    }




    /******************************************* 点赞 *******************************************/

    /**
     * 点赞
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     */
    public function like(){
        $likes = D('Likes');
        $likes->create();

        $comicId = I('comic_id');
        $readerId = I('reader_id');

        $cond = [
            'comic_id'  => $comicId,
            'reader_id' => $readerId
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
            'comic_id'  => I('comic_id'),
            'reader_id' => I('reader_id')
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




    /******************************************* 收藏 *******************************************/

    /**
     * 收藏
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     */
    public function collect(){
        $collect = D('Collect');
        $collect->create();

        $comicId = I('comic_id');
        $readerId = I('reader_id');

        $cond = [
            'comic_id'  => $comicId,
            'reader_id' => $readerId
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
            'comic_id'  => I('comic_id'),
            'reader_id' => I('reader_id')
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
    public function get_collect()
    {
        $readerId = I('reader_id');
        $comic = D('Collect')->getCollectList($readerId);
        $novel = D('NovelCollect')->getCollectList($readerId);

        $data = [
            'comic' => $comic,
            'novel' => $novel
        ];

        ajax_return(1, '我的收藏', $data);
    }




    /******************************************* 需求墙 *******************************************/

    /**
     * 获取需求
     * @param reader_id 读者ID
     */
    public function get_wish()
    {
        $cond['w.status'] = array('neq', C('STATUS_N'));
        $data = D('Wish')->getWishData($cond);

        $like = M('wish_likes');
        $readerId = I('reader_id');
        foreach ($data as $key => $value) {
            $cond_like = [
                'status'  => C('STATUS_Y'),
                'wish_id' => $value['id']
            ];
            $cond_reader['reader_id'] = $readerId;
            $data[$key]['number_like'] = $like->where($cond_like)->count();
            $data[$key]['is_like'] = $like->where($cond_reader)->where($cond_like)->find();
        }

        ajax_return(1, '需求', $data);
    }

    /**
     * 发布需求
     * @param reader_id 读者ID
     * @param wish_title 需求
     */
    public function wish()
    {
        $wish = D('Wish');
        $wish->create();
        $res = $wish->add();

        $wishLikes = D('WishLikes');
        $wishLikes->create();
        $wishLikes->wish_id = $res;
        $wishLikes->add();

        ajax_return(1, '发布需求');
    }

    /**
     * 需求点赞
     * @param reader_id 读者ID
     * @param wish_id 需求ID
     */
    public function wish_like()
    {
        $wishLikes = D('WishLikes');
        $wishLikes->create();
        $wishLikes->add();

        ajax_return(1, '需求点赞');
    }




    /******************************************* 签到 *******************************************/

    /**
     * 签到
     * @param int reader_id 读者ID
     */
    public function sign()
    {
        $sign = D('Sign');
        $readerId = I('reader_id');
        $cond['reader_id'] = $readerId;
        $signInfo = $sign->where($cond)->find();

        if ($signInfo['last_signdate'] >= date("Y-m-d",strtotime("-1 day"))) {
            $days = $signInfo['days'];
        } else {
            $days = 0;
        }

        $days = (intval($days) + 1) % 8; // 连续签到天数

        // 积分
        $cogSign = M('cog_sign')->where(['status'=>C('STATUS_Y')])->getField('days,integral');
        $integral = $cogSign[$days];
        M('reader')->where(['id'=>$readerId])->setInc('integral', $integral);

        // 积分详情
        $data_integral = [
            'reader_id' => $readerId,
            'content'   => '连续签到'.$days.'天',
            'method'    => 1,
            'points'    => $integral,
            'create_at' => date('Y-m-d H:i:s')
        ];
        M('integral')->add($data_integral);


        if ($signInfo) {
            $data = [
                'days'          => $days,
                'last_signdate' => date('Y-m-d')
            ];
            $sign->where($cond)->save($data);
        } else {
            $sign->create();
            $sign->last_signdate = date('Y-m-d');
            $sign->days = $days;
            $sign->add();
        }

        ajax_return(1, 'sign', $days);
    }

    /**
     * 获取用户连续签到天数
     * @param int reader_id 读者ID
     * @return int days 连续签到天数
     * @return int is_sign 今天是否签到
     */
    public function get_sign_days()
    {
        $sign = M('sign');
        $cond['reader_id'] = I('reader_id');
        $signInfo = $sign->where($cond)->find();

        if (strtotime($signInfo['last_signdate']) + 86400 >= time()) {
            $data['days'] = $signInfo['days'];
        } else {
            $data['days'] = 0;
        }

        if ($signInfo['last_signdate'] == date('Y-m-d')) {
            $data['is_sign'] = 1;
        } else {
            $data['is_sign'] = 0;
        }

        ajax_return(1, 'sing days', $data);
    }




    /******************************************* 阅读历史 *******************************************/

    /**
     * 阅读历史记录
     * @return array comic 漫画
     * @return array novel 小说
     */
    public function get_history()
    {
        $readerId = I('reader_id');
        $comic = D('History')->getHistoryList($readerId);
        $novel = D('NovelHistory')->getHistoryList($readerId);

        $data = [
            'comic' => $comic,
            'novel' => $novel
        ];
        ajax_return(1, 'history', $data);
    }
}