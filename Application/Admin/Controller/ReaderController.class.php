<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ReaderController extends AdminBaseController{

    /**
     * 获取读者信息
     */
    public function get_reader_info()
    {
        $ms = D('Reader');

        $cond['r.status'] = C('STATUS_Y');

        $recordsTotal = $ms->alias('r')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['nickname'] = array('like', '%'.$search.'%');
        }
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['registered_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getReaderNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('amount '.$orderDir); break;
                case 3: $ms->order('balance '.$orderDir); break;
                case 4: $ms->order('registered_time '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('registered_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getReaderData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 设置代理
     */
    public function set_proxy()
    {
        $cond['id'] = I('id');
        $data['is_proxy'] = 1;
        $res = M('reader')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '设置代理失败');
        }
        ajax_return(1);
    }

    /**
     * 获取收藏信息
     */
    public function get_collect_info()
    {
        $ms = D('Collect');

        $cond['ct.status'] = C('STATUS_Y');

        $recordsTotal = $ms->alias('ct')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $cond['channel'] = I('channel');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['create_at'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getCollectNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('c.title '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('create_at '.$orderDir); break;
                case 3: $ms->order('channel '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('create_at');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getCollectData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }



    /**
     * 获取阅读历史信息
     */
    public function get_history_info()
    {
        $ms = D('History');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $cond['channel'] = I('channel');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['last_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getHistoryNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('c.title '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('chapter '.$orderDir); break;
                case 3: $ms->order('last_time '.$orderDir); break;
                case 4: $ms->order('channel '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('last_time');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getHistoryData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }



    /**
     * 获取点赞信息
     */
    public function get_likes_info()
    {
        $ms = D('Likes');

        $cond['l.status'] = C('STATUS_Y');

        $recordsTotal = $ms->alias('l')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $cond['channel'] = I('channel');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['create_at'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getLikesNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('c.title '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('create_at '.$orderDir); break;
                case 3: $ms->order('channel '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('create_at');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getLikesData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }



    /**
     * 获取积分信息
     */
    public function get_integral_info()
    {
        $ms = D('Integral');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['nickname|content'] = array('like', '%'.$search.'%');
        }
        $cond['nickname'] = I('nickname');
        $cond['method'] = I('method');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['create_at'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getIntegralNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('nickname '.$orderDir); break;
                case 1: $ms->order('content '.$orderDir); break;
                case 2: $ms->order('method '.$orderDir); break;
                case 3: $ms->order('points '.$orderDir); break;
                case 4: $ms->order('create_at '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('create_at');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getIntegralData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }
}