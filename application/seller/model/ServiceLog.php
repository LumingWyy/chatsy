<?php

namespace app\seller\model;

use think\Model;

class ServiceLog extends Model
{
    protected $table = 'v2_customer_service_log';

    /**
     * 累计访问量
     * @return array
     */
    public function getTotalServiceNum()
    {
        try {

            $res = $this->where('seller_code', session('seller_code'))->count();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => 0, 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];
    }

    /**
     * 今日接待数量
     * @return array
     */
    public function getTodayServiceNum()
    {
        try {

            $res = $this->where('seller_code', session('seller_code'))
                ->where('start_time', '>' , date('Y-m-d 00:00:00', time()))->count();
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => 0, 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];
    }

    /**
     * 统计访问列表数据
     * @param $where
     * @param $limit
     * @param $keFuArr
     * @return array
     */
    public function getServiceList($where, $limit, $keFuArr)
    {
        try {

            $res = db('customer_service_log')->where('seller_code', session('seller_code'))
                ->where($where)->order('service_log_id', 'desc')->paginate($limit)
                ->each(function($item, $key) use ($keFuArr) {

                    $item['kefu_code'] = isset($keFuArr[$item['kefu_code']]) ? $keFuArr[$item['kefu_code']] : $item['kefu_code'];
                    $item['location'] = getLocationByIp($item['customer_ip']);

                    if ('0000-00-00 00:00:00' == $item['end_time']) {
                        $item['service_time'] = '終わっていません、またシステムエラー';
                    } else {
                        $item['service_time'] = changeTimeType(strtotime($item['end_time']) - strtotime($item['start_time']));
                    }

                    return $item;
                });
        }catch (\Exception $e) {

            return ['code' => -1, 'data' => [], 'msg' => $e->getMessage()];
        }

        return ['code' => 0, 'data' => $res, 'msg' => 'ok'];

    }
}