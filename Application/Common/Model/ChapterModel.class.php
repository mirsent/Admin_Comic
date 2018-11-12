<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ChapterModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getChapterData($cond){
        $data = $this
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['catalog_name'] = '第'.$value['catalog'].'章';
        }
        return $data;
    }

}
