<?php


namespace app\index\controller;

use app\model\Customer;
use app\model\Service;
use app\websocket\service\Distribution;
use app\model\KeFu;
use app\service\controller\Index;


class Line
{
    public $channelToken = '';

    // 访客连线分配测试
    public function testOnlineTask()
    {
        $customer = [
            'customer_id' => '52nemknbx6c000',
            'customer_name' => '访客52nemknbx6c000',
            'customer_avatar' => '/static/common/images/customer.png',
            'customer_ip' => 'line',
            'seller_code' => '5c6cbcb7d55ca',
            'client_id' => '7f00000107d000000013',
            'create_time' => date('Y-m-d H:i:s'),
            'online_status' => 1
        ];

        $res = Distribution::userOnlineTask($customer);

        echo "<pre>";
        print_r($res);
    }

    public function serviceList()
    {
        $service = new Service();
        $res = $service->getServiceList('5c6ce9f6d753b');

        $ids = [];
        foreach ($res['data'] as $key => $vo) {
            $ids[] = $vo['customer_id'];
        }

        $customer = new Customer();
        $res = $customer->getCustomerListByIds($ids, '5c6ce9f6d753b');

        echo "<pre>";
        print_r($res);
    }

    // api 获取空闲客服
    public function apiService()
    {
        $code = input('param.code');

        $kefuInfo = file_get_contents(request()->domain() . '/index/api/getFreeKeFu?code=' . $code);
        $kefuInfo = json_decode($kefuInfo, true);

        return json($kefuInfo);
    }

    // api 访客接入
    public function apiCustomerLink($userid, $username, $kefuname)
    {
        $KefuName = 'admin';
        $sellerCode = '5c6cbcb7d55ca';
        file_put_contents('./apiCustomerLink.txt', var_export(session('kf_user_name'), true));

        $keFu = new KeFu();
        $keFuInfo = $keFu->getKeFuInfo($kefuname, $sellerCode);
        $KefuCode = 'KF_' . $keFuInfo['data']['kefu_code'];

        // apiService 得到的客服信息
//        $kefuInfo = [
//            'data' => [
//                'kefu_code' => 'KF_5d90bd01dfa81',
//                'kefu_name' => 'admin',
//                'seller_code' => '5c6cbcb7d55ca'
//            ]
//        ];

        $content = [
            'uid' => $userid,
            "name" => $username,
            "avatar" => "/static/common/images/customer.png",
            "ip" => "line"
        ];

        $push_api_url = "http://127.0.0.1:2945";
        $post_data = [
            "cmd" => "link",
            "data" => $content,
            "kefu_code" => $KefuCode,
            "kefu_name" => $kefuname,
            "seller_code" => $sellerCode
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $return = curl_exec($ch);
        curl_close($ch);
        print_r($post_data);
        echo $return;
    }

    // api 聊天
    public function apiChat($userid, $username, $content, $kefuname)
    {
        //session('kf_user_name');
        //session('kf_user_id');
        file_put_contents('./apiChat.txt', var_export(session('kf_user_name'), true));

        $kefuName = 'admin';
        $sellerCode = '5c6cbcb7d55ca';
        $keFu = new KeFu();
        $keFuInfo = $keFu->getKeFuInfo($kefuname, $sellerCode);
        $KefuCode = 'KF_' . $keFuInfo['data']['kefu_code'];
        // apiService 得到的客服信息
//        $kefuInfo = [
//            'data' => [
//                'kefu_code' => $KefuCode,
//                'kefu_name' => $kefuName,
//                'seller_code' => $sellerCode
//            ]
//        ];

        $push_api_url = "http://127.0.0.1:2945";
        $post_data = [
            "cmd" => "c2sChat",
            "data" => [
                'from_name' => $username,
                'from_avatar' => "/static/common/images/customer.png",
                'from_id' => $userid,
                'content' => $content,
                'to_id' => $KefuCode,
                'to_name' => $kefuname,
                'seller_code' => $sellerCode
            ]
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $return = curl_exec($ch);
        curl_close($ch);

        echo $return;
        $httpStatus = substr($return, 8, 3);

        return $httpStatus;
    }

    // 接受客服发来的消息
    public function receive()
    {
        $param = input('post.');

        //file_put_contents('./log.log', var_export($param, true));
        /**
         * 消息体如下 -- 聊天消息
         * [
         *      'cmd' => 's2cChat',
         *      'data' => [
         *           'from_name' => "客服小白",
         *           'from_avatar' => "//tva2.sinaimg.cn/crop.0.0.512.512.180/005LMAegjw8f2bp9qg4mrj30e80e8dg5.jpg",
         *           'from_id' => KF_WERWERSDF,
         *           'content' => '客服返回消息',
         *           'to_id' => 20190321,
         *           'to_name' => '访客20190321',
         *           'seller_code' => '5c6cbcb7d55ca'
         *      ]
         * ]
         */

        /**
         * 消息体如下  -- 转接消息
         * [
         *      'cmd' => 'relink',
         *      'data' => [
         *          'kefu_code' => 'KF_qeqw',
         *          'kefu_name' => '客服小白',
         *          'msg' => '您已被转接'
         *      ]
         * ]
         */

        /**
         * 消息体如下  -- 被关闭消息
         * [
         *      'cmd' => 'isClose',
         *       'data' => [
         *          'msg' => '客服下班了,稍后再来吧。'
         *       ]
         * ]
         */
        //print_r($param);

        $userid = $param['data']['to_id'];
        $message = $param['data']['content'];
        $headers = [
            'Authorization: Bearer ' . $this->channelToken,
            'Content-Type: application/json; charset=utf-8',
        ];


        $post = [
            'to' => $userid,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],

            ],
        ];
        $post = json_encode($post);


        if (substr($message, 0, 3) == "img") {
            $imgurl = "https://wangluming.net" . substr($message, 4, -1);
            $post = [
                'to' => $userid,
                'messages' => [
                    [
                        'type' => 'image',
                        'originalContentUrl' => $imgurl,
                        'previewImageUrl' => $imgurl
                    ],
                ],
            ];
            $post = json_encode($post);
        }

// HTTP
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        $options = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);


