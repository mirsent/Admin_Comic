<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ReaderModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getReaderNumber($cond){
        $data = $this
            ->alias('r')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getReaderData($cond){
        $data = $this
            ->alias('r')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }

}
