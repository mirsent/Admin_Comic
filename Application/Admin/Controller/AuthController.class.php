<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class AuthController extends AdminBaseController {

    /********************************************** 规则管理 ****************************************/

    public function rule(){
        $cond = array(
            'status' => C('STATUS_Y'),
            'pid'    => 0
        );
        $pRules = M('auth_rule')->where($cond)->select();
        $assign = array(
            'table' => 'AuthRule',
            'pRules' => $pRules
        );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取权限规则列表
     */
    public function get_auth_rule_info(){
        $ms = D('AuthRule');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($cond)->getTreeData('tree','','title');

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 新增/编辑 权限规则
     */
    public function input_rule(){
        $authRule = D('AuthRule');
        $authRule->create();
        if ($id = I('id')) {
            $res = $authRule->where(['id'=>$id])->save();
        } else {
            $res = $authRule->add();
        }

        if ($res === false) {
            ajax_return(0, '新增/编辑权限出错');
        }
        ajax_return(1);
    }





    /********************************************** 用户组管理 ****************************************/

    public function group(){
        $authRule = M('auth_rule');
        // 获取一级规则
        $cond = array(
            'status' => C('STATUS_Y'),
            'pid'    => 0
        );
        $rules = $authRule
            ->where($cond)
            ->field('id, title')
            ->order('id')
            ->select();

        foreach ($rules as $key => $value) {
            $cond['pid'] = $value['id'];
            $rules[$key]['_data'] = $authRule
                ->where($cond)
                ->field('id, title')
                ->order('id')
                ->select(); // 二级规则
        }

        $assign = array(
            'table' => 'AuthGroup',
            'rules' => $rules
        );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取权限分组信息
     */
    public function get_auth_group_info(){
        $ms = D('AuthGroup');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($cond)->select();
        foreach ($infos as $key => $value) {
            $infos[$key]['group_id'] = $value['id'];
            $infos[$key]['rules_arr'] = $this->getMultiSelectArr($value['rules']);
            $infos[$key]['rules'] = D('AuthRule')->getAuthByIds($value['rules']);
        }

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 组合权限 （父权限id,权限id）
     */
    public function getMultiSelectArr($rules){
        $arr = [];
        foreach (explode(',', $rules) as $v) {
            $cond = array(
                'status' => array('neq', C('STATUS_N')),
                'id' => $v
            );
            $pid = M('auth_rule')->where($cond)->getField('pid');
            array_push($arr, $pid.','.$v);
        }
        return $arr;
    }

    /**
     * 新增/编辑 用户组
     */
    public function input_group(){
        $authGroup = D('AuthGroup');
        $authGroup->create();
        $authGroup->rules = implode(',', array_unique(explode(',', implode(',', I('rules')))));
        $id = I('id');
        if ($id) {
            $res = $authGroup->where(['id'=>$id])->save();
        } else {
            $res = $authGroup->add();
        }

        if ($res === false) {
            ajax_return(0, '新增/编辑出错');
        }
        ajax_return(1);
    }





    /********************************************** 用户管理 ****************************************/

    public function user(){
        $groups = D('AuthGroup')->getAuthGroupList();
        $this->assign('groups',$groups);
        $this->display();
    }

    public function get_auth_user_info(){
        $cond['u.status'] = array('neq', C('STATUS_N'));
        $infos = D('User')->getUserInfo($cond);
        foreach ($infos as $key => $value) {
            $infos[$key]['groups_arr'] = explode(',', $value['group_id']);
            $infos[$key]['groups_name'] = D('AuthGroup')->getGroupsByIds($value['group_id']);
        }

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改用户信息
     */
    public function edit_user(){
        $cond['id'] = I('id');
        $user = D('User');
        $user->create();
        $res = $user->where($cond)->save();

        if ($res === false) {
            ajax_return(0, '编辑用户信息出错');
        }
        ajax_return(1);
    }

    /**
     * 修改用户权限
     */
    public function edit_access(){
        $groupAccess = M('auth_group_access');
        $cond['uid'] = I('id');
        $data['group_id'] = implode(',', I('group_id'));
        $is_exist = $groupAccess->where($cond)->find();
        if ($is_exist) {
            $res = $groupAccess->where($cond)->save($data);
        } else {
            $data['uid'] = I('id');
            $res = $groupAccess->add($data);
        }

        if ($res === false) {
            ajax_return(0, '修改用户权限出错');
        }
        ajax_return(1);
    }

    /**
     * 删除用户
     */
    public function delete_user(){
        $tran_result = true;
        $trans = M();
        $trans->startTrans();   // 开启事务

        if ($userId = I('uid')) {
            // 删除用户权限
            $res = M('auth_group_access')->where(['uid'=>$userId])->delete();

            // 修改用户状态
            $cond_user['id'] = $userId;
            $data_user['status'] = C('STATUS_N');
            $userRes = M('user')->where($cond_user)->save($data_user);

            if ($res === false || $userRes === false) {
                $tran_result = false;
            }
        } else {
            $tran_result = false;
        }

        if ($tran_result === false) {
            $trans->rollback();
            ajax_return(0, '删除用户出错');
        } else {
            $trans->commit();
            ajax_return(1);
        }
    }
}
