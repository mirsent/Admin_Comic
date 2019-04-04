<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ShareHelpModel extends BaseModel{

    public function getShareNumber($cond=[])
    {
        $data = $this
            ->alias('sh')
            ->join('__COMICS__ c ON c.id = sh.comic_id')
            ->join('__READER__ r ON r.openid = sh.openid')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getShareData($cond=[])
    {
        $data = $this
            ->alias('sh')
            ->join('__COMICS__ c ON c.id = sh.comic_id')
            ->join('__READER__ r ON r.openid = sh.openid')
            ->field('sh.*,title,nickname')
            ->where(array_filter($cond))
            ->select();
        return $data;
    }
}