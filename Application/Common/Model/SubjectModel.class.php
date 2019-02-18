<?php
namespace Common\Model;
use Common\Model\BaseModel;
class SubjectModel extends BaseModel{
    protected $_auto=array(
        array('status','get_default_status',1,'callback')
    );

    public function getSubject()
    {
        $cond['status'] = C('STATUS_Y');
        $data = $this->where($cond)->select();
        $comic = M('comics');
        foreach ($data as $key => $value) {
            $cond_comic = [
                'status' => C('STATUS_Y'),
                'id'     => array('in', $value['comic_ids'])
            ];
            $arr = $comic->limit(8)->where($cond_comic)->getField('head', true);
            $count = count($arr); // 包含漫画数
            switch ($count) {
                case 2:
                    $top = [$arr[0], $arr[1], $arr[0], $arr[1] ];
                    $bottom = [$arr[1], $arr[0], $arr[1], $arr[0] ];
                    break;
                case 3:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[0] ];
                    $bottom = [$arr[1], $arr[2], $arr[0], $arr[1] ];
                    break;
                case 4:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[3] ];
                    $bottom = [$arr[1], $arr[2], $arr[3], $arr[0] ];
                    break;
                case 5:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[3] ];
                    $bottom = [$arr[4], $arr[0], $arr[1], $arr[2] ];
                    break;
                case 6:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[3] ];
                    $bottom = [$arr[4], $arr[5], $arr[0], $arr[1] ];
                    break;
                case 7:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[3] ];
                    $bottom = [$arr[4], $arr[5], $arr[6], $arr[0] ];
                    break;
                case 8:
                    $top = [$arr[0], $arr[1], $arr[2], $arr[3] ];
                    $bottom = [$arr[4], $arr[5], $arr[6], $arr[7] ];
                    break;

                default:
                    break;
            }
            $data[$key]['top'] = array_merge($top,$top);
            $data[$key]['bottom'] = array_merge($bottom,$bottom);
        }
        return $data;
    }
}