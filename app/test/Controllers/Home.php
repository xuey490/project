<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Test\Controllers;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;

class Home 
{

	#[Route(path: '/api/home', methods: ['GET'], name: 'test.index')]
	public function index():Response
	{

		return new Response(
			'<html><body><h1>Hello, World--App\test\Controllers!</h1></body></html>',
			Response::HTTP_OK, // Code（200）
			['Content-Type' => 'text/html; charset=UTF-8']
		);	
	}
	

}