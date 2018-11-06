<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 权限规则model
 */
class AuthRuleModel extends BaseModel{

	/**
	 * 获取权限规则列表
	 */
	public function getAuthRuleList(){
		$map['status'] = C('STATUS_Y');
		$data = $this
			->where($map)
			->getField('name', true);
		return $data;
	}

	/**
	 * 根据id获取规则
	 * @param string $rules 规则id(1,2)
	 * @return string       规则名称(规则1;规则2)
	 */
	public function getAuthByIds($rules){
		$map = [
			'status' => C('STATUS_Y'),
			'id'     => array('in',$rules)
		];
		$data = $this->where($map)->getField('title',true);
		return implode(';',$data);
	}

}
