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



        $data = [
            'banner' => $banner
        ];
        ajax_return(1, 'novel_data', $data);
    }
}