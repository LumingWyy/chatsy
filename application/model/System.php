<?php

namespace app\model;

use think\Model;
use think\facade\Log;

class System extends Model
{
    protected $table = 'v2_system';

    /**
     * 获取商家的配置
     * @param $sellerCode
     * @return array
     */
    public function getSellerConfig($sellerCode)
    {
        try {

            $info = $this->where('seller_code', $sellerCode)->findOrEmpty()->toArray();
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $info, 'msg' => 'ok'];
    }
}