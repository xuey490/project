<?php
declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use App\Services\UsersService;
use Symfony\Component\HttpFoundation\Request;


/**
 * 用户控制器
 * 无需编写基础 CURD 方法，直接继承 BaseController 的 CrudActionTrait
 */
class Users extends BaseController
{

    // 直接定义 daoClass
    #protected string $daoClass = \App\Dao\UserDao::class;
	
    /**
     * 指定 Service 类名，BaseController 自动初始化
     * @var string
     */
    protected string $serviceClass = UsersService::class;

    /**
     * 【自定义初始化】可覆盖父类 initialize 方法
     * 例如：设置表单验证器、初始化多租户上下文等
     */
    protected function initialize(): void
    {
        parent::initialize();
	
        // 示例：绑定验证器（需自行实现 Validator 类）
        // $this->validator = app(UserValidator::class);
    }

    /**
     * 【扩展接口】多租户用户列表
     * 演示：在基础 CURD 外扩展自定义接口
     * @param Request $request
     * @return mixed
     */
    public function tenantUserList(Request $request)
    {
        try {
            $tenantId = intval($request->query->get('tenant_id'));
			$where['tenant_id'] = $tenantId;
            //$where = $this->selectInput($request); // 复用 CrudFilterTrait 的条件构建方法
			
			
            $list = $this->service->getUserListByTenant($tenantId, $where);
            return $this->formatNormal($list, count($list));
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}