<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *#
 */

namespace App\Controllers;

use App\Services\UserService;
use Framework\Container\Container;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse; // 或者你需要的其他依赖
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class Admin
{
    private UserService $userService;

    // 构造函数依赖注入
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        // var_dump($this->userService->getUserById(1));
        // var_dump("UserController 构造函数被调用，PDO 依赖已注入！");
    }

    // 路由：/admin/get/1
    public function get(int $id): Response
    {
        // 使用注入的 UserService 获取数据
        $user = $this->userService->getUserById($id);

        if (empty($user)) {
            return new Response('<h1>User Not Found</h1>', 404);
        }

        return new Response("ID: {$user['id']}, Name: {$user['name']}");
    }

    /**
     * 1. 获取用户列表（GET /admin）
     * 支持分页：/admin?page=1&size=10.
     */
    public function index(Request $request): Response
    {
        // 获取容器
        $container = Container::getInstance();
        // 获取DI服务
        // $test = $container->get('test.service');
        

        // // 获取控制器（自动创建）
        // $homeController = $container->get('App\Controllers\HomeController');
        // $homeController->index();

        // 1. 获取请求参数（分页）
        $page = (int) $request->get('page', 1); // 默认第1页
        $size = (int) $request->get('size', 10); // 默认每页10条
        $page = $page < 1 ? 1 : $page; // 防止页数小于1
        $size = $size < 1 || $size > 50 ? 10 : $size; // 限制每页最大50条

        // 2. 模拟数据库查询（实际项目替换为ORM查询，如Eloquent）
        $total = 100; // 总用户数（模拟）
        $users = $this->mockUserList($page, $size); // 模拟用户列表数据

        // 3. 计算分页信息
        $totalPages = (int) ceil($total / $size);
        $pagination = [
            'page'       => $page,
            'size'       => $size,
            'total'      => $total,
            'totalPages' => $totalPages,
        ];

        // 4. 返回响应（支持HTML/JSON两种格式，通过format参数控制）
        $format = $request->get('format', 'html');
        if ($format === 'json') {
            // API场景：返回JSON格式
            return new JsonResponse([
                'code'    => 200,
                'message' => 'success',
                'data'    => [
                    'users'      => $users,
                    'pagination' => $pagination,
                ],
            ]);
        }

        // 前端页面场景：返回HTML（实际项目可替换为模板渲染）
        $html = '<h1>User List (Page ' . $page . '/' . $totalPages . ')</h1>';
        $html .= '<table border="1" cellpadding="8" cellspacing="0">';
        $html .= '<tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>';
        foreach ($users as $user) {
            $html .= '<tr>';
            $html .= '<td>' . $user['id'] . '</td>';
            $html .= '<td>' . $user['name'] . '</td>';
            $html .= '<td>' . $user['email'] . '</td>';
            $html .= '<td>';
            $html .= '<a href="/admin/show/?id=' . $user['id'] . '">View</a> | ';
            $html .= '<a href="/admin/edit/?id=' . $user['id'] . '">Edit</a> | ';
            $html .= '<a href="javascript:deleteUser(' . $user['id'] . ')">Delete</a>';
            $html .= '</td></tr>';
        }
        $html .= '</table>';
        $html .= '<br><a href="/admin/create">Add New User</a>';

        // 删除用户的JS逻辑（模拟PUT/DELETE请求，实际项目用表单或Axios）
        $html .= '<script>';
        $html .= 'function deleteUser(id) {';
        $html .= 'if(confirm("Are you sure to delete user " + id + "?")) {';
        $html .= 'fetch("/admin/" + id + "", {method: "DELETE"})';
        $html .= '.then(res => res.json())';
        $html .= '.then(data => {';
        $html .= 'if(data.code === 200) alert("Delete success!");';
        $html .= 'else alert("Delete failed: " + data.message);';
        $html .= 'window.location.reload();';
        $html .= '});}}';
        $html .= '</script>';

        return new Response($html);
    }

	/**2. 获取单个用户详情（GET /user/{id}）.
	 * @ParamConverter("id", options={"mapping": {"id": "id"}})
	 */
    public function show(int $id, Request $request): Response
    {
        // 1. 模拟数据库查询（实际项目替换为ORM查询）
        //$user = $this->mockUser(intval($id));
        $user = $this->mockUser($id);
        if (! $user) {
            // 用户不存在：返回404
            return new JsonResponse([
                'code'    => 404,
                'message' => 'User not found (ID: ' . $id . ')',
            ], 404);
        }

        // 2. 返回响应（支持HTML/JSON）
        $format = $request->get('format', 'html');
        if ($format === 'json') {
            return new JsonResponse([
                'code'    => 200,
                'message' => 'success',
                'data'    => $user,
            ]);
        }

        // 前端页面：显示用户详情
        $html = '<h1>User Detail (ID: ' . $user['id'] . ')</h1>';
        $html .= '<ul>';
        $html .= '<li><strong>ID:</strong> ' . $user['id'] . '</li>';
        $html .= '<li><strong>Name:</strong> ' . $user['name'] . '</li>';
        $html .= '<li><strong>Email:</strong> ' . $user['email'] . '</li>';
        $html .= '<li><strong>Create Time:</strong> ' . $user['created_at'] . '</li>';
        $html .= '</ul>';
        $html .= '<br>';
        $html .= '<a href="/admin/' . $user['id'] . '/edit">Edit</a> | ';
        $html .= '<a href="/admin">Back to List</a>';

        return new Response($html);
    }

    /**
     * 3. 显示创建用户表单（GET /user/create）.
     */
    public function create(): Response
    {
        // 创建用户的表单页面
        $html = '<h1>Create New User</h1>';
        $html .= '<form method="POST" action="/admin">';
        $html .= '<div>';
        $html .= '<label>Name:</label>';
        $html .= '<input type="text" name="name" required placeholder="Enter username">';
        $html .= '</div><br>';
        $html .= '<div>';
        $html .= '<label>Email:</label>';
        $html .= '<input type="email" name="email" required placeholder="Enter email">';
        $html .= '</div><br>';
        $html .= '<div>';
        $html .= '<label>Password:</label>';
        $html .= '<input type="password" name="password" required placeholder="Enter password">';

        $html .= '</div><br>';
        $html .= '<button type="submit">Create User</button>';
        $html .= '<a href="/admin" style="margin-left:10px;">Cancel</a>';
        $html .= '</form>';

        return new Response($html);
    }

    /**
     * 4. 提交创建用户数据（POST /user）.
     */
    public function store(Request $request): Response
    {
        // 1. 获取并验证请求参数
        $name     = trim($request->request->get('name', ''));
        $email    = trim($request->request->get('email', ''));
        $password = $request->request->get('password', '');

        // 参数验证（实际项目可使用验证组件，如symfony/validator）
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        // 验证失败：返回错误信息
        if (! empty($errors)) {
            if ($request->isXmlHttpRequest()) { // AJAX请求返回JSON
                return new JsonResponse([
                    'code'    => 400,
                    'message' => 'Validation failed',
                    'errors'  => $errors,
                ], 400);
            }
            // 普通表单请求：返回带错误的表单页面
            $html = '<h1>Create New User (Error)</h1>';
            $html .= '<div style="color:red;">' . implode('<br>', $errors) . '</div><br>';
            $html .= '<form method="POST" action="/admin">';
            $html .= '<div><label>Name:</label><input type="text" name="name" value="' . $name . '" required></div><br>';
            $html .= '<div><label>Email:</label><input type="email" name="email" value="' . $email . '" required></div><br>';
            $html .= '<div><label>Password:</label><input type="password" name="password" required></div><br>';
            $html .= '<button type="submit">Create User</button> | ';
            $html .= '<a href="/admin">Cancel</a>';
            $html .= '</form>';
            return new Response($html);
        }

        // 2. 模拟数据库插入（实际项目替换为ORM保存）
        $newUserId = rand(100, 999); // 模拟生成新用户ID
        $this->mockSaveUser($newUserId, $name, $email, $password);

        // 3. 响应结果（重定向或JSON）
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'code'    => 201,
                'message' => 'User created successfully',
                'data'    => ['id' => $newUserId, 'name' => $name, 'email' => $email],
            ], 201);
        }

        // 普通表单：重定向到用户详情页（避免表单重复提交）
        return new Response('', 302, [
            'Location' => '/user/' . $newUserId . '',
        ]);
    }

    /**
     * 5. 显示编辑用户表单（GET /user/{id}/edit）.
     */
    public function edit(Request $request, int $id): Response
    {
        // 1. 模拟查询用户数据
        $id   =$request->get('id');
        $user = $this->mockUser($id);
        if (! $user) {
            return new JsonResponse([
                'code'    => 404,
                'message' => 'User not found (ID: ' . $id . ')',
            ], 404);
        }

        // 2. 编辑表单页面（PUT请求通过隐藏字段模拟，浏览器默认不支持PUT）
        $html = '<h1>Edit User (ID: ' . $id . ')</h1>';
        $html .= '<form method="POST" action="/admin/update?id=' . $id . '">';
        // 隐藏字段：标记为PUT请求（后续需在框架中处理PUT/DELETE请求）
        $html .= '<input type="hidden" name="_method" value="PUT">';
        $html .= '<div>';
        $html .= '<label>Name:</label>';
        $html .= '<input type="text" name="name" value="' . $user['name'] . '" required>';
        $html .= '</div><br>';
        $html .= '<div>';
        $html .= '<label>Email:</label>';
        $html .= '<input type="email" name="email" value="' . $user['email'] . '" required>';
        $html .= '</div><br>';
        $html .= '<div>';
        $html .= '<label>New Password (optional):</label>';
        $html .= '<input type="password" name="password" placeholder="Leave empty to keep current">';

        $html .= '</div><br>';
        $html .= '<button type="submit">Update User</button>';
        $html .= '<a href="/admin/' . $id . '" style="margin-left:10px;">Cancel</a>';
        $html .= '</form>';

        return new Response($html);
    }

    /**
     * 6. 提交更新用户数据（PUT /user/{id}）.
     */
    public function update(int $id, Request $request): Response
    {
        // 1. 检查用户是否存在
        $user = $this->mockUser($id);
        if (! $user) {
            return new JsonResponse([
                'code'    => 404,
                'message' => 'User not found (ID: ' . $id . ')',
            ], 404);
        }

        // 2. 获取并验证参数
        $name     = trim($request->request->get('name', ''));
        $email    = trim($request->request->get('email', ''));
        $password = $request->request->get('password', ''); // 可选：为空则不更新密码

        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (! empty($errors)) {
            return new JsonResponse([
                'code'    => 400,
                'message' => 'Validation failed',
                'errors'  => $errors,
            ], 400);
        }

        // 3. 模拟数据库更新
        $this->mockUpdateUser($id, $name, $email, $password);

        // 4. 响应结果
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'code'    => 200,
                'message' => 'User updated successfully',
                'data'    => ['id' => $id, 'name' => $name, 'email' => $email],
            ]);
        }

        // 重定向到用户详情页
        return new Response('', 302, [
            'Location' => '/admin/edit?id=' . $id . '',
        ]);
    }

    /**
     * 7. 删除用户（DELETE /admin/{id}）.
     */
    public function destroy(int $id): Response
    {
        // 1. 检查用户是否存在
        $user = $this->mockUser($id);
        if (! $user) {
            return new JsonResponse([
                'code'    => 404,
                'message' => 'User not found (ID: ' . $id . ')',
            ], 404);
        }

        // 2. 模拟数据库删除
        $this->mockDeleteUser($id);

        // 3. 响应结果（JSON，删除后通常不返回HTML）
        return new JsonResponse([
            'code'    => 200,
            'message' => 'User deleted successfully',
            'data'    => ['id' => $id],
        ]);
    }

    // ------------------------------
    // 以下为模拟数据方法（实际项目替换为ORM）
    // ------------------------------
    /**
     * 模拟用户列表数据.
     */
    // ... (前面的 index, show, create, store, edit, update, destroy 方法)

    // ------------------------------
    // 以下为模拟数据方法（实际项目替换为ORM）
    // ------------------------------

    /**
     * 模拟获取单个用户数据.
     */
    private function mockUser(int $id): ?array
    {
        // 仅模拟少量数据用于测试
        $mockUsers = [
            1 => [
                'id'         => 1,
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'created_at' => '2023-01-01 10:00:00',
            ],
            2 => [
                'id'         => 2,
                'name'       => 'Normal User',
                'email'      => 'user@example.com',
                'password'   => password_hash('user123', PASSWORD_DEFAULT),
                'created_at' => '2023-02-15 15:30:00',
            ],
            3 => [
                'id'         => 3,
                'name'       => 'Test User',
                'email'      => 'test@example.com',
                'password'   => password_hash('test123', PASSWORD_DEFAULT),
                'created_at' => '2023-03-20 09:45:00',
            ],
        ];

        return $mockUsers[$id] ?? null;
    }

    /**
     * 模拟用户列表数据.
     */
    private function mockUserList(int $page, int $size): array
    {
        $users = [];
        $start = ($page - 1) * $size + 1;
        $end   = $start              + $size - 1;

        // 生成连续的用户数据用于列表展示
        for ($i = $start; $i <= $end; ++$i) {
            $users[] = [
                'id'         => $i,
                'name'       => 'User_' . $i,
                'email'      => 'user_' . $i . '@example.com',
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            ];
        }
        return $users;
    }

    /**
     * 模拟保存新用户.
     */
    private function mockSaveUser(int $id, string $name, string $email, string $password): void
    {
        // 实际项目中，这里会是 $user->save() 之类的ORM操作
        // 模拟数据库写入成功
        // echo "User {$name} (ID: {$id}) saved to database.\n";
    }

    /**
     * 模拟更新用户.
     */
    private function mockUpdateUser(int $id, string $name, string $email, string $password = ''): void
    {
        // 实际项目中，这里会是 $user->update(...) 之类的ORM操作
        // 模拟数据库更新成功
        // echo "User {$id} updated.\n";
    }

    /**
     * 模拟删除用户.
     */
    private function mockDeleteUser(int $id): void
    {
        // 实际项目中，这里会是 $user->delete() 之类的ORM操作
        // 模拟数据库删除成功
        // echo "User {$id} deleted.\n";
    }
} // class UserController 结束
