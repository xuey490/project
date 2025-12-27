<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;

use App\Models\Admin;
use App\Models\Config;
use App\Models\Custom;
use App\Dao\CustomDao;

use App\Middlewares\AuthMiddleware;
use Framework\Middleware\MiddlewareXssFilter;
use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Framework\Utils\Captcha as CCaptcha;
use Framework\Utils\CookieManager;
use Framework\Attributes\Auth;
use Framework\Attributes\Route;


use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;
use Framework\Core\App;


use Framework\Database\DatabaseFactory;
#use Illuminate\Database\Capsule\Manager as Capsule;
#use Illuminate\Support\Facades\DB;
#use Illuminate\Database\Schema\Blueprint;
#use Illuminate\Support\Facades\Schema;

use Framework\Utils\Snowflake;
use Framework\Basic\BaseController;


use think\facade\Db;

use App\Repository\UserRepository;



use Framework\Attributes\Validate; // 引入注解
use App\Validate\NewUser as UserValidate; // 引入你的验证器类


use App\Services\UserService;
use Framework\Tenant\TenantContext;	//启用租户隔离



##[Auth(roles: ['admin'])]
##[Prefix('/secures', middleware: [AuthMiddleware::class])]
##[Route(prefix: '/vvv2/admins', group: 'aaaa', middleware: [\App\Middlewares\AuthMiddleware::class])]
/*
 * @auth true
 * @role super
*/
class Home 
{
	
    // 注解注入服务
    #[Autowire]
    private UserService $UserService;	

	
	private CustomDao $customDao;
	
	
    public function __construct(
        private CsrfTokenManager $csrf,
		private RequestStack $requestStack,
		private Request $request,
		private CookieManager $cookie, 
		private DatabaseFactory $db,
		private UserRepository $userRepo,
		CustomDao $customDao,

		#private DB $db1,
		#private DbManager $db1
		//SessionServiceProvider 已经注册的服务名是 'session'， 容器会自动注入 Session 实例
    ) {
		$this->customDao = $customDao;
	}
	
	public function setcookies(Request $request):Response
	{

		$response = new Response('Cookie set!!!');
		$response->headers->setCookie(
			//原生的cookie设置方法
			new \Symfony\Component\HttpFoundation\Cookie('workerman_test', 'success_v5', time() + 3600, '/', '', false, true)
		);
		// 获取单个 cookie
		//$theme = $request->cookies->get('theme'); // 'dark' 或 null		
		    // 获取所有 cookies
		$allCookies = $request->cookies->all();
		//dump($allCookies);
		


		// 1️⃣ 设置 cookie 必须搭配send
		app('cookie')->queueCookie('workerman_teasdasst', 'asdsadasd');
		app('cookie')->sendQueuedCookies($response);
		
		//dump($allCookies['workerman_test']);
		return $response;		
	}
	
	
    ##[Auth] // 仅登录即可访问
	##[Route(path: '/aa',  auth: true, roles: ['admin'], methods: ['GET'], name: 'home.html')] //注解路由的auth roles
	##[Route(path: '/htmls/', auth: true, methods: ['GET'], middleware: [\App\Middlewares\AuthMiddleware::class], name: 'Homeindex')]
	##[GetMapping('/list')]
	public function html():Response
	{
		//thinkORM 和 DB 的测试
        $list = Db::name('config')->select();//error：Undefined db config:mysql
		//dump($list);
        #dump(($this->db)('config')->where('id' , 1)->select()->toArray());
		
		//ThinkORM Model的写法
        //$users = Admin::select()->toArray();
        //dump($users);	
		
		
		/*
		// 创建响应实例
		$response = new Response();

		// 设置响应内容（HTML 代码）
		$response->setContent('<h1>Hello, Symfony!</h1><p>这是 HTML 内容</p>');

		// 设置 Content-Type 为 text/html
		$response->headers->set('Content-Type', 'text/html');

		// 返回响应
		return $response;
		*/

	
		// 直接在构造函数中设置 HTML 内容和类型
		$response = new Response(
			'<html><body><h1>Hello, Symfony!</h1><p>这是 HTML 内容</p></body>',
			Response::HTTP_OK, // 状态码（200）
			['Content-Type' => 'text/html'] // 响应头
		);	
		##33#
		return $response;
		
	}
	
