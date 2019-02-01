<?php
namespace Common\Model;
use Common\Model\BaseModel;
class AdminNavModel extends BaseModel{

	protected $_auto=array(
	    array('status','get_default_status',1,'callback')
	);

	/**
	 * 获取全部菜单
	 * @param  string $type tree获取树形结构 level获取层级结构
	 * @return array       	结构数据
	 */
	public function getTreeData($type='tree',$order=''){
		// 判断是否需要排序
		if(empty($order)){
		    $data=$this->select();
		}else{
		    $data=$this->order('order_num is null,'.$order)->select();
		}
		// 获取树形或者结构数据
		if($type=='tree'){
		    $data=\Org\Nx\Data::tree($data,'name','id','pid');
		}elseif($type="level"){
		    $data=\Org\Nx\Data::channelLevel($data,0,'&nbsp;','id');
		    // 显示有权限的菜单
		    $auth=new \Think\Auth();
		    foreach ($data as $k => $v) {
		        if ($auth->check($v['nav_mca'],$_SESSION[C('USER_AUTH_KEY')]['id'])) {
		            foreach ($v['_data'] as $m => $n) {
		                if(!$auth->check($n['nav_mca'],$_SESSION[C('USER_AUTH_KEY')]['id'])){
		                    unset($data[$k]['_data'][$m]);
		                }
		            }
		        }else{
		            // 删除无权限的菜单
		            unset($data[$k]);
		        }
		    }
		}
		// p($data);die;
		return $data;

	}
}
