<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\View\ViewRender; // ✅ 正确命名空间

class Think
{
    use ViewRender;

    public $siteName = 'My Awesome Site';

    public function index()
    {
		$siteName = $this->siteName;
		
		$user = ['name' => 'Alice', 'age' => 28];
		
        $this->title('首页')
             ->keywords('首页,欢迎,主页')
             ->description('这是网站首页，欢迎访问！')
             ->layout('layout/main')
			 ->section('header', '<link rel="stylesheet" href="/css/profile.css">')
			 
			 ->section('scripts', '<script src="/js/profile.js"></script>')
             ->section('sidebar', $this->renderPartial('think/sidebar', [
                 'active' => 'profile'
             ]))
             ->section('show', $this->renderPartial('think/show', [
                 'active' => 'profile',
				 'user'	=> $user,
             ]))
			 
			->section('footer', '<p>自定义页脚</p>'); // 使用布局

        $news = [['id'=>1, 'title'=>'新闻1'], ['id'=>2, 'title'=>'新闻2']];
        $slider = ['img1.jpg', 'img2.jpg'];

        return $this->render('think/index', compact('news', 'slider' , 'siteName'));
    }

    public function show()
    {
		

        // 主数据
        $user = ['name' => 'Alice', 'age' => 28];
		
		
        // 设置 SEO
        $this->title('用户资料')
             ->keywords('用户,资料,个人中心')
             ->description('查看用户详细信息');

        // 设置布局
        $this->layout('layout/main');

        // 定义区块
        $this->section('header', '<link rel="stylesheet" href="/css/profile.css">')
		
             ->section('scripts', '<script src="/js/profile.js"></script>')
			 
             ->section('sidebar', $this->renderPartial('think/sidebar', [
                 'active' => 'profile',
				 'user'	=> $user,
             ]))
			 
             ->section('show', $this->renderPartial('think/show', [
                 'active' => 'profile',
				 'user'	=> $user,
             ]))
			->section('footer', '<p>自定义页脚</p>');


        return $this->render('think/user', compact('user'));
    }
/*
建议在控制器中使用 snake_case 命名，并保持模板一致：
{$__SECTION_page_header__|raw}
{$__SECTION_page_footer__|raw}
{$__SECTION_page_scripts__|raw}
$this->section('page_header', '<link ...>')
     ->section('page_footer', '<div>...</div>')
     ->section('page_scripts', '<script>...');
*/

}