        $result = curl_exec($ch);

        $errno = curl_errno($ch);
        if ($errno) {
            return;
        }

// HTTP
        $info = curl_getinfo($ch);
        $httpStatus = $info['http_code'];

        $responseHeaderSize = $info['header_size'];
        $body = substr($result, $responseHeaderSize);

// 200  OK
        echo $httpStatus . ' ' . $body;
        //echo $result;
    }

    public function getLineLink()
    {

        $password = 'login';      // user login password
//        $dbFilePath = './line-db.json';        // user info database file path
//        if (!file_exists($dbFilePath)) {
//            file_put_contents($dbFilePath, json_encode(['user' => []]));
//        }
//        $db = json_decode(file_get_contents($dbFilePath), true);
        $bodyMsg = file_get_contents('php://input');
        file_put_contents('./userinfo.txt', date('Y-m-d H:i:s') . 'Recive: ' . $bodyMsg);
        $obj = json_decode($bodyMsg, true);
        file_put_contents('log.txt', print_r($obj, true));
        //file_put_contents('/log.log', print_r($obj, true));
        $customerModel = new Customer();
        foreach ($obj['events'] as &$event) {

            $userId = $event['source']['userId'];
            $type = $event['message']['type'];
            $content = "";
            if ($type == 'text') {
                $content = $event['message']['text'];
            }
            //客服信息
            $kefuname = 'admin';
            $sellercode = '5c6cbcb7d55ca';

            $verification = $customerModel->getCustomerInfoById($userId, $sellercode);
            file_put_contents('./text.txt', print_r($verification, true));

            if (sizeof($verification['data']) == 0) {
                //if (!isset($db['user'][$userId])) {
                if (substr($content, 0, 5) === $password) {//验证登陆关键字
                    $username = substr($content, 5);
                    $username = str_replace(' ', '', $username);//去除空格

                    $db['user'][$userId] = [
                        'userId' => $userId,
                        'userName' => $username,
                        'timestamp' => $event['timestamp']
                    ];
                    //file_put_contents($dbFilePath, json_encode($db));

                    $message = 'Login Success! Wellcome!';
                    $customer = [
                        'customer_id' => $userId,
                        'customer_name' => $username,
                        'customer_avatar' => "/static/common/images/customer.png",
                        'customer_ip' => "line",
                        'seller_code' => $sellercode,
                        'client_id' => 0,
                        'create_time' => date('Y-m-d H:i:s'),
                        'online_status' => 1,
                        'protocol' => 'http'
                    ];
                    $customerModel->updateCustomer($customer);
                } else {
                    $message = '名前を登録してください。 形式は　「login名前」';
                }
            } else {
                if ($event['message']['type'] == "image") {//图片处理
                    $id = $event['message']['id'];
                    $url = 'https://api.line.me/v2/bot/message/' . $id . '/content';//保存图片

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $this->channelToken
                    ]);
                    $json_content = curl_exec($ch);
                    curl_close($ch);

                    if (!$json_content) {
                        return false;
                    }
                    $fileURL = './image/' . $id . '.' . 'png';
                    $fp = fopen($fileURL, 'w');
                    fwrite($fp, $json_content);
                    fclose($fp);
                    $content = 'img[' . substr($fileURL, 1) . ']';
                    //file_put_contents('./log.log', print_r($content, true));
                }

                $username = $verification['data']['customer_name'];
                //$username = $db['user'][$userId]['userName'];
                $this->apiCustomerLink($userId, $username, $kefuname);
                file_put_contents('./log2.txt', print_r($username, true));
                $httpStatus = $this->apiChat($userId, $username, $content, $kefuname);

                if ($httpStatus == '401') {
                    $message = '今担当者が外しています。営業時間：9:00 ~ 17:00';
                }
            }

            // Make payload
            $payload = [
                'replyToken' => $event['replyToken'],
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message
                    ]
                ]
            ];

            // Send reply API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/reply');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channelToken
            ]);
            $result = curl_exec($ch);
            curl_close($ch);

        }
    }

    public function getCustomer()
    {
        define('SQL_HOST', '127.0.0.1');
        define("SQL_USER", "root");
        define("SQL_PASSWORD", "9bf77138aa2e8b54");
        define("SQL_DATABASE", "whisper");
        define("SQL_PORT", "3306");
        $mysql = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASSWORD, SQL_DATABASE, SQL_PORT) or die(mysqli_error());
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        $arr = array();
        $sql = "select distinct customer_id ,customer_name from v2_customer where customer_ip = 'line' ";//sql语句
        $result = $mysql->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($arr, $row);
        }
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        print_r($row['customer_id']);
        print_r($row['customer_name']);

        mysqli_close($mysql);
    }

    public function reply($UserId, $Message)
    {

        $userid = $UserId;
        $message = $Message;
        $headers = [
            'Authorization: Bearer ' . $this->channelToken,
            'Content-Type: application/json; charset=utf-8',
        ];


        $post = [
            'to' => $userid,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],

            ],
        ];
        $post = json_encode($post);


        if (substr($message, 0, 3) == "img") {
            $imgurl = "https://wangluming.net" . substr($message, 4, -1);
            $post = [
                'to' => $userid,
                'messages' => [
                    [
                        'type' => 'image',
                        'originalContentUrl' => $imgurl,
                        'previewImageUrl' => $imgurl
                    ],
                ],
            ];
            $post = json_encode($post);
        }

// HTTP
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        $options = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $post,
        ];
        curl_setopt_array($ch, $options);


        $result = curl_exec($ch);

        $errno = curl_errno($ch);
        if ($errno) {
            return;
        }

// HTTP
        $info = curl_getinfo($ch);
        $httpStatus = $info['http_code'];

        $responseHeaderSize = $info['header_size'];
        $body = substr($result, $responseHeaderSize);

// 200  OK
        echo $httpStatus . ' ' . $body;
        //echo $result;
    }

    //清除session constomername
    public function sessionDestoryCN()
    {
        session('customer_name', null);
        //unset($_SESSION['customer_name']);
        print_r(session('customer_name'));
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
        $index = new Index();
        $index->index();
    }
}