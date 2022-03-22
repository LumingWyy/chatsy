<?php

namespace app\seller\model;

use think\Model;

class Cate extends Model
{
    protected $table = 'v2_word_cate';

    /**
     * 获取分类列表
     * @param $limit
     * @param $where
     * @return array
     */
    public function getCateList($limit, $where = [])
    {
        try {

            $res = $this->where('seller_id', session('seller_user_id'))
                ->where($where)
                ->order('cate_id', 'desc')
                ->paginate($limit);
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];
    }

    /**
     * 增加分类
     * @param $param
     * @return array
     */
    public function addCate($param)
    {
        try {

            $has = $this->where('cate_name', $param['cate_name'])
                ->where('seller_id', session('seller_user_id'))
                ->findOrEmpty()->toArray();
            if(!empty($has)) {
                return ['code' => -2, 'data' => '', 'msg' => 'この類別が存在しています'];
            }

            $this->save($param);
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '追加成功'];
    }

    /**
     * 编辑分类
     * @param $param
     * @return array
     */
    public function editCate($param)
    {
        try {

            $has = $this->where('cate_name', $param['cate_name'])
                ->where('seller_id', session('seller_user_id'))
                ->where('cate_id', '<>', $param['cate_id'])
                ->findOrEmpty()->toArray();
            if(!empty($has)) {
                return ['code' => -2, 'data' => '', 'msg' => 'この類別が存在しています'];
            }

            $this->where('cate_id', $param['cate_id'])->update($param);
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '記入成功'];
    }

    /**
     * 删除分类
     * @param $cateId
     * @return array
     */
    public function delCate($cateId)
    {
        try {
            $wordModel = new Word();
            $hasWord = $wordModel->checkHasWordByCateId($cateId);
            if ($hasWord['data'] > 0) {
                return ['code' => -1, 'data' => '', 'msg' => '削除できません'];
            }

            $this->where('cate_id', $cateId)->where('seller_id', session('seller_user_id'))->delete();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => '', 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => '', 'msg' => '削除成功'];
    }

    /**
     * 获取分类信息
     * @param $cateId
     * @return array
     */
    public function getCateInfoByCateId($cateId)
    {
        try {

            $res = $this->where('cate_id', $cateId)->where('seller_id', session('seller_user_id'))->find();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'success'];
    }

    /**
     * 获取商户的可用分类
     * @return array
     */
    public function getSellerCate()
    {
        try {

            $res = $this->where('seller_id', session('seller_user_id'))->where('status', 1)->select();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'success'];
    }
}