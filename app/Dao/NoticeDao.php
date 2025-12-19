<?php

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Models\Notice;

class NoticeDao extends BaseDao
{
    protected function setModel(): string
    {
        return Notice::class;
    }
	
	public function getData(): array
	{
		return ['id' => 1, 'name' => 'mike'];
	}
}
