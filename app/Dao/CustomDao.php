<?php

namespace App\Dao;

use Framework\Basic\BaseDao;


class CustomDao extends BaseDao
{
    // 指定该 DAO 操作哪个模型
    protected function setModel(): string
    {
        return \App\Models\Custom::class;
    }
    
    // 如果有特殊业务逻辑，可以在这里封装
    public function getActiveUsers()
    {
        // 调用底层的 selectList (通过 __call 转发给 ThinkORMFactory)
        return $this->selectList(['status' => 1], '*', 1, 10, 'id desc')->toArray();
    }
}