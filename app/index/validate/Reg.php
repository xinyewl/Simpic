<?php

/**
 * 注册会员验证器
 * @author WispX
 * @copyright WispX
 * @link http://gitee.com/wispx
 */

namespace app\index\validate;

use think\Validate;

class Reg extends Validate
{
    protected $rule = [
        'email'         => 'require|regex:/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/',
        'username'      => 'require|chsAlphaNum|length:2, 25',
        'password'      => 'require|length:4, 25',
        'passwords'     => 'require',
    ];

    protected $message = [
        'email.require'         => 'email不能为空',
        'email.regex'           => 'email格式不正确',
        'username.require'      => '用户名不能为空',
        'username.chsAlphaNum'  => '用户名名只能是汉字、字母和数字',
        'username.length'       => '用户名长度必须在2-25之间',
        'password.require'      => '请输入密码',
        'password.length'       => '密码必须在4-25个字符之间',
        'passwords.require'     => '请输入确认密码'
    ];
}
