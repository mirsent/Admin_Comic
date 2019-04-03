<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class NovelController extends AdminBaseController{

    /**
     * 小说列表页面
     */
    public function novel_list(){
        $cond['status'] = C('STATUS_Y');
        $type = M('release_type')->where($cond)->select();
        $tag = M('comic_type')->where($cond)->select();
        $novel = M('novel')->where($cond)->field('id,title')->select();
        $assign = compact('type','tag','novel');
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取小说信息
     */
    public function get_novel_info(){
        $ms = D('Novel');

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

        $recordsFiltered = $ms->getNovelNumber($cond);

        // 排序
        $orderObj = I('order')[0];
        $orderColumn = $orderObj['column']; // 排序列，从0开始
        $orderDir = $orderObj['dir'];       // ase desc
        if(isset(I('order')[0])){
            $i = intval($orderColumn);
            switch($i){
                case 0: $ms->order('c.id '.$orderDir); break;
                case 2: $ms->order('title '.$orderDir); break;
                case 3: $ms->order('author '.$orderDir); break;
                case 4: $ms->order('release_type_name '.$orderDir); break;
                case 5: $ms->order('type_ids '.$orderDir); break;
                case 6: $ms->order('total_chapter '.$orderDir); break;
                case 7: $ms->order('free_chapter '.$orderDir); break;
                case 8: $ms->order('pre_chapter_pay '.$orderDir); break;
                case 9: $ms->order('updated_at '.$orderDir); break;
                default: break;
            }
        } else {
            $ms->order('updated_at desc,sort desc');
        }

        // 分页
        $start = I('start');  // 开始的记录序号
        $limit = I('limit');  // 每页显示条数
        $page = I('page');    // 第几页

        $infos = $ms->page($page, $limit)->getNovelData($cond);

        echo json_encode(array(
            "draw" => intval(I('draw')),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 新增、修改小说
     */
    public function input_novel()
    {
        $id = I('id');
        $novel = D('Novel');
        $novel->create();
        $novel->type_ids = implode(',', I('type_ids'));
        if (I('s_fee') == C('C_FEE_N')) {
            // 免费小说的免费章节为总章节数
            $novel->free_chapter = I('total_chapter');
        }

        if ($id) {
            $cond['id'] = $id;
            $res = $novel->where($cond)->save();
        } else {
            $res = $novel->add();
        }

        if ($res === false) {
            ajax_return(0, '修改小说失败');
        }
        ajax_return(1, '修改小说成功');
    }

    /**
     * 更新小说时间
     */
    public function refresh_novel()
    {
        $novel = I('novel');
        $cond['id'] = array('in', $novel);
        $data['updated_at'] = date('Y-m-d H:i:s');
        M('novel')->where($cond)->save($data);
        ajax_return(1);
    }

    /**
     * 推荐小说
     */
    public function recommend_novel()
    {
        $cond['novel_id'] = I('novel_id');
        $recommend = D('NovelRecommend');
        $res = $recommend->where($cond)->find();
        if ($res) {
            $data = [
                'status'         => C('STATUS_Y'),
                'recommend_time' => date('Y-m-d H:i:s')
            ];
            $recommend->where($cond)->save($data);
        } else {
            $recommend->create();
            $recommend->add();
        }
        ajax_return(1);
    }

    /**
     * 小说章节信息
     */
    public function get_chapter_info()
    {
        $cond = [
            'c.status' => C('STATUS_Y'),
            'novel_id' => I('novel_id')
        ];
        $infos = D('NovelChapter')->getChapterData($cond);

        echo json_encode([
            "data" => $infos
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 编辑小说章节
     */
    public function input_chapter()
    {
        $chapterId = I('id');
        $chapter = D('NovelChapter');
        $chapter->create();
        $chapter->words = mb_strlen(strip_tags(htmlspecialchars_decode(I('content'))), 'UTF8'); // 字数
        if ($chapterId) {
            $cond['id'] = $cond_detail['chapter_id'] = $chapterId;
            $res = $chapter->where($cond)->save(); // 修改章节
            M('novel_chapter_detail')->where($cond_detail)->save(['content'=>I('content')]); // 修改章节详情
        } else {
            $res = $chapter->add();
            $data_detail = [
                'chapter_id' => $res,
                'content'    => I('content')
            ];
            M('novel_chapter_detail')->add($data_detail);
        }

        if (false == $res) {
            ajax_return(0, '编辑小说章节失败');
        }
        ajax_return(1, '编辑小说章节成功');
    }

    /*********************************** 小说 banner *************************************/

    /**
     * 小说banner页面
     */
    public function novel_banner(){
        $cond['status'] = C('C_STATUS_U');
        $novel = M('novel')
            ->field('id,title')
            ->where($cond)
            ->select();
        $assign = [
            'novel' => $novel
        ];
        $this->assign($assign);
        $this->display();
    }

    /**
     * 获取banner信息
     */
    public function get_banner_info(){
        $cond['nb.status'] = C('STATUS_Y');
        $infos = M('novel_banner')
            ->alias('nb')
            ->join('__NOVEL__ n ON n.id = nb.jump_novel_id')
            ->field('nb.*,title as novel_title')
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
        $banner = D('NovelBanner');
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
        $res = M('novel_banner')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '删除banner失败');
        }
        ajax_return(1);
    }




    /*********************************** 小说 推荐 *************************************/

    /**
     * 获取小说推荐
     */
    public function get_recommend_info(){
        $ms = D('NovelRecommend');
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
        $res = M('novel_recommend')->where($cond)->save($data);

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
        $res = M('novel_recommend')->where($cond)->save($data);

        if ($res === false) {
            ajax_return(0, '取消推荐失败');
        }
        ajax_return(1);
    }
}