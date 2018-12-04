<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
class DistributionController extends AdminBaseController{
    /**
     * 获取分销层级关系
     */
    public function get_distribution_tree()
    {
        $cond = [
            'proxy_openid' => array('exp', 'is null'),
            'status'       => C('STATUS_Y')
        ];
        $users = M('reader')->where($cond)->field('id,openid,proxy_openid,nickname')->select();
        foreach ($users as $key => $value) {
            $cond_child = [
                'proxy_openid' => $value['openid'],
                'status'       => C('STATUS_Y')
            ];

            $childArr = $this->get_distirbution_child($cond_child);

            $arrUser[] = [
                'text'  => '平台',
                'icon'  => 'glyphicon glyphicon-bookmark',
                'nodes' => [
                    [
                        'text'  => $value['nickname'],
                        'icon'  => $childArr ? 'glyphicon glyphicon-bookmark' : '',
                        'nodes' => $childArr,
                        'state' => [
                            expanded => true
                        ]
                    ]
                ]
            ];
        }
        echo(json_encode($arrUser));
    }
    public function get_distirbution_child($cond)
    {
        $users = M('reader')->where($cond)->field('id,openid,proxy_openid,nickname')->select();
        if($users) {
            foreach ($users as $key => $value) {
                $childData[] = array(
                    'text' => $value['nickname'],
                    'icon' => 'glyphicon glyphicon-user'
                );
            }
            return $childData;
        } else {
            return null;
        }
    }
}