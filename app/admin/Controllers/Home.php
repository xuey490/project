<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Admin\Controllers;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;

class Home 
{

	###[Route(path: '/xxapi/admin/home', methods: ['GET'], name: 'admin.index')]
	public function index():Response
	{

		return new Response(
			'<html><body><h1>Hello, World--App\admin\Controllers! api/admin/home</h1></body></html>',
			Response::HTTP_OK, // Code（200）
			['Content-Type' => 'text/html; charset=UTF-8']
		);	
	}
	
	/*
	http://127.0.0.1:8000/xxx/home/list
	*/
	public function list():Response
	{

		return new Response(
			'<html><body><h1>list, World--App\admin\Controllers! api/admin/list</h1></body></html>',
			Response::HTTP_OK, // Code（200）
			['Content-Type' => 'text/html; charset=UTF-8']
		);	
	}
}