<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\model\LoginLog;
use think\Controller;

class Login extends Controller
{
    // 登录页面
    public function index()
    {
        return $this->fetch();
    }

    // 处理登录
    public function doLogin()
    {
        if(request()->isPost()) {

            $param = input('post.');

            if(!captcha_check($param['vercode'])){
                return json(['code' => -3, 'data' => '', 'msg' => '検证コード間違っています']);
            }

            $admin = new Admin();
            $adminInfo = $admin->getAdminByName($param['username']);

            $log = new LoginLog();

            if(0 != $adminInfo['code'] || empty($adminInfo['data'])) {

                $log->writeLoginLog($param['username'], 2);
                return json(['code' => -1, 'data' => '', 'msg' => 'ユーザIDまたはパスワードが間違っています。']);
            }

            if(md5($param['password'] . config('whisper.salt')) != $adminInfo['data']['admin_password']){

                $log->writeLoginLog($param['username'], 2);
                return json(['code' => -2, 'data' => '', 'msg' => 'ユーザIDまたはパスワードが間違っています。']);
            }

            // 设置session标识状态
            session('admin_user_name', $adminInfo['data']['admin_name']);
            session('admin_user_id', $adminInfo['data']['admin_id']);

            // 维护上次登录时间
            db('admin')->where('admin_id', $adminInfo['data']['admin_id'])->setField('last_login_time', date('Y-m-d H:i:s'));

            $log->writeLoginLog($param['username'], 1);

            return json(['code' => 0, 'data' => url('index/index'), 'msg' => '登録成功']);
        }
    }

    public function loginOut()
    {
        session('admin_user_name', null);
        session('admin_user_id', null);

        $this->redirect(url('login/index'));

    }
}