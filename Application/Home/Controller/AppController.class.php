<?php
namespace Home\Controller;
use Think\Controller;

class AppController extends Controller {

    /**
     * 获取首页数据
     */
    public function get_index_data()
    {
        $cond['status'] = C('STATUS_Y');

        // 最新
        $newData = D('Comics')->order('updated_at desc')->limit(5)->getComicApiData($cond);
        foreach ($newData as $key => $value) {
            $newData[$key]['brief'] = htmlspecialchars_decode($value['brief']);
        }

        // 推荐
        $cond_recommend['r.status'] = C('STATUS_Y');
        $recommendData = D('Recommend')->order('r.sort')->getRecommendApi($cond_recommend);
        foreach ($recommendData as $key => $value) {
            $recommendData[$key]['brief'] = strip_tags(htmlspecialchars_decode($value['brief']));
        }

        // 专题
        $subjectData = D('Subject')->getSubject();

        // 猜你喜欢
        $likeData = D('Comics')->getLikeComic(I('openid'));

        $data = [
            'new' => $newData,
            'recommend' => $recommendData,
            'subject' => $subjectData,
            'like' => $likeData
        ];
        ajax_return(1, '', $data);
    }

    /**
     * 分配读者账号
     */
    public function assign_reader()
    {
        $nickname = substr(md5(time().rand(0,9)), '-6');
        $reader = M('reader');
        $data = [
            'nickname' => $nickname,
            'status' => 1,
            'registered_date' => date('Y-m-d'),
            'registered_time' => date('Y-m-d H:i:s')
        ];
        $res = $reader->add($data);

        $readerInfo = $reader->find($res);
        ajax_return(1, '分配账号', $readerInfo);
    }

    /**
     * 搜索联想
     */
    public function search_associate()
    {
        $search = I('search');
        $cond['title'] = array('like', '%'.$search.'%');

        $type = I('type');
        if ($type == 2) {
            $titles = M('novel')->where($cond)->field('id,title')->select();
        } else {
            $titles = M('comics')->where($cond)->field('id,title')->select();
        }

        foreach ($titles as $key => $value) {
            $titles[$key]['title_filter'] = str_replace($search, '<span class="keyword">'.$search.'</span>', $value['title']);
        }
        ajax_return(1, '搜索联想', $titles);
    }

    /**
     * 搜索
     */
    public function search_result()
    {
        $search = I('search');
        $cond['title'] = array('like', '%'.$search.'%');

        $type = I('type');
        if ($type == 2) {
            $data = M('novel')->where($cond)->select();
        } else {
            $data = M('comics')->where($cond)->select();
        }

        foreach ($data as $key => $value) {
            $data[$key]['title_filter'] = str_replace($search, '<span class="keyword">'.$search.'</span>', $value['title']);
        }
        ajax_return(1, '搜索', $data);
    }

    /**
     * 获取参数
     */
    public function get_cog()
    {
        $data = M('cog')->find(1);
        ajax_return(1, 'cog', $data);
    }
}