<?php

namespace app\admin\validate;

use think\Validate;

/**
 * 管理员验证器
 * Class AdminUser
 * @package app\admin\validate
 */
class AdminUser extends Validate
{
    protected $rule = [
        'password' => 'confirm:confirm_password',
        'confirm_password' => 'confirm:password',
        //'mobile' =>'unique:user,status',
        'status' => 'require',
        'username' => 'require',
        'id_card' => ['/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/'],
        'professional_number' => ['/^[A-Z]{1}+[A-Za-z0-9]{13}$/'],
        'job_number' => ['/^[0-9]{4}$/']
    ];

    protected $message = [
        'username.require' => '请输入用户名',
        'mobile.unique' => '手机号码已存在',
        'password.confirm' => '两次输入密码不一致',
        'confirm_password.confirm' => '两次输入密码不一致',
        'status.require' => '请选择状态',
        'id_card' => '请输入正确的身份证号',
        'professional_number' => '请输入正确的职业编号',
        'job_number' => '工号必须为四位数字'
    ];
}