<?php
namespace Home\Controller;
use Think\Controller;

require './vendor/autoload.php';
use DfaFilter\SensitiveHelper;

class ComicController extends Controller {

    /**
     * 上传图片
     */
    public function upload_img()
    {
        $path = upload_single('gather');
        echo $path;
    }

    /**
     * 阅读
     * 1.章节信息（图片）
     * 2.分享相关（标题、封面、章节）
     * 3.更新阅读历史
     * 4.显示标题信息
     */
    public function reading(){
        $comicId = I('comic_id');
        $readerId = I('reader_id');
        $chapter = I('chapter');
        $channel = I('channel');

        D('History')->updateHistory($comicId, $chapter, $readerId, $channel); // 更新阅读历史

        $data['share_cover'] = D('Chapter')->getChapterCover($comicId, $chapter); // 分享封面

        $data['imgs'] = D('Comics')->getComicDetail($comicId, $chapter);

        $data['chapter_title'] = '第'.$chapter.'章' ;

        ajax_return(1, '阅读漫画', $data);
    }

    /**
     * 验证阅读权限
     * @return 1：免费
     *         2：已购买
     *         3：已分享
     *         -1：充值解锁 + 分享解锁
     *         -2：充值解锁
     *         -3：余额解锁 + 分享解锁
     *         -4：余额解锁
     */
    public function check_auth(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $readerId = I('reader_id');
        $chapter = I('chapter');

        $res = $comic->checkCost($comicId, $chapter, $readerId);

        $data['need_pay'] = $comic->getFieldById($comicId, 'pre_chapter_pay');
        $data['need_share'] = $comic->getNeedShare($comicId, $chapter, $readerId);

        ajax_return($res['status'], '1：免费2：已购买3：已分享-1：充值分享-2：充值-3：余额分享-4：余额', $data);
    }

    /**
     * 获取解锁限制
     * @param comic_id
     * @param chapter
     * @param reader_id
     */
    public function get_auth_info()
    {
        $comic = D('Comics');
        $data = $comic->getComicInfo(I('comic_id'));
        $data['need_share'] = $comic->getNeedShare(I('comic_id'), I('chapter'), I('reader_id'));
        ajax_return(1, '解锁限制', $data);
    }

    /**
     * 转发解锁次数
     */
    public function share_help()
    {
        $help = M('share_help');

        $comicId = I('comic_id');
        $chapter = I('chapter');
        $readerId = I('reader_id');

        $cond = [
            'comic_id'  => $comicId,
            'chapter'   => $chapter,
            'reader_id' => $readerId
        ];
        $helpInfo = $help->where($cond)->find();

        if ($helpInfo) {
            $res = $help->where($cond)->setInc('times',1);
        } else {
            $data = [
                'comic_id'   => $comicId,
                'chapter'    => $chapter,
                'reader_id'  => $readerId,
                'share_time' => date('Y-m-d H:i:s'),
                'times'      => 1
            ];
            $res = $help->add($data);
        }

        if ($res === false) {
            ajax_return(0, '分享解锁失败');
        }
        ajax_return(1);
    }

    /**
     * 解锁
     * @param comic_id 漫画ID
     * @param chapter 章节
     * @param reader_id 读者id
     * @param channel 渠道
     * 1.生成订单
     * 2.更新读者余额
     * 3.更新积分详情
     */
    public function unlock()
    {
        $readerId = I('reader_id');

        // 1.生成订单
        $consume = D('ConsumeOrder');
        $consume->create();
        $consume->order_number = generateOrderNo(C('ORDER_C'));

        $comicInfo = M('comics')->find(I('comic_id'));
        $needPay = $comicInfo['pre_chapter_pay'];
        $consume->consumption = $needPay;

        $res = $consume->add();

        if ($res === false) {
            ajax_return(0, '解锁失败');
        }

        // 2.更新读者余额
        $cond_reader['id'] = $readerId;
        M('reader')->where($cond_reader)->setDec('balance', $needPay);

        // 3.更新积分详情
        $data_integral = [
            'reader_id' => $readerId,
            'content'   => '解锁漫画《'.$comicInfo['title'].'》第'.I('chapter').'章',
            'method'    => 2,
            'points'    => $needPay,
            'create_at' => date('Y-m-d H:i:s')
        ];
        M('integral')->add($data_integral);

        ajax_return(1);
    }



    ////////
    // 评论 //
    ////////

    /**
     * 获取评论
     * @param int comic_id 漫画ID
     */
    public function get_comment_data()
    {
        $data = D('ComicComment')->getComment1st(I('comic_id'));
        ajax_return(1, 'comment data', $data);
    }

