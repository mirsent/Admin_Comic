<?php
namespace Common\Model;
use Common\Model\BaseModel;
class AuthGroupModel extends BaseModel{

	protected $_auto=array(
	    array('status','get_default_status',1,'callback')
	);

	/**
	 * 获取权限分组列表
	 */
	public function getAuthGroupList(){
		$data = $this
			->where(['status'=>C('STATUS_Y')])
			->select();
		return $data;
	}

	public function getGroupsByIds($groupIds){
		$map = [
			'status' => C('STATUS_Y'),
			'id' => array('in', strval($groupIds))
		];
		$data = $this
			->where($map)
			->getField('title',true);
		return implode('、',$data);
	}

	/**
	 * 传递主键id删除数据
	 * @param  array   $map  主键id
	 * @return boolean       操作是否成功
	 */
	public function deleteData($map){
		$this->where($map)->delete();
		$group_map=array(
			'group_id'=>$map['id']
			);
		// 删除关联表中的组数据
		$result=D('AuthGroupAccess')->deleteData($group_map);
		return $result;
	}
}
