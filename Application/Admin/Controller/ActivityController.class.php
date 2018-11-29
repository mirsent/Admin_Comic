<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ActivityController extends AdminBaseController {

    /**
     * 获取充值活动信息
     */
    public function get_recharge_activity_info()
    {
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = M('recharge_activity')
            ->where($cond)
            ->select();
        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 编辑充值活动信息
     */
    public function input_recharge_activity()
    {
        $activity = D('RechargeActivity');
        $activity->create();
        $id = I('id');

        if ($id) {
            $cond['id'] = $id;
            $res = $activity->where($cond)->save();
        } else {
            $res = $activity->add();
        }

        if ($res === false) {
            ajax_return(0, '编辑充值活动信息失败');
        }
        ajax_return(1);
    }

    /**
     * 删除充值活动信息
     */
    public function delete_recharge_activity()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('recharge_activity')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除充值活动信息失败');
        }
        ajax_return(1);
    }
}