<?php
namespace Common\Model;
use Common\Model\BaseModel;
class FeedbackTypeModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getDataForDt(){
        $data = $this
            ->where(['status'=>array('neq',C('STATUS_N'))])
            ->select();
        return $data;
    }

}
