<?php

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    protected $table = 'v2_admin';

    /**
     * 获取管理员
     * @param $limit
     * @param $adminName
     * @return array
     */
    public function getAdmins($limit, $adminName)
    {
        try {

            if (!empty($adminName)) {

                $res = $this->where('admin_name', 'like', $adminName . '%')->paginate($limit);
            } else {

                $res = $this->paginate($limit);
            }

        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];
    }

    /**
     * 增加管理员
     * @param $admin
     * @return array
     */
    public function addAdmin($admin)
    {
        try {

            $has = $this->where('admin_name', $admin['admin_name'])->findOrEmpty()->toArray();
            if(!empty($has)) {
                return ['code' => -2, 'data' => '', 'msg' => '管理员名存在しています'];
            }

            $this->insert($admin);
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '管理员を増設成功'];
    }

    /**
     * 获取管理员信息
     * @param $adminId
     * @return array
     */
    public function getAdminById($adminId)
    {
        try {

            $info = $this->where('admin_id', $adminId)->findOrEmpty()->toArray();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $info, 'msg' => 'ok'];
    }

    /**
     * 编辑管理员
     * @param $admin
     * @return array
     */
    public function editAdmin($admin)
    {
        try {

            $has = $this->where('admin_name', $admin['admin_name'])->where('admin_id', '<>', $admin['admin_id'])
                ->findOrEmpty()->toArray();
            if(!empty($has)) {
                return ['code' => -2, 'data' => '', 'msg' => '管理员名存在しています'];
            }

            $this->save($admin, ['admin_id' => $admin['admin_id']]);
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '管理员を増設成功'];
    }

    /**
     * 删除管理员
     * @param $adminId
     * @return array
     */
    public function delAdmin($adminId)
    {
        try {
            if (1 == $adminId) {
                return ['code' => -2, 'data' => '', 'msg' => 'admin管理员を削除できません'];
            }

            $this->where('admin_id', $adminId)->delete();
        } catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '削除成功'];
    }

    /**
     * 获取管理员信息
     * @param $name
     * @return array
     */
    public function getAdminByName($name)
    {
        try {

            $info = $this->where('admin_name', $name)->findOrEmpty()->toArray();
        } catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $info, 'msg' => 'ok'];
    }

    /**
     * 获取管理员信息
     * @param $id
     * @return array
     */
    public function getAdminInfo($id)
    {
        try {

            $info = $this->where('admin_id', $id)->findOrEmpty()->toArray();
        } catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $info, 'msg' => 'ok'];
    }
}