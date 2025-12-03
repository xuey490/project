<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use Framework\Basic\BaseService;
use Framework\Basic\Crud;
use Framework\Utils\Json;

class Member extends Crud
{
    public function __construct(
        UserService $service
    ) {
        parent::__construct($service);
    }

    public function get(Request $request): Response
    {
		$this->setRequest($request);
		
		#dump($request->query->all());
        #$query = $this->request->query->get('q', ''); //千万不要这样写!!!
		$query1 =$request->query->get('q', ''); //可以这样写
		$query2 = $this->getParam('q', ''); //也可以这样
		$query3 = $request->get('q', ''); //也可以这样
        $results = $this->service->getUsers(1);
		
		return new Response($query3);
        //return Json::success('ok'.$query, $results);
    }
}