<?php

namespace app\seller\validate;

use think\Validate;

class KeFuValidate extends Validate
{
    protected $rule =   [
        'kefu_name'  => 'require',
        'kefu_password'   => 'require',
        'group_id'   => 'require|number',
        'max_service_num' => 'require|number'
    ];

    protected $message  =   [
        'kefu_name.require' => '担当者名が空にでいきません',
        'kefu_password.require'   => 'パスワードを空にできません',
        'group_id.require'  => 'グループを選択してください',
        'max_service_num.require'  => '担当者人数を記入してください',
        'max_service_num.number'  => '必ず数字にしてください',
    ];

    protected $scene = [
        'edit'  =>  ['kefu_name', 'group_id', 'max_service_num']
    ];
}