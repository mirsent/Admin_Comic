<?php
namespace Common\Model;
use Common\Model\BaseModel;
class FeedbackModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('feedback_time','get_datetime',1,'callback')
    );

    public function getFeedbackNumber($cond=[])
    {
        $data = $this
            ->alias('f')
            ->join('__READER__ r ON r.id = f.reader_id')
            ->join('__FEEDBACK_TYPE__ ft ON ft.id = f.feedback_type_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getFeedbackData($cond=[])
    {
        $data = $this
            ->alias('f')
            ->join('__READER__ r ON r.id = f.reader_id')
            ->join('__FEEDBACK_TYPE__ ft ON ft.id = f.feedback_type_id')
            ->field('f.*,nickname,feedback_type_name')
            ->where(array_filter($cond))
            ->select();
        foreach ($data as $key => $value) {
            $data[$key]['url_arr'] = explode(',',$value['url']);
        }
        return $data;
    }

}