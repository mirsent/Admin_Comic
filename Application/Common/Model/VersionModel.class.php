<?php
namespace Common\Model;
use Common\Model\BaseModel;
class VersionModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('create_at','get_datetime',1,'callback')
    );

}
