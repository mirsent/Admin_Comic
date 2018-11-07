<?php
namespace Home\Controller;
use Think\Controller;
class ComicController extends Controller {

    /**
     * 获取漫画banner
     */
    public function get_comic_banner(){
        $cond['status'] = C('STATUS_Y');
        $data = M('comic_banner')
            ->where($cond)
            ->order('sort desc')
            ->select();
        ajax_return(1, '漫画banner列表', $data);
    }

    /**
     * 获取漫画列表
     */
    public function get_comic_list(){
        $comics = D('Comics');
        $cond['c.status'] = C('C_STATUS_U');
        $data = $comics->getComicData($cond);

        ajax_return(1, '漫画列表', $data);
    }

    /**
     * 获取漫画图片
     * @param comic_id 漫画ID
     * @param chapter 章节
     * @param openid 读者身份ID
     */
    public function get_comic_imgs(){
        $comicId = I('comic_id');
        $chapter = I('chapter');
        $openid = I('openid');

        $cond['id'] = $comicId;
        $comicInfo = M('comics')->where($cond)->find();
        $totalChapter = $comicInfo['total_chapter']; // 总章节
        $freeChapter = $comicInfo['free_chapter']; // 免费章节
        $preChapterPay = $comicInfo['pre_chapter_pay']; // 章节费用
        $sFee = $comicInfo['s_fee']; // 收费/免费

        if ($chapter > $totalChapter) {
            ajax_return(2, '超出章节');
        }

        if ($sFee == C('C_FEE_Y') && $chapter > $freeChapter) {
            // 收费章节
            $cond_reader['openid'] = $openid;
            $readerInfo = M('reader')->where($cond_reader)->find();
            $balance = $readerInfo['balance']; // 余额

            // 是否已买
            $cond_consumed = [
                'openid'   => $openid,
                'comic_id' => $comicId,
                'chapter'  => $chapter
            ];
            $isConsumed = M('consume')->where($cond_consumed)->find();

            if (!$isConsumed) {
                if ($preChapterPay > $balance) {
                    ajax_return(3, '余额不足');
                }

                // todo 消费
            }
        }

        $path = "Uploads/comic/".$comicId."/".$chapter."/*";
        $folder = glob($path);
        asort($folder,SORT_NATURAL);
        ajax_return(1, '漫画图片列表', $folder);
    }


}
