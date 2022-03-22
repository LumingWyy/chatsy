<?php

namespace app\model;

use think\Model;

class Word extends Model
{
    protected $table = 'v2_word';

    /**
     * 根据分类获取商户的常用语
     * @param $cateId
     * @param $sellerCode
     * @return array
     */
    public function getSellerWordByCate($cateId, $sellerCode)
    {
        try {

            $res = $this->where('cate_id', $cateId)
                ->where('seller_code', $sellerCode)->select()->toArray();
        } catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];
    }
}