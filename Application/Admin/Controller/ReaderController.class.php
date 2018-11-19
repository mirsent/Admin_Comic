<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ReaderController extends AdminBaseController{

    /**
     * 获取收藏信息
     */
    public function get_collect_info()
    {
        $ms = D('Collect');

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
}