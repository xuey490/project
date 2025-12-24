<?php

namespace App\Validate;

use think\Validate;

class NewUser extends Validate
{
    protected $rule = [
      'name'  => 'require|alphaDash|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

    protected $message = [
	  'email'        => '邮箱格式错误',
      'name.require' => '名称必须',
      'name.alphaDash' => '名称只能是字母,数字，下划线组成',
      'name.max'     => '名称最多不能超过25个字符',
      'age.number'   => '年龄必须是数字',
      'age.between'  => '年龄必须在1~120之间',
    ];
}