<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;

class Index 
{

	#[Route(path: '/api/index', methods: ['GET'], name: 'Index.index', group: 'Admins')]
	
	public function index():Response
	{

		return new Response(
			'<html><body><h1>Hello, World!!--Index-a</h1></body></html>',
			Response::HTTP_OK, // Code（200）
			['Content-Type' => 'text/html; charset=UTF-8']
		);	
	}
	

}
