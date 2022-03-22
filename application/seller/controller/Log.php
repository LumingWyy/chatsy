<?php

namespace app\seller\controller;

use app\model\Chat;
use app\seller\model\Log as LogModel;
use app\seller\model\Msg;

class Log extends Base
{
    // 聊天日志
    public function index()
    {
        if(request()->isAjax()) {

            $limit = input('param.limit');
            $customerName = input('param.customerName');

            $logModel = new LogModel();
            $list = $logModel->getChatLogList($limit, $customerName);

            if(0 == $list['code']) {

                return json(['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()]);
            }

            return json(['code' => 0, 'msg' => 'ok', 'count' => 0, 'data' => []]);
        }

        return $this->fetch();
    }

    // 获取聊天详情
    public function getChatLogDetail()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $log = new Chat();
            $list = $log->getSellerChatLog($param);

            return json($list);
        }

        $this->assign([
            'uid' => input('param.uid')
        ]);

        return $this->fetch('detail');
    }

    // 访客留言
    public function leave()
    {
        if(request()->isAjax()) {

            $limit = input('param.limit');

            $msgModel = new Msg();
            $list = $msgModel->getLeaveMsgList($limit);

            if(0 == $list['code']) {

                return json(['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()]);
            }

            return json(['code' => 0, 'msg' => 'ok', 'count' => 0, 'data' => []]);
        }

        // 获取未读留言消息
        $noRead = (new Msg())->getNoReadMsgCount()['data'];

        $this->assign([
            'no_read' => $noRead
        ]);

        return $this->fetch();
    }

    // 标记已读
    public function readMsg()
    {
        if(request()->isAjax()) {

            $id = input('param.id');

            $msgModel = new Msg();
            $res = $msgModel->updateMsgStatus($id);

            return json($res);
        }
    }

    // 全部标记已读
    public function readAll()
    {
        if(request()->isAjax()) {

            $msgModel = new Msg();
            $res = $msgModel->updateMsgStatusBatch();

            return json($res);
        }
    }
}