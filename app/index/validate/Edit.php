<?php

/**
 * 注册会员验证器
 * @author WispX
 * @copyright WispX
 * @link http://gitee.com/wispx
 */

namespace app\index\validate;

use think\Validate;

class Edit extends Validate
{
    protected $rule = [
        'username'      => 'require|chsAlphaNum|length:2, 25',
        'password'      => 'length:4, 25',
    ];

    protected $message = [
        'username.require'      => '用户名不能为空',
        'username.chsAlphaNum'  => '用户名名只能是汉字、字母和数字',
        'username.length'       => '用户名长度必须在2-25之间',
        'password.length'       => '密码必须在4-25个字符之间',
    ];
}
