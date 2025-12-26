<?php

namespace App\Dao;

use Framework\Basic\BaseDao;

use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Context;

use App\Repository\ModuleRepository;
use App\Dao\NoticeDao;

class CustomDao extends BaseDao
{
    #[Autowire]
    protected ModuleRepository $moduleRepo;	
	
	#[Autowire]
    protected NoticeDao $noticedao;	
	
	protected string $modelClass = \App\Models\Custom::class;

    // 指定该 DAO 操作哪个模型
    protected function setModel(): string
    {
        return \App\Models\Custom::class;
    }
    
    // 如果有特殊业务逻辑，可以在这里封装
    public function getActiveUsers()
    {
		//return ($this->noticedao->getData()); //生效
        // 调用底层的 selectList (通过 __call 转发给 ThinkORMFactory)
        return $this->selectList(['status' => 1], '*', 0, 2, 'id desc')->toArray();
    }
}