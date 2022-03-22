<?php

namespace app\index\controller;

use app\model\CustomerInfo;
use app\model\Seller;
use think\Controller;
use app\model\Chat;

class Index extends Controller
{
    // demo 演示首页
    public function index()
    {
        return $this->fetch();
    }

    // 弹层方式显示聊天框
    public function chatBoxJs()
    {
        $sellerCode = input('param.u');

        $sellerModel = new Seller();
        $info = $sellerModel->getSellerInfo($sellerCode);

        if(0 != $info['code']) {
            return 'alert("部署有误！-- 商户不存在")';
        }

        if(empty($info['data'])) {
            return 'alert("部署有误！-- 商户信息为空")';
        }

        if (date("Y-m-d H:i:s") > $info['data']['valid_time']) {
            return 'alert("部署有误！-- 商户已过期")';
        }

        $domain = rtrim(request()->header()['referer'], '/');
        $domain = explode('/', $domain);
        $domain = $domain['0'] . '//' . $domain['2'];
	    //print_r($domain);
        if($domain != $info['data']['access_url']) {
            return 'alert("部署有误！ -- 接入域名不正确")';
        }
	
        $time = time();
        $this->assign([
            'domain' => config('whisper_socketio.domain'),
            'seller' => $sellerCode,
            'time' => $time,
            'token' => md5($sellerCode . config('whisper.salt') . $time)
        ]);

        if(request()->isMobile()) {
            return $this->fetch('mobile');
        }

        return $this->fetch();
    }

    // 客户端聊天窗口
    public function cliBox()
    {
        $sellerCode = input('param.u');
        $time = input('param.t');
        $token = input('param.tk');

        if(empty($sellerCode) || empty($time) || empty($token)) {
            return $this->fetch('error');
        }

        // token 签发日期大于2天了
        if(time() - $time > 86400 * 2) {
            return $this->fetch('error');
        }

        $safeToken = md5($sellerCode . config('whisper.salt') . $time);
        if($token != $safeToken) {
            return $this->fetch('error');
        }

        // 到期检测
        $sellerModel = new Seller();
        $sellerInfo = $sellerModel->getSellerInfo($sellerCode);
        if (0 != $sellerInfo['code'] || $sellerInfo['data']['valid_time'] < date('Y-m-d H:i:s')) {
            return $this->fetch('error');
        }

        $nowTime = time();
        $safeToken = md5($sellerCode . config('whisper.salt') . $nowTime);
        $token =  $sellerCode . '-' . $nowTime  . '-' . $safeToken;

        $this->assign([
            'port' => config('whisper_socketio.socket_port'),
            'nowTime' => $nowTime,
            'token' => $safeToken,
            'seller' => $sellerCode,
            'os' => (request()->isMobile()) ? 'm' : 'p',
            'direct_kefu' => '',
            'type' => 1, // 弹层显示
            'socket' => config('whisper.protocol') . config('whisper.socket') . '/' . $token
        ]);

        return $this->fetch();
    }

    // 固定连接打开聊天窗口
    public function chat()
    {
        $sellerCode = input('param.u');
        $kefuCode = input('param.f', '');

        // 到期检测
        $sellerModel = new Seller();
        $sellerInfo = $sellerModel->getSellerInfo($sellerCode);

        if (0 != $sellerInfo['code'] || $sellerInfo['data']['valid_time'] < date('Y-m-d H:i:s')) {
            return $this->fetch('error');
        }

        // 校验接入域名
        if (1 == config('whisper_socketio.default_link_flag') && (!isset($_SERVER['HTTP_REFERER']) ||
                rtrim($_SERVER['HTTP_REFERER'], '/') != $sellerInfo['data']['access_url'])) {
            return $this->fetch('error');
        }

        $nowTime = time();
        $safeToken = md5($sellerCode . config('whisper.salt') . $nowTime);
        $token =  $sellerCode . '-' . $nowTime  . '-' . md5($sellerCode . config('whisper.salt') . $nowTime);

        $this->assign([
            'port' => config('whisper_socketio.socket_port'),
            'nowTime' => $nowTime,
            'token' => $safeToken,
            'seller' => $sellerCode,
            'os' => (request()->isMobile()) ? 'm' : 'p',
            'direct_kefu' => $kefuCode, // 是否是电商模式
            'type' => 2, // 固定连接打开窗口
            'socket' => config('whisper.protocol') . config('whisper.socket') . '/' . $token
        ]);

        return $this->fetch('cli_box');
    }

    // 获取聊天信息
    public function getChatLog()
    {
        if(request()->isAjax()) {

            $param = input('param.');

            $log = new Chat();
            $list = $log->getCustomerChatLog($param);

            return json($list);
        }
    }

    // 访客留言
    public function leaveMsg()
    {
        if (request()->isPost()) {

            $param = input('post.');
            if(empty($param['username']) || empty($param['phone']) || empty($param['content'])){
                return json(['code' => -1, 'data' => '', 'msg' => '请全部填写']);
            }

            $param['add_time'] = time();

            try{
                db('leave_msg')->insert($param);
            }catch (\Exception $e){
                return json(['code' => -2, 'data' => '', 'msg' => '留言失败']);
            }

            return json(['code' => 0, 'data' => '', 'msg' => '留言成功']);
        }

        $sellerCode = input('param.s');

        $this->assign([
            'seller_code' => $sellerCode
        ]);

        return $this->fetch('msg/index');
    }

    // 更新访客信息
    public function updateUserInfo()
    {
        if (request()->isAjax()) {

            $customerId = input('param.customer_id');
            $sellerCode = input('param.seller_code');
            $referrer = input('param.referrer');

            $info = new CustomerInfo();

            $res = $info->updateCustomerInfo([
                'customer_id' => $customerId,
                'seller_code' => $sellerCode,
                'from_url' => $referrer,
                'search_engines' => $this->analysisEngine($referrer)
            ]);

            return json($res);
        }
    }

    // 给客服评价
    public function praise()
    {
        if (request()->isAjax()) {

            $param = input('post.');

            $param['kefu_code'] = ltrim($param['kefu_code'], "KF_");
            $param['add_time'] = date('Y-m-d H:i:s');

            try {

                db("praise")->insert($param);
            } catch (\Exception $e) {
                return json(['code' => -1, 'data' => '', 'msg' => '暂时无法评价']);
            }

            return json(['code' => 0, 'data' => '', 'msg' => '本次服务已结束，感谢您的评价']);
        }
    }

    /**
     * 分析来源
     * @param $referrer
     * @return string
     */
    protected function analysisEngine($referrer)
    {
        if (empty($referrer)) {
            return '直接訪問';
        } elseif (strstr($referrer, 'baidu.com')) { // 百度
            return '百度';
        } elseif (strstr($referrer, 'google.com') or strstr($referrer, 'google.cn')) { // 谷歌
            return '谷歌';
        } elseif (strstr($referrer, 'so.com')) { // 360搜索
            return '360';
        } elseif (strstr( $referrer, 'sogou.com')) { // 搜狗
            return '搜狗';
        } elseif (strstr( $referrer, 'soso.com')) { // 搜搜
            return '搜搜';
        } else {
            return '其他网站';
        }
    }
}
