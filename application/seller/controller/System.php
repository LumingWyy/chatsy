<?php

namespace app\seller\controller;

use app\seller\model\System as SystemModel;
use app\seller\validate\SystemValidate;

class System extends Base
{
    public function index()
    {
        if(request()->isPost()) {

            $param = input('post.');

            $validate = new SystemValidate();
            if(!$validate->check($param)) {
                return ['code' => -3, 'data' => '', 'msg' => $validate->getError()];
            }

            isset($param['hello_status']) ? $param['hello_status']= 1 : $param['hello_status'] = 0;
            isset($param['relink_status']) ? $param['relink_status']= 1 : $param['relink_status'] = 0;
            isset($param['auto_link']) ? $param['auto_link']= 1 : $param['auto_link'] = 0;

            $sys = new SystemModel();
            $res = $sys->editSystem($param);

            return json($res);
        }

        $system = new SystemModel();
        $this->assign([
            'system' => $system->getSellerConfig()['data']
        ]);

        return $this->fetch();
    }
}