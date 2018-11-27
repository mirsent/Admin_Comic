<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ComicsModel extends BaseModel{

    protected $_auto=array(
        array('status','get_default_status',1,'callback'),
        array('created_at','get_datetime',1,'callback'),
        array('updated_at','get_datetime',3,'callback')
    );

    public function getComicNumber($cond){
        $data = $this
            ->alias('c')
            ->join('__RELEASE_TYPE__ rt ON rt.id = c.release_type_id')
            ->where(array_filter($cond))
            ->count();
        return $data;
    }

    public function getComicData($cond){
        $data = $this
            ->alias('c')
            ->join('__RELEASE_TYPE__ rt ON rt.id = c.release_type_id')
            ->field('c.*,release_type_name')
            ->where(array_filter($cond))
            ->select();
        $type = M('comic_type');
        foreach ($data as $key => $value) {
            $cond_type = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['type_ids'])
            ];
            $typeArr = $type->where($cond_type)->getField('comic_type_name', true);
            $data[$key]['type_names'] = implode('；', $typeArr);
            $data[$key]['brief'] = htmlspecialchars_decode($value['brief']);
            $data[$key]['no'] = str_pad($value['id'], 5, 0, STR_PAD_LEFT);
        }
        return $data;
    }

    public function addComic($data){
        if (!$data = $this->create($data)) {
            return false;
        } else {
            $data['type_ids'] = implode(',', $data['type_ids']);
            if ($data['s_fee'] == C('C_FEE_N')) {
                // 免费章节
                $data['free_chapter'] = $data['total_chapter'];
            }
            $res = $this
                ->add($data);
            return $res;
        }
    }

    public function editComic($cond, $data){
        if (!$data = $this->create($data)) {
            return false;
        } else {
            $data['type_ids'] = implode(',', $data['type_ids']);
            $res = $this
                ->where($cond)
                ->save($data);
            return $res;
        }
    }

    /**
     * 获取漫画信息
     * @param int $comicId 漫画ID
     */
    public function getComicInfo($comicId){
        $cond['id'] = $comicId;
        $data = $this
            ->where($cond)
            ->field('id as comic_id,total_chapter,pre_chapter_pay,pre_chapter_share,max_share_chapter')
            ->find();

        return $data;
    }

    /**
     * 获取漫画图片详情
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     * @return arr          漫画图片数组
     */
    public function getComicDetail($comicId, $chapter){

        $cond = [
            'comic_id' => $comicId,
            'catalog'  => $chapter
        ];
        M('chapter')->where($cond)->setInc('popularity',1); // 查看+1

        $path = "Uploads/comic/".$comicId."/".$chapter."/*";
        $folder = glob($path);
        asort($folder,SORT_NATURAL);
        return array_values($folder);
    }

    /**
     * 验证费用
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     * @param  string $openid  字符串
     * @return 1：免费 2：已购买 -1：未购买 -2：只能付费阅读
     * @return need_share 还需要分享数
     */
    public function checkCost($comicId, $chapter, $openid){
        $comicInfo = $this->find($comicId);
        $isFree = $comicInfo['s_fee'];
        $freeChapter = $comicInfo['free_chapter'];
        $maxShareChapter = $comicInfo['max_share_chapter'];
        $preChapterShare = $comicInfo['pre_chapter_share'];

        if ($isFree == C('C_FEE_N') || $chapter <= $freeChapter) {
            // 免费章节
            return ['status'=>1];
        } else {
            // 付费
            $cond_consume = [
                'comic_id' => $comicId,
                'openid'   => $openid,
                'chapter'  => $chapter,
                'status'   => C('STATUS_Y')
            ];
            $consumeInfo = M('consume_order')->where($cond_consume)->find();

            if ($chapter <= $maxShareChapter) { // 可以分享兑换
                // 分享
                $cond_share = [
                    'comic_id' => $comicId,
                    'openid'   => $openid,
                    'chapter'  => $chapter,
                ];
                $shareTimes = M('share_help')->where($cond_share)->getField('times');
                if ($shareTimes >= $preChapterShare) {
                    $isShare = 1;
                }
            } else {
                // todo 只能付费
                $cannotShare = 1;
            }

            if ($consumeInfo || $isShare) {
                // 已购买/已分享
                return ['status'=>2];
            } else {
                // 未购买
                if ($cannotShare) {
                    return ['status'=>'-2'];
                } else {
                    return ['status'=>'-1'];
                }
            }
        }
    }

    /**
     * 获取需要分享数量
     * @param  int $comicId 漫画ID
     * @param  int $chapter 章节
     * @param  string $openid  读者openid
     */
    public function getNeedShare($comicId, $chapter, $openid)
    {
        $comicInfo = $this->find($comicId);
        $preChapterShare = $comicInfo['pre_chapter_share'];

        $cond_share = [
            'comic_id' => $comicId,
            'openid'   => $openid,
            'chapter'  => $chapter,
        ];
        $shareTimes = M('share_help')->where($cond_share)->getField('times');
        $needShare = $preChapterShare - $shareTimes;

        return $needShare;
    }

    /**
     * 点赞
     * @param  int $comicId 漫画ID
     */
    public function likeComic($comicId)
    {
        $cond['id'] = $comicId;
        $res = $this
            ->where($cond)
            ->setInc('heat',1);
        return $res;
    }

    /**
     * 收藏
     * @param  int $comicId 漫画ID
     */
    public function collectComic($comicId)
    {
        $cond['id'] = $comicId;
        $res = $this
            ->where($cond)
            ->setInc('collection',1);
        return $res;
    }

}
