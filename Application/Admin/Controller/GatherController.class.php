<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class GatherController extends AdminBaseController {
    /**
     * 获取画册列表信息
     */
    public function get_gather_info()
    {
        $ms = D('Gather');

        $cond['g.status'] = C('APPLY_P');

        $recordsTotal = $ms->alias('g')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['gather_title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['gather_title'] = I('gather_title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['publish_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getGatherNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('gather_title '.$orderDir); break;
                case 2: $ms->order('like '.$orderDir); break;
                case 3: $ms->order('nickname '.$orderDir); break;
                case 4: $ms->order('publish_time '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('publish_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getGatherData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    public function input_gather()
    {
        $rules = array (
            array('status','2'),
            array('publisher_id','-1'),
            array('like','0'),
            array('publish_time','get_datetime',1,'callback')
        );
        $gather = D('Gather');
        $gather->auto($rules)->create();

        $id = I('id');
        if ($id) {
            $cond['id'] = $id;
            $res = $gather->where($cond)->save();
        } else {
            $res = $gather->add();
        }

        if ($res === false) {
            ajax_return(0, '编辑画册失败');
        }
        ajax_return(1);
    }

    public function delete_gather()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('gather')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除画册失败');
        }
        ajax_return(1);
    }


    /**
     * 获取画册申请列表信息
     */
    public function get_gather_apply_info()
    {
        $ms = D('Gather');

        $cond['g.status'] = C('APPLY_I');

        $recordsTotal = $ms->alias('g')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['gather_title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['gather_title'] = I('gather_title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['publish_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getGatherNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('gather_title '.$orderDir); break;
                case 2: $ms->order('like '.$orderDir); break;
                case 3: $ms->order('nickname '.$orderDir); break;
                case 4: $ms->order('publish_time '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('publish_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getGatherData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取画册驳回列表信息
     */
    public function get_gather_ban_info()
    {
        $ms = D('Gather');

        $cond['g.status'] = C('APPLY_B');

        $recordsTotal = $ms->alias('g')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['gather_title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['gather_title'] = I('gather_title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['publish_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getGatherNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('gather_title '.$orderDir); break;
                case 2: $ms->order('like '.$orderDir); break;
                case 3: $ms->order('nickname '.$orderDir); break;
                case 4: $ms->order('publish_time '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('publish_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getGatherData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    public function pass_gather()
    {
        $cond['id'] = I('id');
        $data['status'] = C('APPLY_P');
        $res = M('gather')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '通过画册失败');
        }
        ajax_return(1);
    }

    public function ban_gather()
    {
        $cond['id'] = I('id');
        $data['status'] = C('APPLY_B');
        $res = M('gather')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '驳回画册失败');
        }
        ajax_return(1);
    }
}