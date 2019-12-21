<?php
namespace app\admin\validate;

use think\Validate;

class Sort extends Validate
{
    protected $rule = [
        'sort'         => 'require|number',
        'id'         => 'require|number6',
    ];

    protected $message = [
        'sort.number'         => '请输入数字',
    ];
}