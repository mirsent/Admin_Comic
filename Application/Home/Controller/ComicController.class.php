<?php
namespace Home\Controller;
use Think\Controller;
class ComicController extends Controller {

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
     * 2.限制数量
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
                'status' => C('STATUS_Y'),
                'release_type_id' => $value['release_type_id']
            ];
            $comicList = $comic
                ->field('id,cover,title,brief')
                ->limit(C('INDEX_SHOW'))
                ->order('sort desc')
                ->where($cond_comic)
                ->select();

            if ($comicList) {
                $data[$key]['products'] = $comicList;
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
        $cond['c.status'] = C('C_STATUS_U');

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
            ->field('c.*,release_type_name')
            ->order('sort desc')
            ->where(array_filter($cond))
            ->select();

        $type = M('comic_type');
        foreach ($data as $key => $value) {
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
        $cond['c.id'] = I('comic_id');
        $data = M('comics')
            ->alias('c')
            ->join('__COLLECT__ collect ON collect.comic_id = c.id', 'LEFT')
            ->join('__LIKES__ likes ON likes.comic_id = c.id', 'LEFT')
            ->field('c.id,cover,title,brief,type_ids,heat,popularity,total_chapter,s_serial,collect.id as is_collect,likes.id as is_like')
            ->where($cond)
            ->find();
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
        $comicInfo = M('comics')->where($cond)->find();
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
     * 验证阅读权限
     * @return -1 限制阅读
     */
    public function check_auth(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter');

        $status = $comic->checkCost($comicId, $chapter, $openid);

        if ($status == '-1') {
            // 限制阅读
            $comicInfo = $comic->getComicInfo($comicId);
            ajax_return('-1', '限制阅读', $comicInfo);
        }
        ajax_return(1);
    }

    /**
     * 阅读
     */
    public function reading(){
        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter');

        D('History')->updateHistory($comicId, $chapter, $openid); // 更新阅读历史

        $data['comics'] = D('Comics')->getComicDetail($comicId, $chapter);
        $data['chapter_title'] = '第'.$chapter.'章' ;

        ajax_return(1, '漫画阅读', $data);
    }

    /**
     * 下一章
     */
    public function reading_next(){
        $comic = D('Comics');

        $comicId = I('comic_id');
        $openid = I('openid');
        $chapter = I('chapter') + 1;

        $status = $comic->checkCost($comicId, $chapter, $openid);

        if ($status == '-1') {
            // 限制阅读
            $comicInfo = $comic->getComicInfo($comicId);
            ajax_return('-1', '限制阅读', $comicInfo);
        }

        D('History')->updateHistory($comicId, $chapter, $openid); // 更新阅读历史
        $data['comics'] = $comic->getComicDetail($comicId, $chapter);
        $data['chapter_title'] = '第'.$chapter.'章' ;

        ajax_return(1, '漫画阅读', $data);
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
        $data = [
            'comic_id'  => I('comic_id'),
            'openid'    => I('openid'),
            'create_at' => date('Y-m-d H:i:s'),
            'status'    => C('STATUS_Y')
        ];
        $res = M('likes')->add($data);

        if ($res === false) {
            ajax_return(0, '点赞失败');
        }
        ajax_return(1);
    }

    /**
     * 收藏
     * @param comic_id 漫画ID
     * @param openid 读者ID
     */
    public function collect(){
        $data = [
            'comic_id'  => I('comic_id'),
            'openid'    => I('openid'),
            'create_at' => date('Y-m-d H:i:s'),
            'status'    => C('STATUS_Y')
        ];
        $res = M('collect')->add($data);

        if ($res === false) {
            ajax_return(0, '收藏失败');
        }
        ajax_return(1);
    }
}