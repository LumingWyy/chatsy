<?php

namespace app\service\controller;

use app\model\BlackList;
use app\model\Chat;
use app\model\Customer;
use app\model\CustomerInfo;
use app\model\Queue;
use app\model\Service as ServiceModel;
use think\Controller;

class Api extends Controller
{
    // 获取待服务的访客列表
    public function getNowServiceList()
    {
        $keFuCode = ltrim(input('param.kefu_code'), 'KF_');
        $sellerCode = input('param.seller_code');

        $service = new ServiceModel();
        $newService = $service->getServiceList($keFuCode);

        if(0 != $newService['code'] || empty($newService['data'])) {
            return json(['code' => 0, 'data' => [], 'msg' => 'empty list']);
        }

        $ids = [];
        foreach($newService['data'] as $key => $vo) {
            $ids[$vo['customer_id']] = $vo['service_log_id'];
        }

        $customer = new Customer();
        $list = $customer->getCustomerListByIds(array_keys($ids), $keFuCode);

        // 查询该商户下,访客被标注的名称
        $infoMap = [];
        $info = new CustomerInfo();
        $customerInfo = $info->getCustomerNameByIds(array_keys($ids), $sellerCode);

        if (0 == $customerInfo['code'] && !empty($customerInfo['data'])) {
            foreach ($customerInfo['data'] as $key => $vo) {
                $infoMap[$vo['customer_id']] = $vo['real_name'];
            }
        }

        if(0 == $list['code'] && !empty($list['data'])) {

            foreach($list['data'] as $key => $vo) {
                $list['data'][$key]['log_id'] = isset($ids[$vo['customer_id']]) ? $ids[$vo['customer_id']] : -1;
                $list['data'][$key]['real_name'] = isset($infoMap[$vo['customer_id']]) ? $infoMap[$vo['customer_id']] : '';
            }
        }

        return json($list);
    }

    // ip 定位
    public function getCity()
    {
        $ip = input('param.ip');

        $ip2region = new \Ip2Region();
        $info = $ip2region->btreeSearch($ip);

        $info = explode('|', $info['region']);

        $address = '';
        foreach($info as $vo) {
            if('0' !== $vo) {
                $address .= $vo . '-';
            }
        }

        return json(['code' => 0, 'data' => rtrim($address, '-'), 'msg' => 'ok']);
    }

    // 获取当前商户在线的未咨询的客户
    public function getCustomerQueue()
    {
        $sellerCode = input('param.seller_code');

        $queue = new Queue();
        $list = $queue->getCustomerList($sellerCode);

        return json($list);
    }

    // 获取聊天记录
    public function getChatLog()
    {
        $param = input('param.');

        $log = new Chat();
        $list = $log->getChatLogByClint($param);

        return json($list);
    }

    // 转接
    public function reLink()
    {
        $keFuCode = input('param.kefu_code');
        $sellerId = input('param.seller_id');

        try {

            $groups = db('group')->where('group_status', 1)->where('seller_id', $sellerId)->select();
            if(!empty($groups)) {

                foreach($groups as $key => $vo) {
                    $groups[$key]['users'] = db('kefu')->alias('a')
                        ->field('a.kefu_code,a.kefu_name,a.kefu_avatar,a.group_id,a.max_service_num,count(b.service_id) as service_num')
                        ->leftJoin('v2_now_service b', 'a.kefu_code = b.kefu_code')
                        ->where('a.group_id', $vo['group_id'])->where('a.online_status', 1)
                        ->where('a.kefu_code', '<>', $keFuCode)
                        ->group('a.group_id')
                        ->select();
                }
            }

        } catch (\Exception $e) {

            return json(['code' => -1, 'data' => [], 'msg' => $e->getMessage()]);
        }

        return json(['code' => 0, 'data' => $groups, 'msg' => 'online info']);
    }

    // 获取用户详情
    public function getCustomerInfo()
    {
        $customerId = input('param.customer_id');
        $sellerCode = input('param.seller_code');

        $info = new CustomerInfo();
        $detail = $info->getCustomerInfoById($customerId, $sellerCode);

        return json($detail);
    }

    // 更新访客信息
    public function updateCustomerInfo()
    {
        $param = input('post.');
        unset($param['u']);

        if (empty($param['real_name'])) {
            unset($param['real_name']);
        }

        if (empty($param['email'])) {
            unset($param['email']);
        }

        if (empty($param['phone'])) {
            unset($param['phone']);
        }

        if (empty($param['remark'])) {
            unset($param['remark']);
        }

        if (empty($param)) {
            return json(['code' => 0, 'data' => '', 'msg' => 'save nothing']);
        }

        $info = new CustomerInfo();
        $res = $info->updateCustomerInfo($param);

        return json($res);
    }

    // 将访客加入商户黑名单
    public function joinBlackList()
    {
        $param = input('post.');
        unset($param['u']);

        $black = new BlackList();
        $res = $black->updateBlackList($param);

        return json($res);
    }
}