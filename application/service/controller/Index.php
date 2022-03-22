<?php


namespace app\service\controller;

use app\model\Cate;
use app\model\KeFu;
use app\model\System;
use app\model\Customer;
use app\index\controller\Test;


class Index extends Base
{
    // 客服服务台
    public function index()
    {
        $sellerCode = session('kf_seller_code');
        //$customerName = session('customer_name');

        $time = time();
        $safeToken = md5($sellerCode . config('whisper.salt') . $time);
        $token = $sellerCode . '-' . $time . '-' . $safeToken;

        $cateModel = new Cate();
        $systemModel = new System();
        $customerName = session('customer_name');
        $customerName = str_replace(' ', '', $customerName);
        $customer = new Customer();
        $CustomerInfo = $customer->getCustomerInfoByName($customerName, $sellerCode);
//        file_put_contents('./log1.txt', print_r($customerName, true));
        if (!empty($CustomerInfo['data'])) {
            $customerId = $CustomerInfo['data']['customer_id'];
            $this->assign([
                'port' => config('whisper_socketio.socket_port'),
                'seller' => $sellerCode,
                'socket' => config('whisper.protocol') . config('whisper.socket') . '/' . $token,
                'userName' => session('kf_user_name'),
                'userCode' => session('kf_user_id'),
                'userAvatar' => session('kf_user_avatar'),
                'word' => $cateModel->getSellerWord(session('kf_seller_id'), session('kf_seller_code'))['data'],
                'system' => $systemModel->getSellerConfig(session('kf_seller_code'))['data']
                , 'customerName' => $customerName
                , 'customerId' => $customerId
            ]);
//            $test = new Test();
//            $test->apiCustomerLink($customerId, $customerName);
//            file_put_contents('./log2.txt', print_r($customerId, true));
        } else {
            $this->assign([
                'port' => config('whisper_socketio.socket_port'),
                'seller' => $sellerCode,
                'socket' => config('whisper.protocol') . config('whisper.socket') . '/' . $token,
                'userName' => session('kf_user_name'),
                'userCode' => session('kf_user_id'),
                'userAvatar' => session('kf_user_avatar'),
                'word' => $cateModel->getSellerWord(session('kf_seller_id'), session('kf_seller_code'))['data'],
                'system' => $systemModel->getSellerConfig(session('kf_seller_code'))['data']
                , 'customerName' => $customerName
                , 'customerId' => ""
            ]);
        }


        if (request()->isMobile()) {
            return $this->fetch('mobile');
        }

        return $this->fetch();
    }

    public function loginByUrl($User, $CustomerName)
    {
        $sellercode = '5c6cbcb7d55ca';
        $customerName = $CustomerName;
        $keFu = new KeFu();
        $keFuInfo = $keFu->getKeFuInfo($User, $sellercode);
        session('kf_user_name', $keFuInfo['data']['kefu_name']);
        session('kf_user_id', $keFuInfo['data']['kefu_code']);
        session('kf_user_avatar', $keFuInfo['data']['kefu_avatar']);
        session('kf_seller_id', $keFuInfo['data']['seller_id']);
        session('kf_seller_code', $keFuInfo['data']['seller_code']);
        session('customer_name', $customerName);
        $this->index();
    }

}