<?php
namespace Common\Model;
use Common\Model\BaseModel;
class UserModel extends BaseModel{

    protected $_auto=array(
        array('user_psw','md5',1,'function'),
        array('user_psw','',2,'ignore'),
        array('status','get_default_status',1,'callback'),
        array('created_at','get_datetime',1,'callback'),
        array('updated_at','get_datetime',3,'callback')
    );

    /**
     * 根据条件获取用户列表
     */
    public function getUserList($cond){
        $data = $this
            ->where($cond)
            ->select();
        return $data;
    }

    /**
     * 获取用户详细信息
     */
    public function getUserInfo($cond){
        $data = $this
            ->alias('u')
            ->join('__AUTH_GROUP_ACCESS__ aga ON aga.uid = u.id','LEFT')
            ->field('u.*,aga.*')
            ->where($cond)
            ->select();
        return $data;
    }

    /**
     * 根据条件获取用户信息
     */
    public function getUserData($cond){
        $data = $this
            ->where($cond)
            ->find();
        return $data;
    }
}
