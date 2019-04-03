<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class ComicController extends AdminBaseController{

    /**
     * 发布类型页面
     */
    public function t_release(){
        $assign = [
            'table' => 'ReleaseType',
            'name'  => 'release_type_name',
            'title' => '发布类型'
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 漫画类型页面
     */
    public function t_comic(){
        $assign = [
            'table' => 'ComicType',
            'name'  => 'comic_type_name',
            'title' => '漫画类型'
        ];
        $this->assign($assign);
        $this->display();
    }




    /******************************************* banner *******************************************/

    /**
     * 漫画广告banner页面
     */
    public function banner(){
        $cond['status'] = C('C_STATUS_U');
        $comic = M('comics')
            ->field('id,title')
            ->where($cond)
            ->select();
        $assign = [
            'comic' => $comic
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取banner信息
     */
    public function get_banner_info(){
        $cond['cb.status'] = C('STATUS_Y');
        $infos = M('comic_banner')
            ->alias('cb')
            ->join('__COMICS__ c ON c.id = cb.jump_comic_id')
            ->field('cb.*,title as comic_title')
            ->where($cond)
            ->select();
        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改banner
     */
    public function input_banner(){
        $banner = D('ComicBanner');
        $banner->create();
        $id = I('id');
        if ($id) {
            $cond['id'] = $id;
            $res = $banner->where($cond)->save();
        } else {
            $res = $banner->add();
        }

        if ($res === false) {
            ajax_return(0, '修改banner失败');
        }
        ajax_return(1);
    }

    /**
     * 删除banner
     */
    public function delete_banner(){
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('comic_banner')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除banner失败');
        }
        ajax_return(1);
    }




    /******************************************* 漫画 *******************************************/

    /**
     * 漫画列表页面
     */
    public function comic_list(){
        $cond['status'] = C('STATUS_Y');
        $release_type = M('release_type')->where($cond)->select();
        $comic_type = M('comic_type')->where($cond)->select();
        $comic = M('comics')->where($cond)->field('id,title')->select();
        $assign = compact('release_type','comic_type','comic');
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取上架漫画信息
     */
    public function get_comic_info(){
        $ms = D('Comics');

        $recordsTotal = $ms->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|author|release_type_name'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['author'] = I('author');
        $cond['release_type_id'] = I('release_type_id');
        $comicTypeId = I('comic_type_id');
        if ($comicTypeId) {
            $cond['_string'] = 'FIND_IN_SET('.$comicTypeId.', type_ids)';
        }
        $cond['s_serial'] = I('s_serial');
        $cond['s_fee'] = I('s_fee');
        $cond['s_target'] = I('s_target');
        $cond['s_space'] = I('s_space');
        $cond['c.status'] = I('status');

        $recordsFiltered = $ms->getComicNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('c.id '.$orderDir); break;
                case 3: $ms->order('title '.$orderDir); break;
                case 4: $ms->order('author '.$orderDir); break;
                case 5: $ms->order('release_type_name '.$orderDir); break;
                case 6: $ms->order('type_ids '.$orderDir); break;
                case 7: $ms->order('total_chapter '.$orderDir); break;
                case 8: $ms->order('free_chapter '.$orderDir); break;
                case 9: $ms->order('pre_chapter_pay '.$orderDir); break;
                case 10: $ms->order('pre_chapter_share '.$orderDir); break;
                case 11: $ms->order('max_share_chapter '.$orderDir); break;
                case 12: $ms->order('spread_times '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('updated_at desc,sort desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getComicData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 更新漫画
     */
    public function refresh_comic()
    {
        $comics = I('comic');
        $cond['id'] = array('in', $comics);
        $data['updated_at'] = date('Y-m-d H:i:s');
        M('comics')->where($cond)->save($data);
        ajax_return(1);
    }

    /**
     * 推荐漫画
     */
    public function recommend_comics()
    {
        $comics = explode(',', I('comic'));
        foreach ($comics as $key => $value) {
            $data[] = [
                'comic_id'       => $value,
                'recommend_time' => date('Y-m-d H:i:s'),
                'status'         => C('STATUS_Y')
            ];
        }
        M('recommend')->addAll($data);
        ajax_return(1);
    }

    public function recommend_comic()
    {
        $comicId = I('comic_id');
        $date = date('Y-m-d H:i:s');

        $cond['comic_id'] = $comicId;
        $recommendInfo = M('recommend') ->where($cond)->find();

        if ($recommendInfo) {
            M('recommend')->where($cond)->save(['recommend_time'=>$date]);
        } else {
            $data = [
                'comic_id'       => $comicId,
                'recommend_time' => $date,
                'status'         => C('STATUS_Y')
            ];
            M('recommend')->add($data);
        }
        ajax_return(1);
    }




    /******************************************* 漫画仓库 *******************************************/

    /**
     * 漫画仓库页面
     */
    public function warehouse(){
        $cond['status'] = C('STATUS_Y');
        $release_type = M('release_type')->where($cond)->select();
        $comic_type = M('comic_type')->where($cond)->select();
        $assign = compact('release_type','comic_type');
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取下架漫画信息
     */
    public function get_comic_down_info(){
        $ms = D('Comics');
        $cond['c.status'] = C('C_STATUS_D');

        $recordsTotal = $ms->alias('c')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['name|author|release_type_name'] = array('like', '%'.$search.'%');
        }
        $cond['name'] = I('name');
        $cond['author'] = I('author');
        $cond['release_type_id'] = I('release_type_id');
        $comicTypeId = I('comic_type_id');
        if ($comicTypeId) {
            $cond['_string'] = 'FIND_IN_SET('.$comicTypeId.', type_ids)';
        }
        $cond['s_serial'] = I('s_serial');
        $cond['s_fee'] = I('s_fee');
        $cond['s_target'] = I('s_target');
        $cond['s_space'] = I('s_space');

        $recordsFiltered = $ms->getComicNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 1: $ms->order('name '.$orderDir); break;
                case 2: $ms->order('author '.$orderDir); break;
                case 3: $ms->order('release_type_name '.$orderDir); break;
                case 4: $ms->order('type_ids '.$orderDir); break;
                case 5: $ms->order('total_chapter '.$orderDir); break;
                case 6: $ms->order('free_chapter '.$orderDir); break;
                case 7: $ms->order('pre_chapter_pay '.$orderDir); break;
                case 8: $ms->order('spread_times '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('sort desc,created_at');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getComicData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 编辑漫画
     */
    public function input_comic(){
        $comic = D('Comics');
        $data = I('post.');
        $id = I('id');
        if ($id) {
            $cond['id'] = $id;
            $res = $comic->editComic($cond, $data);
        } else {
            $res = $comic->addComic($data);
        }

        if ($res === false) {
            ajax_return(0, '编辑漫画失败');
        }
        ajax_return(1);
    }

    /**
     * 漫画分享图库页面
     */
    public function share_imgs(){
        $cond = [
            'status'   => C('STATUS_Y'),
            'comic_id' => I('comic_id')
        ];
        $covers = M('comic_cover')->where($cond)->select();

        $assign = [
            'covers' => $covers
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 上传分享图片
     */
    public function upload_cover(){
        $imgs = upload_multiple('image');
        $comicId = I('comic_id');
        for ($i=0; $i < count($imgs); $i++) {
            $data[] = [
                'comic_id'  => $comicId,
                'cover_url' => $imgs[$i],
                'status'    => C('STATUS_Y')
            ];
        }
        $res = M('comic_cover')->addAll($data);

        if ($res === false) {
            ajax_return(0, '上传分享图失败');
        }
        ajax_return(1);
    }

    /**
     * 删除分享图片
     */
    public function delete_cover(){
        $cond['id'] = I('key');
        $data['status'] = C('STATUS_N');
        $res = M('comic_cover')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除分享图片失败');
        }
        ajax_return(1);
    }




    /******************************************* 推荐 *******************************************/

    /**
     * 获取推荐漫画
     */
    public function get_recommend_info(){
        $ms = D('Recommend');
        $cond['r.status'] = C('STATUS_Y');
        $infos = $ms->getRecommendData($cond);
        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 排序
     */
    public function order_recommend()
    {
        $cond['id'] = I('id');
        $data['sort'] = I('sort');
        $res = M('recommend')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '排序失败');
        }
        ajax_return(1);
    }

    /**
     * 取消推荐
     */
    public function cancel_recommend()
    {
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('recommend')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '取消推荐失败');
        }
        ajax_return(1);
    }




    /******************************************* 专题 *******************************************/

    public function subject(){
        $cond['status'] = C('STATUS_Y');
        $comic = M('comics')->where($cond)->field('id, title')->select();

        $assign = [
            'comic' => $comic
        ];
        $this->assign($assign);
        $this->display();
    }

    public function get_subject_info(){
        $subject = D('Subject');
        $cond['status'] = array('neq', C('STATUS_N'));
        $infos = $subject->where($cond)->select();
        $comic = D('Comics');
        foreach ($infos as $key => $value) {
            $infos[$key]['comics_arr'] =$comic->getComicsIdsArr($value['comic_ids']);
            $infos[$key]['comics'] = $comic->getComicsByIds($value['comic_ids']);
        }

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 编辑专题
     */
    public function input_subject(){
        $subject = D('Subject');
        $subject->create();
        $subject->comic_ids = implode(',', I('comic_ids'));
        $id = I('id');

        if ($id) {
            $cond['id'] = $id;
            $res = $subject->where($cond)->save();
        } else {
            $res = $subject->add();
        }

        if ($res === false) {
            ajax_return(0, '编辑专题失败');
        }
        ajax_return(1);
    }

    /**
     * 删除专题
     */
    public function delete_subject(){
        $cond['id'] = I('id');
        $data['status'] = C('STATUS_N');
        $res = M('subject')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除专题失败');
        }
        ajax_return(1);
    }




    /******************************************* 评论 *******************************************/

    /**
     * 获取评论信息
     */
    public function get_comment_info(){
        $ms = D('ComicComment');
        $cond['c.status'] = array('neq', C('STATUS_N'));

        $recordsTotal = $ms->alias('c')->where($cond)->count();

        // 搜索
        $search = I('search');
        if (strlen($search)>0) {
            $cond['title|nickname|comment_content'] = array('like', '%'.$search.'%');
        }
        $cond['title'] = I('title');
        $cond['nickname'] = I('nickname');
        $searchDate = I('search_date');
        if ($searchDate) {
            $cond['comment_time'] = array('BETWEEN', [$searchDate.' 00:00:00', $searchDate.' 23:59:59']);
        }

        $recordsFiltered = $ms->getCommentNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('title '.$orderDir); break;
                case 1: $ms->order('nickname '.$orderDir); break;
                case 2: $ms->order('comment_content '.$orderDir); break;
                case 3: $ms->order('comment_time '.$orderDir); break;
                case 4: $ms->order('status '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('comment_time desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getCommentData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 设置评论显示状态
     */
    public function set_comment(){
        $cond['id'] = I('id');
        $data['status'] = I('status');
        $res = M('comic_comment')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '设置评论显示状态失败');
        }
        ajax_return(1);
    }

}
