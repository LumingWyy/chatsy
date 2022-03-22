<?php

namespace app\model;

use think\facade\Log;
use think\Model;

class Chat extends Model
{
    protected $table = 'v2_chat_log';

    /**
     * 记录聊天日志
     * @param $param
     * @return int|string
     */
    public function addChatLog($param)
    {
        try {

            return $this->insertGetId($param);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return 0;
    }

    /**
     * 获取聊天日志信息[客服端]
     * @param $param
     * @return array
     */
    public function getChatLog($param)
    {
        try {

            $limit = config('whisper.log_page');
            $offset = ($param['page'] - 1) * $limit;

            $sellerCode = session('kf_seller_code');

            $temp = $this->where(function($query) use($param, $sellerCode) {
                $query->where('from_id', $param['uid'])->where('seller_code', $sellerCode);
            })->whereOr(function($query) use($param, $sellerCode) {
                $query->where('seller_code', $sellerCode)->where('to_id', $param['uid']);
            });

            $logs = $temp->limit($offset, $limit)->order('log_id', 'desc')->select()->toArray();
            sort($logs);
            $total = $temp->count();

            foreach($logs as $key => $vo) {

                $logs[$key]['type'] = 'user';

                if(strpos($vo['from_id'], 'KF_') !== false) {
                    $logs[$key]['type'] = 'mine';
                }
            }

            return ['code' => 0, 'data' => $logs, 'msg' => intval($param['page']), 'total' => ceil($total / $limit)];
        } catch (\Exception $e) {

            return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
        }
    }

    /**
     * 获取聊天日志信息[访客端]
     * @param $param
     * @return array
     */
    public function getCustomerChatLog($param)
    {
        try {

            $sellerCode = $param['u'];
            $time = $param['t'];
            $token = $param['tk'];

            // 权限校验
            if(time() - $time > 86400 * 2) {
                return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
            }

            $safeToken = md5($sellerCode . config('whisper.salt') . $time);
            if($token != $safeToken) {
                return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
            }

            $limit = config('whisper.log_page');
            $offset = ($param['page'] - 1) * $limit;

            $temp = $this->where(function($query) use($param, $sellerCode) {
                $query->where('from_id', $param['uid'])->where('seller_code', $sellerCode);
            })->whereOr(function($query) use($param, $sellerCode) {
                $query->where('seller_code', $sellerCode)->where('to_id', $param['uid']);
            });

            $logs = $temp->limit($offset, $limit)->order('log_id', 'desc')->select()->toArray();
            sort($logs);
            $total = $temp->count();

            foreach($logs as $key => $vo) {

                $logs[$key]['type'] = 'user';

                if(strpos($vo['from_id'], 'KF_') === false) {
                    $logs[$key]['type'] = 'mine';
                }
            }

            return ['code' => 0, 'data' => $logs, 'msg' => intval($param['page']), 'total' => ceil($total / $limit)];
        } catch (\Exception $e) {

            return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
        }
    }

    /**
     * 获取聊天日志信息[商户端]
     * @param $param
     * @return array
     */
    public function getSellerChatLog($param)
    {
        try {

            $limit = config('whisper.log_page');
            $offset = ($param['page'] - 1) * $limit;

            $sellerCode = session('seller_code');

            $temp = $this->where(function($query) use($param, $sellerCode) {
                $query->where('from_id', $param['uid'])->where('seller_code', $sellerCode);
            })->whereOr(function($query) use($param, $sellerCode) {
                $query->where('seller_code', $sellerCode)->where('to_id', $param['uid']);
            });

            $logs = $temp->limit($offset, $limit)->order('log_id', 'desc')->select()->toArray();
            sort($logs);
            $total = $temp->count();

            foreach($logs as $key => $vo) {

                $logs[$key]['type'] = 'user';

                if(strpos($vo['from_id'], 'KF_') !== false) {
                    $logs[$key]['type'] = 'mine';
                }
            }

            return ['code' => 0, 'data' => $logs, 'msg' => intval($param['page']), 'total' => ceil($total / $limit)];
        } catch (\Exception $e) {

            return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
        }
    }

    /**
     * 批量更新阅读状态
     * @param $ids
     * @return array
     */
    public function updateReadStatusBatch($ids)
    {
        try {

            $this->whereIn('log_id', $ids)->setField('read_flag', 2);

            return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
        }  catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['code' => -1, 'data' => [], 'msg' => 0, 'total' => 0];
        }
    }

    /**
     * 获取聊天日志信息[PC客服端]
     * @param $param
     * @return array
     */
    public function getChatLogByClint($param)
    {
        try {

            $limit = config('whisper.log_page');
            $offset = ($param['page'] - 1) * $limit;

            $sellerCode = $param['seller_code'];

            $temp = $this->where(function($query) use($param, $sellerCode) {
                $query->where('from_id', $param['uid'])->where('seller_code', $sellerCode);
            })->whereOr(function($query) use($param, $sellerCode) {
                $query->where('seller_code', $sellerCode)->where('to_id', $param['uid']);
            });

            $logs = $temp->limit($offset, $limit)->order('log_id', 'desc')->select()->toArray();
            sort($logs);
            $total = $temp->count();

            foreach($logs as $key => $vo) {

                $logs[$key]['type'] = 'user';

                if(strpos($vo['from_id'], 'KF_') !== false) {
                    $logs[$key]['type'] = 'mine';
                }
            }

            return ['code' => 0, 'data' => $logs, 'msg' => intval($param['page']), 'total' => ceil($total / $limit)];
        } catch (\Exception $e) {

            return ['code' => 0, 'data' => [], 'msg' => 0, 'total' => 0];
        }
    }
}