	public function index():Response
	{
		return new Response(
			'<html><body><h1>Hello, World!</h1></body></html>',
			Response::HTTP_OK, // 状态码（200）
			['Content-Type' => 'text/html'] // 响应头
		);	
	}
	
	##[Auth(required: true, roles: ['admin', 'editor'], guard: 'index')]
    public function index1(Request $request)
    {
		//dump($this->customDao->getActiveUsers());
		#$rawRouteMiddleware = $request->attributes->get('_middleware', []);
		#dump($rawRouteMiddleware);		
		//dump($request->headers->get('x-csrf-token'));
        // ✅ 此时 app() 已可用！

        //dump(app()->getServiceIds()); // 查看所有服务 ID

		//Eloquent 模型的写法
        //$config = Config::where('id', 1)->first()->toArray(); //得到 App\Models\Config 可以使用->toArray()转化
		
		//use Illuminate\Database\Capsule\Manager as Capsule; //必须要有这个才能下面的操作
		//$config = Capsule::table('config')->where('id', 1)->get()->toArray();  //得到：Illuminate\Support\Collection 可以使用->toArray()转化
		//dump($config);
		
		//Eloquent 模型 $this->db->make('config') 小写表名
		//$config =  $this->db->make('flow')->where('id', 1)->first();
		//$config =$this->db->make('flow')->find(1);
		//dump(app('response')->headers->set('Authorization', 'Bearer 123'));
		//dump($config);
		
		
		//$allHeaders = app('response')->headers->all();

		//dump($allHeaders);	

		//dump(app('redis.client'));
			
		//$this->db('表名') 的写法相当于 DB::table
		//Eloquent 和thinkorm 通用
		/*
		$test = ($this->db)('config')
		->orderBy('id', 'desc')
		->limit(10)
		->get()->toArray();
		*/
		
		#$count = ($this->db)('config')->count();  //5
		#$users = ($this->db)('config')->paginate(2);
        #dump($users);

		
		#dump(app('db')(('App\Models\Config'))->getFields());
		// 用 __invoke()  == > ($this->db)('App\Models\Config') 完整模型名不带::class
		//$config1 = ($this->db)('App\Models\Config')->where('id', 1)->first()->toArray();
		//dump($config1);
		
		// Laravel 风格查询
		//use Illuminate\Support\Facades\DB;
		/*
		$count = DB::table('config')->where('id', 1)->count();
		DB::beginTransaction();
		try {
			$config2 = DB::table('config')
				->orderBy('id', 'desc')
				->limit(10)
				->get();
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			throw $e;
		}		
		*/
		
		#dump($config2);

		//$session = $request->getSession();
		//$session->set('test', 'workerman');	
		
        // getService(\Framework\Log\LoggerService::class)->info('App started');

        # $userService = getService('App\Service\UserService'); // ✅ 只要容器已 set，就可以
        

		
		//ThinkORM Model的写法
        #$users = Config::select()->get()->toArray();
        #dump($users);	
			
		//ThinkORM Model的写法
        $user =App::make( Custom::class);
		//dump($user->getFields_1());
		
		//$user = App::make( Custom::class)->find(4152240944932200448);//更新操作
			#dump($user);
			//通用插入
		/*
		
        // 2. 给模型属性赋值（对应数据库表字段）
        $user->name = '9999';
        $user->englishname = '777';
        $user->nickname = '王111';
        $user->email = 'test11@example.com';
        $user->group_id = 101;
        //$user->create_time = date('Y-m-d H:i:s'); // 若开启自动时间戳，可省略
        
        // 3. 调用save()方法插入数据
        $result = $user->save();
        if ($result) {
            // 获取插入后的自增主键ID
            $insertId = $user->id;
            $string =  "插入成功，主键ID：{$insertId}";
        } else {
            // 获取错误信息
            $string =  "插入失败：" . $user->getError();
        }
		*/

		$data = [
			'name' => '李四11',
			'nickname' => '李四11',
			'englishname' => 'aaa',
			'email'=> 'zs@test.com',
			'group_id'=>1001,
			'status'=>1,
			#'created_at'=> time(),
			#'updated_at'=> time(),
			
		];
		
		#($this->userRepo)(\App\Models\User::class)->save($data);
		
		/*
		// find 会自动加上 AND tenant_id = 1001
		// 把 get() 改为 first()
		$info = $user->where('id', '4152317470889484288')->first(); 

		if ($info) {
			// 此时 $info 是 User 模型对象，可以正常赋值
			$info->nickname = '王五';
			$info->save(); 
		}
		
		$info = $user->find('4152317470889484288');
		if ($info) {
		$info->nickname = '王五11111111';
		$info->save();
		}		
		*/


		
		
		//上下文操作
		//TenantContext::restore();
		//TenantContext::setTenantId(1001);
		$userList = ($this->userRepo)(\App\Models\User::class)->where('status', 1)->get()->toArray(); 
		dump($userList);
		
		// 手动排除
		#$list = ($this->userRepo)(\App\Models\User::class)->withoutGlobalScope(['tenant'])->select()->toArray();
		// SQL: SELECT * FROM custom		
		#dump($userList);
		
($this->userRepo)(\App\Models\User::class)->where('id', 4152317470889484288)->update(['status' => 12]);
		
		//thinkphp的多租户演示
		#$userList1 = $user->withoutGlobalScope(['tenant'])->select();
		#dump(array_keys($user->getFields()));
		

		/*
		TenantContext::restore();
		TenantContext::setTenantId(100111);	//tenant_id 不存在
		*/
		//执行语句：SELECT * FROM `oa_custom` WHERE  `id` = '4152255745003626496'  AND `oa_custom`.`tenant_id` = '100111' LIMIT 1
		
		#$info = ($this->userRepo)(\App\Models\User::class)->where('id' ,'4152317470889484288')->get();//查询结果null
		#dump($info);
		#if($info) {
		#	$info->nickname = '123';
		#	$info->update_time = time();
		#	$info->save();
		#}
		
		
		//($this->userRepo)(\App\Models\User::class)->withoutGlobalScope(['tenant'])->where('id', 4152255745003626496)->update(['status' => 5]);
		
		/*
		//执行语句：UPDATE `oa_custom`  SET `nickname` = 'KKKKKKKKK'  WHERE  `id` = '4152257913374908416'  AND `oa_custom`.`tenant_id` = '100111'
		$user::where('id', '4152257913374908416')->update([
			'nickname' => 'KKKKKKKKK',
		]);

		//执行语句：UPDATE `oa_custom`  SET `nickname` = 'hack'  WHERE  `id` = '1'  AND `oa_custom`.`tenant_id` = '100111'
		$user::where('id', 1)->update([
			'nickname' => 'hack',
		]);
		*/
		
		
		#$info = $user::where('id' ,'4152255745003626496')->find();
		#$user->where('id', '4152255745003626496')->delete();
		
		#dump($user->getData());
		#dump(($this->userRepo)(\App\Models\User::class));
      
		//ThinkORM Model的写法
        #$user = (new Custom())->getTableName();
       	
		//$list1 = $this->customDao->getActiveUsers() ; //$this->customDao->count(['enabled'=>1]);
		// dump($list1);
		 #dump($user->getTable());
		 //---------------------------------------
		 

$currentPage = max(1, (int) $request->query->get('page', 1));

#dump($page);
$limit = 1;

/*
$list = $this->customDao->selectModel(
    ['status' => 1],
    '*',
    $currentPage,
    $limit,
);
*/



// App\Models\Custom 
#dump($this->customDao->getModel());//得到 App\Models\Custom 模型

/*
 Framework\ORM\Factories\LaravelORMFactory {#949 ▼
  -modelClass: "App\Models\Custom"
  -modelInstance: 
App\Models
\
Custom
 {#964 ▶}
}
*/
/*
dump($this->customDao->getAdapter()->selectModel(
    ['status' => 1],
    '*',
    $currentPage,
    $limit,
)->paginate(3, ['*'], 'page', 1)->toArray());
*/
//dump($this->customDao);		// 几乎等于$this->customDao->getAdapter();
//dump($this->customDao->getAdapter());

//dump( ($this->db)( Custom::class)->getFields() );

//dump($user->getFields_1());
//->toArray(); TP 
// ->get()->toArray();
//->paginate(3, ['*'], 'page', 1)->toArray(); //Laravel

//dump ($this->customDao->get(['status' => 1])->toArray());

#dump($this->UserService);

#$model = new Custom();
#echo $model->getTableName(); // 应该输出 oa_custom

		//dump(config('cache.stores.redis.host'));
		/*
		$cacheFile = BASE_PATH . '/storage/test.php';
		$cache = new \Framework\Config\Cache\ConfigCache($cacheFile, 300); // TTL 300s
		$config = new \Framework\Config\ConfigService( BASE_PATH . '/config', $cache, null , ['routes.php', 'services.php']);

		//dump($all = $config->load());
		
		*/
		//dump(app('config')->get('database'));
		// echo storage_path('logs/sql.log');
		
		
		
$snow = new Snowflake(2, 3);

$id = $snow->nextId();


	/*	
    // 查询构造器
    $count = $this->db->make('config')->count();

    // 模型
    $configModel = $this->db->make(\App\Models\Config::class);

    $user2 = $configModel->find(1);

    // 或如果你的 __invoke 支持
    $user3 = ($this->db)(\App\Models\Config::class)->find(1);		
	
    $user4 =($this->db)('App\Models\Config')->find(1);

	#dump($user4);
	*/
    
        // 日志测试
        // $logger = app('log');
        // $logger->info('Homepage visited--------------------');


        # thinkcache测试
        //$cache = app('cache');
        //$cache->set('test1', ['name' => 'mike'], 3600);
        //$test1 =$cache->get('test1');
        //$test1 = $cache->clear();
        
		
		#$loggerService = getService(\Framework\Log\LoggerService::class);
		
		#$loggerService->info('App----------------------------------------------------');
		
		//caches('foo-----', 'bar', 120);  // set
		

		#$factory = new \Framework\Cache\ThinkCache(require BASE_PATH . '/config/cache.php');
		#$redisCache = $factory->create(); // ✅ 成功
		#$redisCache ->set('foo111', 'bar', 120);

		//$ca = app(\Framework\Cache\ThinkCache::class)->create('redis');
		//$ca->set('xxxx', 'bar', 120);
		
		#$logger1 = app('log_cache');

		#$logger1->log('默认日志文件');
		
		
		// 容器注册快速测试
		// 使用自定义参数 app(\Framework\Log\LoggerCache::class) 或app('\Framework\Log\LoggerCache') 带引号做为字符串参数
		/*
		$logger2 = App::getContainer()->make(\Framework\Utils\LoggerCache::class, [
			'channel' => 'payment',
			'logFile' => BASE_PATH .'/storage/logs/payment.log',
		]);
		$logger2->log('支付日志');
		*/
	

        // Symfony缓存
        // cache_set('user_1', ['name' => 'AliceA'], 3600);
        // $user = cache_get('user_1');
		
		// Thinkphp 缓存
        // caches('foo', 'bar'); 

        // $post = ['name' => 'Alice'];
        // cache_set('post_1', $post, 3600, ['posts', 'user_123']);
        // cache_set('post_2', $post, 3600, ['posts', 'category_news']);
        // 删除所有 posts 相关缓存
        // cache_invalidate_tags(['posts']);
        // cache_invalidate_tags(['user_123']);
		//print_r(config('storage.local'));


        // session测试
        $session = app('session');
        // 设置一个 session 属性
        $session->set('user_id', 'tom_11');
        // 获取一个 session 属性
        $userId = $session->get('user_id');
        
		
		#dump(app('session')->all());


        // 在返回响应之前，收集信息
        $includedFiles = get_included_files();
        $loadedClasses = get_declared_classes();

        // 你可以选择将信息追加到响应内容中
        $debugInfo = sprintf(
            '<hr><pre>'
            . 'Included files: %d' . PHP_EOL
            . 'Loaded classes: %d' . PHP_EOL
            . '</pre>',
            count($includedFiles),
            count($loadedClasses)
        );


		##
		//Thinkphp验证##
		$data = [
			'name'  => '222',
			'age'   => 5520,
			'email' => 'thinkphp@qq.com',
		];

		$validate = new \App\Validate\NewUser;
		$result = $validate->check($data);

		if(!$result){
		#echo $validate->getError();
		}
        return new Response("<h1>Welcome to My Framework!__{$userId}</h1>");
    }



