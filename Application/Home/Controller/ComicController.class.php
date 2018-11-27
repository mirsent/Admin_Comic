<?php
namespace Home\Controller;
use Think\Controller;
class ComicController extends Controller {

    /**
     * 阅读
     * 1.章节信息（图片）
     * 2.分享相关（标题、封面、章节）
     * 3.更新阅读历史
     * 4.显示标题信息
     */
    public function reading(){
        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter');
        $channel = I('channel');

        D('History')->updateHistory($comicId, $chapter, $openid, $channel); // 更新阅读历史

        $data['share_cover'] = D('Chapter')->getChapterCover($comicId, $chapter); // 分享封面

        $data['imgs'] = D('Comics')->getComicDetail($comicId, $chapter);

        $data['chapter_title'] = '第'.$chapter.'章' ;

        ajax_return(1, '阅读漫画', $data);
    }

    /**
     * 验证阅读权限
     * @return -1 付费 或 分享
     * @return -2 付费
     */
    public function check_auth(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter');

        $res = $comic->checkCost($comicId, $chapter, $openid);

        $data['need_pay'] = $comic->getFieldById($comicId, 'pre_chapter_pay');
        $data['need_share'] = $comic->getNeedShare($comicId, $chapter, $openid);

        if ($res['status'] == '-1') {
            ajax_return('-1', '付费、分享', $data);
        }
        if ($res['status'] == '-2') {
            ajax_return('-2', '付费', $data);
        }

        ajax_return(1);
    }

    /**
     * 获取解锁限制
     * @param comic_id
     * @param chapter
     * @param openid
     */
    public function get_auth_info()
    {
        $comic = D('Comics');
        $data = $comic->getComicInfo(I('comic_id'));
        $data['need_share'] = $comic->getNeedShare(I('comic_id'), I('chapter'), I('openid'));
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
        $openid = I('openid');

        $cond = [
            'comic_id' => $comicId,
            'chapter'  => $chapter,
            'openid'   => $openid
        ];
        $helpInfo = $help->where($cond)->find();

        if ($helpInfo) {
            $res = $help->where($cond)->setInc('times',1);
        } else {
            $data = [
                'comic_id'   => $comicId,
                'chapter'    => $chapter,
                'openid'     => $openid,
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
     * 登录
     */

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

        $reader = M('reader');
        $openid = $json['openid'];
        $cond_reader = [
            'status' => C('STATUS_Y'),
            'openid' => $openid
        ];
        $readerInfo = $reader
            ->where($cond_reader)
            ->field('openid,proxy_id,nickname,head,balance,own')
            ->find();

        if ($readerInfo) {
            $json = array_merge($json,$readerInfo);
        } else {
            $now = date('Y-m-d H:i:s');
            $data_reader = [
                'openid'          => $openid,
                'registered_time' => $now,
                'status'          => C('STATUS_Y'),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
            $res = $reader->add($data_reader);
        }

        ajax_return(1, '凭证校验', $json);
    }

    /**
     * 编辑读者信息
     */
    public function edit_reader(){
        $cond = [
            'status' => C('STATUS_Y'),
            'openid' => I('openid')
        ];
        $reader = M('reader');
        $reader->create();
        $reader->updated_at = date('Y-m-d H:i:s');
        $res = $reader->where($cond)->save();

        $data = $reader
            ->where($cond)
            ->field('openid,proxy_id,nickname,head,balance,own')
            ->find();

        if ($res === false) {
            ajax_return(0, '更新读者信息失败');
        }
        ajax_return(1, '更新读者信息成功', $data);
    }


    /**
     * 漫画
     */

    /**
     * 获取漫画类型
     */
    public function get_comic_type()
    {
        $all[] = [
            'id'              => '-1',
            'comic_type_name' => '全部',
            'is_on'           => true
        ];
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_type')
            ->where($cond)
            ->field('*,null as is_on')
            ->select();
        ajax_return(1, '漫画类型', array_merge($all, $data));
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
        $cond['c.status'] = array('neq', C('STATUS_N'));

        // 发布类型
        $release = I('release');
        if ($release) {
            $cond['release_type_id'] = $release;
        }

        // 漫画类型
        $type = I('type');
        if ($type != '-1') {
            $cond['_string'] = 'FIND_IN_SET('.$type.', type_ids)';
        }

        $data = M('comics')
            ->alias('c')
            ->join('__RELEASE_TYPE__ rt ON rt.id = c.release_type_id')
            ->field('c.*,c.id as comic_id,release_type_name')
            ->order('sort desc')
            ->where(array_filter($cond))
            ->select();

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
        $openid = I('openid');

        $cond['id'] = $comicId;

        $comic->where($cond)->setInc('popularity',1); // 查看+1
        $data = $comic->where($cond)->field('*,id as comic_id')->find();
        $data['brief'] = htmlspecialchars_decode($data['brief']);

        $cond_reader = [
            'comic_id' => $comicId,
            'openid'   => $openid
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
            ->select();

        foreach ($data as $key => $value) {
            $chapterTitle = $value['chapter_title'] ? ' '.$value['chapter_title'] : '';
            $data[$key]['catalog_name'] = '第'.$value['catalog'].'章'.$chapterTitle;
            if ($value['catalog'] > $freeChapter) {
                $data[$key]['is_fee'] = 1;
            }
        }

        ajax_return(1, '漫画章节', $data);
    }

    /**
     * 直接阅读,判断章节
     */
    public function get_reading_chapter(){
        $comicId = I('comic_id');
        $openid = I('openid');

        $cond_history = [
            'comic_id' => $comicId,
            'openid'   => $openid
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
     */
    public function reading_next(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter') + 1;
        $channel = I('channel');

        $res = $comic->checkCost($comicId, $chapter, $openid);

        if ($res['status'] == '-1') {
            // 限制阅读
            $comicInfo = $comic->getComicInfo($comicId);
            $comicInfo['chapter_cover'] = D('Chapter')->getChapterCover($comicId, $chapter); // 章节封面
            $comicInfo['need_share'] = $res['data']['need_share']; // 需要分享数
            ajax_return('-1', '限制阅读', $comicInfo);
        }

        D('History')->updateHistory($comicId, $chapter, $openid, $channel); // 更新阅读历史
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

    /**
     * 获取历史记录
     * @param string openid
     */
    public function get_history_info()
    {
        $data = D('History')->getHistoryList(I('openid'));
        ajax_return(1, '历史记录', $data);
    }



    /**
     * 读者
     */

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
}