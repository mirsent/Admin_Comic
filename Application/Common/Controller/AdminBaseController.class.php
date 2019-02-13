<?php
namespace Common\Controller;
use Common\Controller\BaseController;
/**
 * admin 基类控制器
 */
class AdminBaseController extends BaseController{
    /**
    * 初始化方法
    */
    public function _initialize(){
        parent::_initialize();

        if (session(C('USER_AUTH_KEY'))==null) $this->redirect(C('USER_AUTH_GATEWAY'));

        $auth = new \Think\Auth();
        $rule_name = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $authRule = D('AuthRule')->getAuthRuleList();
        $isVerify = in_array($rule_name,$authRule);
        if ($isVerify) {
            $result = $auth->check($rule_name,$_SESSION[C('USER_AUTH_KEY')]['id']);
            if(!$result) $this->error('您没有权限访问');
        }

        $cond_gather['status'] = C('APPLY_I');
        $gatherCount = M('gather')->where($cond_gather)->count();

        // 分配菜单数据
        $nav_data=D('AdminNav')->getTreeData('level','order_num,id');
        $assign=array(
            'nav_data' => $nav_data,
            'gather_count' => $gatherCount
        );
        $this->assign($assign);
    }
}

