<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class TestController extends AdminBaseController{
    public function index(){
        $sl=0;
        $arr = glob("Uploads/comic/1/1/*");
        asort($arr,SORT_NATURAL);
        foreach ($arr as $v)
        {
            echo '<img src="http://127.0.0.1/Comic/'.$v.'" alt="">';
        }
        echo $sl;
    }
}