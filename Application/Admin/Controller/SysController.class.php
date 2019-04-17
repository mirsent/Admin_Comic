<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class SysController extends AdminBaseController{

    /**
     * 类型页面
     */
    public function type(){
        $assign = [
            'table' => 'ReleaseType',
            'name'  => 'release_type_name',
            'title' => '类型'
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 标签页面
     */
    public function tag(){
        $assign = [
            'table' => 'ComicType',
            'name'  => 'comic_type_name',
            'title' => '标签'
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 签到积分
     */
    public function cog_sign()
    {
        $assign = [
            'table' => 'CogSign',
            'name'  => 'integral',
            'title' => '签到积分'
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 反馈类型
     */
    public function feedback()
    {
        $assign = [
            'table' => 'FeedbackType',
            'name'  => 'feedback_type_name',
            'title' => '反馈类型'
        ];
        $this->assign($assign);
        $this->display();
    }




    /******************************************* 通用 *******************************************/

    /**
     * 获取dt数据
     */
    public function get_dt_info(){
        $ms = D(I('table'));
        $infos = $ms->getDataForDt();
        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 新增/编辑
     */
    public function input(){
        $ms = D(I('table'));
        $ms->create();
        $id = I('id');
        if ($id) {
            $map['id'] = $id;
            $res = $ms->where($map)->save();
        } else {
            $res = $ms->add();
        }

        if ($res === false) {
            ajax_return(0, '新增/编辑 出错');
        }
        ajax_return(1);
    }

    /**
     * 设置状态
     * @param id
     * @param table 表名
     * @param status 修改状态
     */
    public function set_status(){
        $ms = D(I('table'));
        $ms->create();
        $map['id'] = I('id');
        $res = $ms->where($map)->save();

        if ($res === false) {
            ajax_return(0, '设置状态出错');
        }
        ajax_return(1);
    }




    /******************************************* 参数 *******************************************/

    public function cog()
    {
        $cog = M('cog')->find(1);
        $this->assign('cog', $cog);
        $this->display();
    }

    /**
     * 修改参数
     */
    public function input_config()
    {
        $cond['id'] = 1;
        $data[I('name')] = I('value');
        M('cog')->where($cond)->save($data);

        ajax_return(1);
    }




    /******************************************* 菜单管理 *******************************************/

    public function admin_nav(){
        $map = array(
            'status' => array('neq',C('STATUS_N')),
            'pid' => 0
        );
        $navs = M('admin_nav')->where($map)->select();
        $assign = array(
            'table' => 'AdminNav',
            'navs' => $navs
        );
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取菜单列表
     */
    public function get_admin_nav_info(){
        $ms = D('AdminNav');
        $map['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($map)->getTreeData();

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 新增/编辑 菜单
     */
    public function input_nav(){
        $adminNav = D('AdminNav');
        $adminNav->create();
        $id = I('id');

        if ($id) { // 编辑
            $res = $adminNav->where(['id'=>$id])->save();
        } else {
            $res = $adminNav->add();
        }

        if ($res === false) {
            ajax_return(0, '录入菜单出错');
        }
        ajax_return(1);
    }

    /**
     * 菜单排序
     */
    public function order_nav(){
        $data['order_num'] = I('order_num');
        $res = M('admin_nav')->where(['id'=>I('id')])->save($data);
        if ($res === false) {
            ajax_return(0, '排序出错');
        }
        ajax_return(1);
    }




    /******************************************* 公告 *******************************************/

    /**
     * 获取公告列表
     */
    public function get_announce_info(){
        $ms = M('announce');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($cond)->select();
        foreach ($infos as $key => $value) {
            $infos[$key]['announce_content'] = htmlspecialchars_decode($value['announce_content']);
            $infos[$key]['announce_content_text'] = strip_tags(htmlspecialchars_decode($value['announce_content']));
        }

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改公告
     */
    public function input_announce()
    {
        $announce = D('Announce');
        $announce->create();
        $id = I('id');

        if ($id) {
            $cond['id'] = $id;
            $res = $announce->where($cond)->save();
        } else {
            $res = $announce->add();
        }

        if ($res === false) {
            ajax_return(0, '修改公告失败');
        }
        ajax_return(1, '修改公告成功');
    }

    /**
     * 删除公告
     */
    public function delete_announce()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('announce')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除公告失败');
        }
        ajax_return(1, '删除公告成功');
    }




    /******************************************* 帮助问题 *******************************************/

    /**
     * 获取帮助问题列表
     */
    public function get_help_info(){
        $ms = M('help');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($cond)->select();
        foreach ($infos as $key => $value) {
            $infos[$key]['help_content'] = htmlspecialchars_decode($value['help_content']);
            $infos[$key]['help_content_text'] = strip_tags(htmlspecialchars_decode($value['help_content']));
        }

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改帮助问题
     */
    public function input_help()
    {
        $help = D('Help');
        $help->create();
        $id = I('id');

        if ($id) {
            $cond['id'] = $id;
            $res = $help->where($cond)->save();
        } else {
            $res = $help->add();
        }

        if ($res === false) {
            ajax_return(0, '修改帮助问题失败');
        }
        ajax_return(1, '修改帮助问题成功');
    }

    /**
     * 删除帮助问题
     */
    public function delete_help()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('help')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除帮助问题失败');
        }
        ajax_return(1, '删除帮助问题成功');
    }




    /******************************************* 版本 *******************************************/

    public function get_version_info()
    {
        $ms = M('version');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $ms->where($cond)->select();

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改版本
     */
    public function input_version()
    {
        $version = D('Version');
        $version->create();
        $id = I('id');

        if ($id) {
            $cond['id'] = $id;
            $res = $version->where($cond)->save();
        } else {
            $res = $version->add();
        }

        if ($res === false) {
            ajax_return(0, '修改版本失败');
        }
        ajax_return(1, '修改版本成功');
    }

    /**
     * 删除版本
     */
    public function delete_version()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('version')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除版本失败');
        }
        ajax_return(1, '删除版本成功');
    }
}
