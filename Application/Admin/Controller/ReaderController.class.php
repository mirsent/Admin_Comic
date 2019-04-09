<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ReaderController extends AdminBaseController{

    /******************************************* 读者信息 *******************************************/

    public function reader_list()
    {
        $admin = session(C('USER_AUTH_KEY'));
        $this->assign(compact('admin'));
        $this->display();
    }

    /**
     * 获取读者信息
     */
    public function get_reader_info()
    {
        $ms = D('Reader');

        // 代理登录
        $admin = session(C('USER_AUTH_KEY'));
        if ($admin['pid']) {
            $cond['proxy_openid'] = $admin['openid'];
        }

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
        $orderColumn = $orderObj['column'];
        $orderDir = $orderObj['dir'];
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
     * 1.更新reader状态
     * 2.添加系统用户
     * 3.添加代理组权限
     */
    public function set_proxy()
    {
        $reader = M('reader');
        $cond['id'] = I('reader_id');
        $readerInfo = $reader->where($cond)->find();

        if ($readerInfo['is_proxy'] == 1) {
            ajax_return(1);
        } else {
            // 1.更新reader状态
            $data['is_proxy'] = 1;
            $res = $reader->where($cond)->save($data);

            // 2.添加系统用户
            $openid = I('openid');
            $admin = session(C('USER_AUTH_KEY'));
            $userId = $admin['id'];

            $user = D('User');
            $user->create();
            $user->pid = $userId;
            $user->user_name = substr($openid, '-5');
            $user->user_psw = md5(C('USER_DEFAULT_PSW'));
            $res_user = $user->add();

            // 3.添加代理组权限
            $data_access = [
                'uid'      => $res_user,
                'group_id' => C('GROUP_PROXY')
            ];
            M('auth_group_access')->add($data_access);

            if ($res === false) {
                ajax_return(0, '设置代理失败');
            }
            ajax_return(1);
        }
    }




    /******************************************* 积分 *******************************************/

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




    /******************************************* 分享 *******************************************/

    public function get_share_info()
    {
        $ms = D('ShareHelp');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['share_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getShareNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('title '.$orderDir.', chapter'); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('share_time '.$orderDir); break;
                case 3: $ms->order('times '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('share_time');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getShareData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }




    /******************************************* 愿望墙 *******************************************/

    public function get_wish_info()
    {
        $ms = D('Wish');

        $cond['w.status'] = array('neq', C('STATUS_N'));

        $recordsTotal = $ms->alias('w')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['wish_title|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['wish_title'] = I('wish_title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['wish_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getWishNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('wish_title '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('wish_time '.$orderDir); break;
                case 3: $ms->order('vote '.$orderDir); break;
                case 3: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('status, wish_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getWishData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 上架漫画
     * @param id 期望ID
     * @param wish_title 期望漫画
     * @param reader_id 读者ID
     */
    public function update_wish()
    {
        // 修改wish状态为已上架
        $cond['id'] = I('id');
        $data['status'] = C('APPLY_P');
        M('wish')->where($cond)->save($data);

        // 通知用户
        $data_notice = [
            'reader_id'   => I('reader_id'),
            'content'     => '小主想看的 '.I('wish_title').' 已上架！',
            'notice_time' => date('Y-m-d H:i:s'),
            'status'      => C('STATUS_Y')
        ];
        M('notice')->add($data_notice);

        ajax_return(1);
    }




    /******************************************* 读者信息 *******************************************/

    public function get_sign_info()
    {
        $ms = D('Sign');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['nickname'] = array('like', '%'.$search.'%');
        }
        $cond['nickname'] = I('nickname');
        $searchDate = I('create_at');
        if ($searchDate) {
            $cond['create_at'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getSignNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('nickname '.$orderDir); break;
                case 1: $ms->order('days '.$orderDir); break;
                case 2: $ms->order('create_at '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('create_at');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getSignData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }




    /******************************************* 反馈 *******************************************/

    public function feedback()
    {
        $cond['status'] = C('STATUS_Y');
        $type = M('feedback_type')->where($cond)->select();
        $this->assign('type', $type);
        $this->display();
    }

    public function get_feedback_info()
    {
        $ms = D('Feedback');

        $cond['f.status'] = array('neq', C('STATUS_N'));

        $recordsTotal = $ms->alias('f')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['feedback_type_name|feedback_content|nickname'] = array('like', '%'.$search.'%');
        }
        $cond['feedback_type_id'] = I('feedback_type_id');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['feedback_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getFeedbackNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('feedback_type_name '.$orderDir); break;
                case 2: $ms->order('feedback_content '.$orderDir); break;
                case 3: $ms->order('nickname '.$orderDir); break;
                case 4: $ms->order('feedback_time '.$orderDir); break;
                case 5: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('feedback_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getFeedbackData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 回复反馈
     */
    public function reply_feedback()
    {
        $cond['id'] = I('feedback_id');
        $data = [
            'status' => C('APPLY_P'),
            'reply'  => I('reply')
        ];
        $res = M('feedback')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '回复失败');
        }
        ajax_return(1);
    }
}