    /**
     * 评论
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     * @param comment_content 评论内容
     */
    public function comment()
    {
        $comment = D('ComicComment');
        $comment->create();

        $content = I('comment_content');

        // 获取感词库文件路径
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';

        // get one helper
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
     * @param int comic_id 漫画Id
     * @param varchar comment_content 评论内容
     */
    public function reply()
    {
        $comment = D('ComicComment');
        $comment->create();

        $content = I('comment_content');

        // 获取感词库文件路径
        $wordFilePath = 'vendor/lustre/php-dfa-sensitive/keywords.txt';
        // get one helper
        $handle = SensitiveHelper::init()->setTreeByFile($wordFilePath);
        // 敏感词替换
        $filterContent = $handle->replace($content, C('FILTER_TEXT'));
        $comment->comment_content = $filterContent;
        $comment->add();

        // 消息通知
        $data_notice = [
            'reader_id'   => I('reader_id'),
            'type'        => 3,
            'target'      => I('comic_id'),
            'target_type' => 1,
            'content'     => $filterContent,
            'notice_time' => date('Y-m-d H:i:s'),
            'status'      => C('STATUS_Y')
        ];
        M('notice')->add($data_notice);

        ajax_return(1, 'comment');
    }

    /**
     * 获取某条评论信息
     * @param int comment_id 评论ID
     */
    public function get_comment_info()
    {
        $data = D('ComicComment')->getCommentInfo(I('comment_id'));
        ajax_return(1, 'comment info', $data);
    }

    /**
     * 获取读者信息
     * @param int reader_id 读者ID
     */
    public function get_reader()
    {
        $data = M('reader')->find(I('reader_id'));
        ajax_return(1, '读者信息', $data);
    }





    ////////
    // 登录 //
    ////////

    /**
     * 登录凭证校验
     * @param js_code 登录凭证code
     */
    public function code_2_session()
    {
        $appid = C('WX_CONFIG.APPID');
        $secret = C('WX_CONFIG.APPSECRET');
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.I('js_code').'&grant_type=authorization_code';
        $info = file_get_contents($url);
        $json = json_decode($info, true);

        $reader = D('Reader');
        $openid = $json['openid'];
        $cond_reader = [
            'status' => C('STATUS_Y'),
            'openid' => $openid
        ];
        $readerInfo = $reader
            ->where($cond_reader)
            ->find();

        if (!$readerInfo) {
            $reader->create();
            $reader->openid = $openid;
            $res = $reader->add();
            $readerInfo = $reader->find($res);
        }

        $json = array_merge($json,$readerInfo);

        ajax_return(1, '凭证校验', $json);
    }

    /**
     * 编辑读者信息
     */
    public function edit_reader(){
        $cond = [
            'status' => C('STATUS_Y'),
            'id'     => I('reader_id')
        ];
        $reader = M('reader');
        $reader->create();
        $reader->updated_at = date('Y-m-d H:i:s');
        $res = $reader->where($cond)->save();

        $data = $reader
            ->where($cond)
            ->find();

        if ($res === false) {
            ajax_return(0, '更新读者信息失败');
        }
        ajax_return(1, '更新读者信息成功', $data);
    }




    ////////
    // 漫画 //
    ////////


    /**
     * 猜你喜欢
     * 3条
     * @param int reader_id 读者ID
     */
    public function get_like_comic()
    {
        $data = D('Comics')->getLikeComic(I('reader_id'));

        ajax_return(1, '喜欢漫画', $data);
    }

    /**
     * 漫画专题
     */
    public function get_subject_comic()
    {
        $data = D('Subject')->getSubject();
        ajax_return(1, '专题', $data);
    }

    /**
     * 获取漫画类型
     */
    public function get_comic_type()
    {
        $all[] = [
            'id'              => '-1',
            'comic_type_name' => '全部'
        ];
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_type')
            ->where($cond)
            ->select();

        $data = array_merge($all, $data);

        $count = count($data);
        $mod = 6-$count%6;

        $place[] = [
            'id'              => '-2',
            'comic_type_name' => '占位'
        ];
        for ($i=0; $i < $mod; $i++) {
            $data = array_merge($data, $place);
        }

        ajax_return(1, '漫画类型', $data);
    }

    /**
     * 获取漫画banner
     */
    public function get_comic_banner(){
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_banner')
            ->where($cond)
            ->order('sort desc')
            ->select();
        ajax_return(1, '漫画banner列表', $data);
    }

    /**
     * 按发布类型获取漫画列表
     * 1.按sort倒叙
     * 2.限制数量yuanche
     * 3.只get类型下设置漫画的
     */
    public function get_comic_by_release(){
        $cond['status'] = C('STATUS_Y');
        $data = M('release_type')
            ->field('id as release_type_id,release_type_name')
            ->order('sort desc')
            ->where($cond)
            ->select();

        $comic = M('comics');
        foreach ($data as $key => $value) {
            $cond_comic = [
                'status' => array('neq', C('STATUS_N')), // 上架+未开放
                'release_type_id' => $value['release_type_id']
            ];
            $comicList = $comic
                ->field('id as comic_id,cover,title,brief')
                ->limit(C('INDEX_SHOW'))
                ->order('sort desc')
                ->where($cond_comic)
                ->select();

            if ($comicList) {
                $data[$key]['comics'] = $comicList;
            } else {
                unset($data[$key]);
            }
        }

        ajax_return(1, '漫画列表', array_filter($data));
    }

    /**
     * 获取漫画列表
     * @param type 漫画类型 -1：全部
     */
    public function get_comic_list(){
        $cond['status'] = array('neq', C('STATUS_N'));

        // 漫画类型
        $type = I('type');
        if ($type != '-1') {
            $cond['_string'] = 'FIND_IN_SET('.$type.', type_ids)';
        }

        $data = D(Comics)->getComicApiData($cond);

        $type = M('comic_type');
        foreach ($data as $key => $value) {
            $data[$key]['brief'] = strip_tags(htmlspecialchars_decode($value['brief'])); // 去掉html标记
            $cond_type = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['type_ids'])
            ];
            $typeArr = $type->where($cond_type)->getField('comic_type_name', true);
            $data[$key]['type_names'] = implode('；', $typeArr);
        }

