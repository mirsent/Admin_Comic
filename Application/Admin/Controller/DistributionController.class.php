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

    /************************** 代理列表 ************************** */

    public function distribution_list()
    {
        // 获取代理的读者
        $cond = [
            'proxy_openid' => array('exp', 'is not null'),
            'status'       => C('STATUS_Y')
        ];
        $readers = M('reader')->where($cond)->getField('openid',true);

        $today = date('Y-m-d');
        $month = date('Y-m');

        $recharge = D('RechargeOrder');
        $cond_recharge_d = [
            'recharge_date' => $today,
            'openid'        => array('in',$readers)
        ];
        $cond_recharge_m = [
            'recharge_month' => $month,
            'openid'         => array('in',$readers)
        ];
        $cond_recharge_a['openid'] = array('in',$readers);

        $rechargeD = $recharge->caclRechargeMoney($cond_recharge_d);
        $rechargeM = $recharge->caclRechargeMoney($cond_recharge_m);
        $rechargeA = $recharge->caclRechargeMoney($cond_recharge_a);

        $this->assign(compact('rechargeD','rechargeM','rechargeA'));

        $this->display();
    }

    public function get_proxy_info()
    {
        $recharge = D('RechargeOrder');
        $today = date('Y-m-d');
        $month = date('Y-m');
        $cond_recharge_d['recharge_date'] = $today;
        $cond_recharge_m['recharge_month'] = $month;

        $cond = [
            'status'   => C('STATUS_Y'),
            'is_proxy' => 1
        ];
        $data = M('reader')->where($cond)->field('id,openid,nickname')->select();

        foreach ($data as $key => $value) {
            $cond_recharge_d['proxy_openid'] = $cond_recharge_m['proxy_openid'] = $cond_recharge_a['proxy_openid'] = $value['openid'];
            $data[$key]['today'] = $recharge->caclProxyRechargeMoney($cond_recharge_d);
            $data[$key]['month'] = $recharge->caclProxyRechargeMoney($cond_recharge_m);
            $data[$key]['all'] = $recharge->caclProxyRechargeMoney($cond_recharge_a);
        }
        echo json_encode([
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);
    }
}