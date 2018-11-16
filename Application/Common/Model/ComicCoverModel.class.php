<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ComicCoverModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

}