        ajax_return(1, '漫画列表', $data);
    }

    /**
     * 获取漫画信息
     */
    public function get_comic_info(){
        $comic = M('comics');
        $comicId = I('comic_id');
        $readerId = I('reader_id');

        $cond['id'] = $comicId;

        $comic->where($cond)->setInc('popularity',1); // 查看+1
        $data = $comic->where($cond)->field('*,id as comic_id')->find();
        $data['brief'] = htmlspecialchars_decode($data['brief']);

        $cond_reader = [
            'comic_id'  => $comicId,
            'reader_id' => $readerId
        ];
        $data['is_collect'] = M('collect')->where($cond_reader)->getField('status');
        $data['is_like'] = M('likes')->where($cond_reader)->getField('status');

        $cond_type = [
            'status' => C('STATUS_Y'),
            'id'     => array('in', $data['type_ids'])
        ];
        $data['types'] = M('comic_type')
            ->where($cond_type)
            ->getField('comic_type_name', true);

        $data['s_serial_name'] = $data['s_serial'] == C('C_SERIAL_L') ? '连载中' : '已完结';

        ajax_return(1, '漫画信息', $data);
    }

    /**
     * 获取漫画章节列表
     * @param comic_id 漫画ID
     * @return catalog_name 第1章
     */
    public function get_comic_chapter(){
        $cond_comic['id'] = I('comic_id');
        $comicInfo = M('comics')->where($cond_comic)->find();
        $freeChapter = $comicInfo['free_chapter'];

        $cond = [
            'status'   => C('STATUS_Y'),
            'comic_id' => I('comic_id')
        ];
        $data = M('chapter')
            ->where($cond)
            ->order('catalog')
            ->select();

        foreach ($data as $key => $value) {
            $chapterTitle = $value['chapter_title'] ? ' '.$value['chapter_title'] : '';
            // 判断是否只有一章(完结)
            if (count($data) == 1 && $comicInfo['s_serial'] == C('C_SERIAL_W')) {
                $data[$key]['catalog_name'] = '全一册'.$chapterTitle;
            } else {
                $data[$key]['catalog_name'] = '第'.$value['catalog'].'章'.$chapterTitle;
            }

            if ($value['catalog'] > $freeChapter) {
                $data[$key]['is_fee'] = 1;
            }
        }

        ajax_return(1, '漫画章节', $data);
    }

    /**
     * 直接阅读,判断章节
     * @param int comic_id 漫画ID
     * @param int reader_id 读者ID
     */
    public function get_reading_chapter(){
        $comicId = I('comic_id');
        $readerId = I('reader_id');

        $cond_history = [
            'comic_id'  => $comicId,
            'reader_id' => $readerId
        ];
        $historyInfo = M('history')->where($cond_history)->find();

        if ($historyInfo) {
            // 历史记录
            $chapter = $historyInfo['chapter'];
        } else {
            // 首次
            $chapter = 1;
        }

        ajax_return(1, '章节', $chapter);
    }


    /**
     * 下一章
     * @param comic_id 漫画ID
     * @param reader_id 读者ID
     * @param chapter 章节
     * @param channel 途径
     */
    public function reading_next(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $readerId = I('reader_id');
        $chapter = I('chapter') + 1;
        $channel = I('channel');

        $res = $comic->checkCost($comicId, $chapter, $readerId);

        if ($res['status'] == '-1') {
            // 限制阅读
            $comicInfo = $comic->getComicInfo($comicId);
            $comicInfo['chapter_cover'] = D('Chapter')->getChapterCover($comicId, $chapter); // 章节封面
            $comicInfo['need_share'] = $res['data']['need_share']; // 需要分享数
            ajax_return('-1', '限制阅读', $comicInfo);
        }

        D('History')->updateHistory($comicId, $chapter, $readerId, $channel); // 更新阅读历史
        $data['comics'] = $comic->getComicDetail($comicId, $chapter);
        $data['chapter_title'] = '第'.$chapter.'章' ;

        ajax_return(1, '漫画阅读', $data);
    }

    /**
     * 获取章节封面
     */
    public function get_chapter_cover()
    {
        $comicId = I('comic_id');
        $chapter = I('chapter');
        $data = D('Chapter')->getChapterCover($comicId, $chapter);

        ajax_return(1, '章节封面', $data);
    }

}