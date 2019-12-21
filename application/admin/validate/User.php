<?php
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'nick_name'         => 'require|unique:user|min:6',
        'password'         => 'confirm:confirm_password|min:6',
        'confirm_password' => 'confirm:password',
        'mobile'           => 'number|length:11',
        'status'           => 'require',
    ];

    protected $message = [
        'nick_name.require'         => '请输入用户名',
    		'nick_name.min'         => '用户名至少6位',
        'nick_name.unique'          => '用户名已存在',
        'password.confirm'         => '两次输入密码不一致',
    		'password.length'         => '密码不小于6位',
        'confirm_password.confirm' => '两次输入密码不一致',
        'phone.number'            => '手机号格式错误',
        'phone.length'            => '手机号长度错误',
        'status.require'           => '请选择状态',
    		'usermail.unique'          => '邮箱已存在',
    ];
}