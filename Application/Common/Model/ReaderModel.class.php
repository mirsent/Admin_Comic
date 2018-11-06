<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ReaderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getReaderData($cond){
        $data = $this
            ->alis('r')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}
