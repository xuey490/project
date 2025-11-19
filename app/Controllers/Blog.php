<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame.
 *
 */

namespace App\Controllers;

use App\Services\BlogService;
use App\Twig\AppTwigExtension;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use App\Models\Admin;
#use Framework\Utils\ThinkORMFactory;
 // 假设你有文章服务
use Framework\Factory\ThinkORMFactory;

class Blog
{
    private Environment $twig;

    private BlogService $blogService;
	
    private ThinkORMFactory $db;

    public function __construct(Environment $twig, BlogService $blogService , ThinkORMFactory $db)
    {
        $this->twig        = $twig;
        $this->blogService = $blogService;
        $this->db = $db;
    }
	
	public function list(Request $request)
	{
		$total = 150;
		
		$pagination = new \Framework\Utils\Pagination($total, $request, 10);
		
		$pageData =$pagination->getData(2);
		

		
		return $this->twig->render('blog/list.html.twig', [
			'pagination' => $pageData,
		]);
		
	}
	
	
	public function auth(Request $request): Response
	{
		return new Response('aaa');
	}
	
	
	

    public function index(): Response
    {
		
        #$users = ($this->db)(Admin::class)::select()->toArray();	//这三种方法都可以运行
        #$users = $this->db->make(Admin::class)::select()->toArray();//这三种方法都可以运行
        $users = Admin::select()->toArray();
        dump($users); // 因为你框架会处理 array => json
		
		
        // 🔍 检查当前 Twig 实例是否加载了 AppTwigExtension
        $extensions   = app('view')->getExtensions();
        $hasExtension = false;
        foreach ($extensions as $ext) {
            if ($ext instanceof AppTwigExtension) {
                $hasExtension = true;
                // print_r($ext);
                break;
            }
        }
        if (! $hasExtension) {
            // print_r("❌ AppTwigExtension NOT registered in Twig instance!");
        }
        // print_r("✅ AppTwigExtension IS registered!");

        $mdContent = $this->twig->render('makedown.html.twig', ['title' => 'Hello']);

        // 动态获取文章数据
        $posts = $this->blogService->getList();
        // 热门文章数据
        $popularPosts = $this->blogService->getpopularPosts();

        $html = $this->twig->render('blog/index.html.twig', [
            'posts'        => $posts,
            'current_user' => app('session')->get('user'),
            'page_title'   => '最新文章',
            'popularPosts' => $popularPosts,
            'mdContent'    => $mdContent,
        ]);
            $response = app('response');
			$response->headers->set('Authorization1', 'Bearer 123');

        return new Response($html);
    }
}
