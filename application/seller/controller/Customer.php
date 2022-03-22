<?php

namespace app\seller\controller;

use app\seller\model\ServiceLog;

class Customer extends Base
{
    public function index()
    {
        $keFuModel = new \app\seller\model\KeFu();
        $keFu = $keFuModel->getSellerKeFu();

        $keFuArr = [];
        foreach ($keFu['data'] as $vo) {
            $keFuArr[$vo['kefu_code']] = $vo['kefu_name'];
        }

        if(request()->isAjax()) {

            $limit = input('param.limit');
            $kefuCode = input('param.kefu_code');
            $startTime = input('param.start_time');
            $where = [];
            
            if (!empty($kefuCode)) {
                $where[] = ['kefu_code', '=', $kefuCode];
            }

            if (!empty($startTime)) {
                $where[] = ['start_time', 'between', [$startTime, $startTime . ' 23:59:59']];
            }

            $log = new ServiceLog();
            $list = $log->getServiceList($where, $limit, $keFuArr);

            if (0 != $list['code']) {
                return json(['code' => -1, 'msg' => 'ok', 'count' => 0, 'data' => []]);
            }

            return json(['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()]);
        }

        $this->assign([
            'kefu' => $keFuArr
        ]);

        return $this->fetch();
    }
}