    // 基础游标分页（默认按 id 降序排序）
    public function config(Request $request)
    {
		//dump($request->query->all());
		$cursor = $request->query->get('cursor');
        // 1. 最简单的用法：默认每页 15 条，按 id 降序
        $users = Custom::toBase()->pluck('name' , 'id');
		//$users1 = Custom::all()->pluck('id');
        /*
		 array:4 [▼
		  1 => "萨尔阿萨德"
		  2 => "徐州有限公司"
		  3 => "T三国江东集团"
		  4 => "sae"
		]
		*/
        // 2. 自定义每页条数（例如每页 10 条）
        //$users = Custom::cursorPaginate(2)->toArray();
        
        // 3. 带查询条件的游标分页（筛选 + 分页）
        // $users = Custom::where('status', 1) // 只查状态为1的用户
        //             ->where('age', '>', 18) // 年龄大于18
        //             ->cursorPaginate(10);
        
        // 4. 自定义排序字段（必须包含唯一标识，避免重复/遗漏）
        // 注意：排序字段需与游标分页兼容，推荐用「主键 + 其他字段」
        // $users = Custom::orderBy('created_at', 'desc') // 先按创建时间降序
        //             ->orderBy('id', 'desc') // 再按 id 降序（唯一标识，确保排序唯一性）
        //             ->cursorPaginate(10);
		
		//public function cursorPaginate($perPage = 15, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
		//columns用法：->cursorPaginate(10, ['id', 'title', 'create_time'])。
		$paginator = Custom::query() //->orderBy('create_time', 'desc') 
            // 2. 必须追加 id 排序（唯一标识，避免多条记录排序字段相同）
            ->orderBy('id', 'ASC') 
            // 3. 保持查询条件一致（如果有筛选条件，确保每次请求都包含）
            ->where('status', 1) // 示例：如果有筛选条件，必须保留
			->cursorPaginate(1, ['*'], 'cursor', $cursor);;//->toArray();
		$paginator->withQueryString();
		$paginator->withPath('/home/config');


		// 如果你需要数组格式
		$query = $paginator->toArray();
/*
$query1 = Schema::table('custom', fn(Blueprint $t) =>
    $t->index(['address','email'])
)->get();
*/	
// 输出链接时，直接使用生成的 URL，不要再手动拼接 "/home/config"
//echo '<a href="'.$query['prev_page_url'].'">Prev link</a>&nbsp;&nbsp;&nbsp;';
//echo '<a href="'.$query['next_page_url'].'">next link</a>&nbsp;&nbsp;&nbsp;';

#echo '<a href="'.$query['hasMorePages'].'">More</a>';

		//dump($query);
		
		/*
        // 返回分页数据（包含游标链接，前端可直接使用）
        return new Response(
			json_encode([
				'data' => $query->items(), // 当前页数据
				'pagination' => [
					'next_page_url' => $query->nextPageUrl(), // 下一页链接（带游标参数）
					'has_more' => $query->hasMorePages() // 是否还有更多数据
				]
			]) 
		);
		*/
    }













