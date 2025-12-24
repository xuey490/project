<?php
// app/Validate/User.php
namespace App\Validate;

use Framework\Validation\ThinkValidatorFactory; // ← 改为使用你自己的基类

use think\Validate;

class User extends Validate
{

	
    protected $rule = [
        'name'     => 'required|alphaDash|max:30',
		'age'      => 'numeric|between:1,120',
        'email'    => 'required|email',
        'password' => 'required|min:8|confirmed',
        'birthday' => 'date:Y-m-d|after:1900-01-01',
        'start_at' => 'date:Y-m-d|after:today',
        'phone'    => 'mobile',
        'id_card'  => 'idcard',
        'config'   => 'json',
    ];

    protected $message = [
		'name.alphaDash'    => '姓名只能包含字母、数字、下划线和中划线',
        'name.required'     => '姓名不能为空',
        'age.numeric'   	=> '年龄必须是数字',
        'age.between'  		=> '年龄只能在1-120之间',
        'password.confirmed'=> '两次输入的密码不一致',
        'birthday.date'     => '生日格式必须为 YYYY-MM-DD',
        'birthday.after'    => '生日不能早于1900年',
        'start_at.after'    => '开始日期必须是今天或之后',
        'phone.mobile'      => '手机号格式错误',
        'id_card.idcard'    => '身份证号码无效',
        'config.json'       => '配置必须是有效的 JSON 字符串',
    ];
	
	/*
    protected $rule = [
        'name'  => 'required|max:25',
        'age'   => 'numeric|between:1,120',
        'email' => 'email',
    ];

    protected $message = [
        'name.required' => '名称必须不能为空',
        'name.max'     => '名称最多不能超过25个字符',
        'age.numeric'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',
    ];
	*/

    // 可选：场景
    protected $scene = [
        'login'  => ['email'],
        'create' => ['name', 'age', 'email'],
    ];
}