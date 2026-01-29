<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Basic\BaseService;
use App\Dao\AdminGroupDao;
use Framework\Core\App;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;
use Framework\Basic\BaseDao; // 引入父类类型
#use Framework\Database\DatabaseFactory;

/**
 * AdminGroupDao服务层
 * @extends BaseService<AdminGroupDao> // 指定泛型类型为 AdminGroupDao
 */
class AdminGroupService extends BaseService
{

    // 关键：通过 @Inject 注解注入 DAO
    #[Inject(id:AdminGroupDao::class)]
    protected ?BaseDao $dao = null;

    public function __construct(
        //protected DatabaseFactory $db // 构造函数注入
    ) {
        parent::__construct(); // 必须调用父类构造函数执行 inject()
    }

    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
        #parent::initialize();
    }
	
}