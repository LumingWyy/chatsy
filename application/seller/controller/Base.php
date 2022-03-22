<?php

namespace app\seller\controller;

use think\Controller;

class Base extends Controller
{
    public function initialize()
    {
        if(empty(session('seller_user_name'))){

            $this->redirect(url('login/index'));
        }

        $this->assign([
            'seller_name' => session('seller_user_name'),
            'seller_id' => session('seller_user_id'),
            'seller_code' => session('seller_code'),
            'version' => config('version.version')
        ]);
    }
}