    // http://localhost:8000/home/xss?name=mike<script>alert('ok');</script>
    public function xss(Request $request): Response
    {
        // 如果是 JSON 请求，使用过滤后的数据
        //$data = \Framework\Middleware\XssFilterMiddleware::getFilteredJsonBody($request);

        // if ($data === null) {
        // 可能是表单提交，用 $request->request->all()
        //	$data = $request->request->all();
        // }

        $name = $request->get('name');

        // $data 中的字符串已自动 XSS 过滤
        // $name = $data['name'] ?? '';
        // 直接输出是安全的（无需再 htmlspecialchars）

        return new Response("Hello, {$name}");
    }

    public function show1(Request $request): Response
    {
		$session = app('session');
		$userid = $session->get('user_id');
		
		//dump($userid);
		
		$CaptchaImage =\Framework\Utils\Captcha::base64();
		
		$base64 = $CaptchaImage['base64'];
		$key = $CaptchaImage['key'];
			
        $token = $this->csrf->getToken('default');
        // 传递给模板
        return new Response("<form method='POST' action='/home/checkCaptcha'>
            <input type='hidden' name='_token' value='{$token}'>
			用户：<input type='text' name='name' value='chen'><br/>
			年龄：<input type='text' name='age' value='333'><br/>
			生日：<input type='text' name='birthday' value='1898-12-11'><br/>
			邮箱：<input type='text' name='email' value='aa@admn.com'><br/>
			验证码：<input type='text' name='code'>
			<input type='text' name='userid' value={$userid}>
			<img src='{$base64}'>
            <input name='key' value='{$key}'>
            <button type='submit'>Submit</button>
        </form>");
    }

	
	
	#[Validate(validator: UserValidate::class, scene: 'create')] 
    public function checkCaptcha(Request $request): Response
    {
		
        $data =  array_merge($request->query->all(), $request->request->all());  //$request->toArray();
        #dump($data);
        // save to db...
        
        return new JsonResponse(['msg' => 'User created', 'data' => $data]);
		
		
		/*
        $config    = require __DIR__ . '/../../config/captcha.php';
        $captcha   = new CCaptcha($config);
        $userInput = $request->request->get('code');
		
        if (!$captcha->validate($userInput)) {
            return new Response('验证码错误'.$userInput);
        }
        return new Response('验证码正确'.$userInput, 200);
		*/

		$code = $request->request->get('code');
		$key = $request->request->get('key');
		if (false === \Framework\Utils\Captcha::check($code, $key)) {
			// 验证失败
			return new Response('验证码错误'.$code);
		};
		return new Response('验证码正确'.$code, 200);
    }


    // CSRF token测试。
    public function getForm(Request $request)
    {
        // 1.【可选】兜底验证（如果中间件可能失效）
        $token = $request->request->get('_token');
        if (! $this->csrf->isTokenValid('default', $token)) {
            // return new Response('Invalid CSRF token.', 503);
        }

        $title = $request->request->get('title');
        return new Response("Hello, {$title}");
        // 2. 重定向到成功页面（✅ 正确）
        // return new RedirectResponse('/home/successPage');
    }

    public function successPage(Request $request)
    {
		
        $cache = app('cache');
       # $cache->set('test1', ['name' => 'mike'], 3600);
        $test1 =$cache->get('test1');
        //$test1 = $cache->clear();
        print_r($test1);
		
        return new Response('<h1>提交成功！</h1>');
    }

    public function getsession(Request $request): Response
    {
        //$session = $request->getSession(); // Symfony 自动绑定 session 到 Request
		$session = $this->requestStack->getSession();
        $session->set('test', 'hello');
        return new Response($session->get('test'));
    }

    public function uploadform(): Response
    {   // echo BASE_PATH;
        // $token = $this->csrf->getToken('default');
        $html = view('upload/index');
        return new Response($html);
    }

    public function upload(Request $request)
    {
        return;
        // 下面是文件上传的测试
        // 获取普通字段
        $title = $request->request->get('title'); // request->get() 也可以，但明确用 ->request 更清晰

        // 获取上传的文件（UploadedFile 对象）
        $uploadedFile = $request->files->get('image');

        if ($uploadedFile && $uploadedFile->isValid()) {
            // 文件基本信息
            $originalName = $uploadedFile->getClientOriginalName();
            $extension    = $uploadedFile->getClientOriginalExtension();
            $mimeType     = $uploadedFile->getMimeType();
            $size         = $uploadedFile->getSize(); // 字节

            // 保存文件（例如保存到 public/uploads/）
            $newFilename = uniqid() . '.' . $extension;
            $uploadedFile->move(
                BASE_PATH . '/public/uploads',
                $newFilename
            );

            // 你可以返回 JSON、重定向或渲染模板
            return json_encode([
                'title' => $title,
                'file'  => [
                    'original_name' => $originalName,
                    'saved_as'      => $newFilename,
                    'mime_type'     => $mimeType,
                    'size'          => $size,
                ],
            ]);
        }

        // 文件无效或未上传
        return json_encode(['error' => '文件上传失败'], 400);
    }

    // 列举自己需要的参数
    public function show(Request $request , int $id):Response
    {
        // 获取所有用户 => 返回数组数据或 json 响应
        //$users = Admin::select()->toArray();
        //print_r($users); // 因为你框架会处理 array => json

        $id = $request->get('id');
        return new Response("<h1>User ID: $id</h1>");
    }

    // 只获取需要的参数
    public function search1(Request $request, $roleid, $name, $status) {}

    // 或者只获取Request对象
    public function search2(Request $request):Response
    {
        $roleid = $request->get('roleid');
        $name   = $request->get('name');
        $status = $request->get('status');
        // ...
		
		return new Response("<h1>User ID: $name</h1>");
    }

    // 混合使用
    public function search3(Request $request, $id)
    {
        // 从路由获取id，从请求中获取其他参数
        $name = $request->get('name');
        // ...
    }
}
