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


    /**
     * 获取banner信息
     */
    public function get_banner_info(){
        $cond['status'] = C('STATUS_Y');
        $infos = M('comic_banner')->where($cond)->select();
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



    /**
     * 漫画列表页面
     */
    public function list(){
        $cond['status'] = C('STATUS_Y');
        $release_type = M('release_type')->where($cond)->select();
        $comic_type = M('comic_type')->where($cond)->select();
        $assign = compact('release_type','comic_type');
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取上架漫画信息
     */
    public function get_comic_info(){
        $ms = D('Comics');
        $cond['c.status'] = C('C_STATUS_U');

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
            $ms->order('sort desc', 'created_at');
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
            $ms->order('sort desc', 'created_at');
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
     * 获取评论信息
     */
    public function get_comment_info(){
        $ms = D('Comment');
        $cond['c.status'] = array('neq', C('STATUS_N'));

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

        $recordsFiltered = $ms->getCommentNumber($cond);

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
            $ms->order('sort desc', 'created_at');
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
}
