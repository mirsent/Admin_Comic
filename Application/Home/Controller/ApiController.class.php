<?php
namespace Home\Controller;
use Think\Controller;

class ApiController extends Controller {

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
        $recommendData = D('Recommend')->order('r.sort')->getRecommendData($cond_recommend);
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
}