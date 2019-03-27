<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 系统配置controller
 */
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


    /*********************** 类型管理通用方法 *************************/

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


    /************************ 菜单管理 ***************************/

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
}
