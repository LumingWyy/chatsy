<?php

namespace app\seller\validate;

use think\Validate;

class GroupValidate extends Validate
{
    protected $rule =   [
        'group_name'  => 'require'
    ];

    protected $message  =   [
        'group_name.require' => 'グループ名は空にできません'
    ];
}