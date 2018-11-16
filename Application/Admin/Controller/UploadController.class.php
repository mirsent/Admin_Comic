<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class UploadController extends AdminBaseController{
    /**
     * 上传图片
     */
    public function upload_img(){
        $url = upload_single('image');
        ajax_return(1, '上传成功', $url);
    }

    public function upload_multiple(){
        $url = upload_multiple('image');
        ajax_return(1, '上传成功', $url);
    }
}