<?php

namespace app\admin\controller;

use app\admin\model\Admin;

class Manager extends Base
{
    // 管理员列表
    public function index()
    {
        if(request()->isAjax()) {

            $limit = input('param.limit');
            $adminName = input('param.admin_name');

            $admin = new Admin();
            $list = $admin->getAdmins($limit, $adminName);

            if(0 == $list['code']) {

                return json(['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()]);
            }

            return json(['code' => 0, 'msg' => 'ok', 'count' => 0, 'data' => []]);
        }

        return $this->fetch();
    }

    // 添加管理员
    public function addAdmin()
    {
        if(request()->isPost()) {

            $param = input('post.');

            $param['admin_password'] = md5($param['admin_password'] . config('whisper.salt'));

            $admin = new admin();
            $res = $admin->addAdmin($param);

            return json($res);
        }

        return $this->fetch('add');
    }

    // 编辑管理员
    public function editAdmin()
    {
        if(request()->isPost()) {

            $param = input('post.');

            if(isset($param['admin_password'])) {
                $param['admin_password'] = md5($param['admin_password'] . config('whisper.salt'));
            }

            $admin = new admin();
            $res = $admin->editAdmin($param);

            return json($res);
        }

        $adminId = input('param.admin_id');
        $admin = new admin();

        $this->assign([
            'admin' => $admin->getAdminById($adminId)['data']
        ]);

        return $this->fetch('edit');
    }

    /**
     * 删除管理员
     * @return \think\response\Json
     */
    public function delAdmin()
    {
        if(request()->isAjax()) {

            $adminId = input('param.id');

            $admin = new admin();
            $res = $admin->delAdmin($adminId);

            return json($res);
        }
    }
}