<?php
namespace Common\Model;
use Common\Model\BaseModel;
class FeedbackModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('feedback_time','get_datetime',1,'callback')
    );

}