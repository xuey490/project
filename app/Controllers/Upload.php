<?php

//declare(strict_types=1);

/**
 * This file is part of NovaFrame.
 *
 */

namespace App\Controllers;

use Framework\Utils\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class Upload
{
    private Environment $twig;

    private FileUploader $uploader;

    public function __construct(Environment $twig, FileUploader $uploader)
    {
        $this->twig     = $twig;
        //$this->uploader = $uploader;
    }

    public function form()
    {
        #dump(config('storage'));
        $html = $this->twig->render('upload/form.html.twig');
        return new Response($html);
    }

    /**
     * 表单上传（multipart/form-data）
     */
	public function process(Request $request):Response
	{

		// 传入当前 Request
		// 指定adapter 为local
		#$storage = \Framework\Storage\Storage::disk('local', true, $request);
		#$res = $storage->uploadFile();
		#var_dump(json_encode($res));
		
		//默认模式
		$res = \Framework\Storage\Storage::uploadFile();
		var_dump(json_encode($res));
		
		return new Response('');

    }

    /**
     * Base64 上传
     */
    public function base64Upload(Request $request)
    {
        $base64 = $request->request->get('image');

        $disk = \Framework\Storage\Storage::disk('local', false); // 非表单上传
        return $disk->uploadBase64($base64, 'png');
    }

    /**
     * 上传服务器文件（例如你已有路径）
     */
    public function uploadFromPath(Request $request)
    {
        $path = 'C:\Users\Administrator\Desktop\ArtPlayer-master\images\mobile.png';

        $disk = \Framework\Storage\Storage::disk('local', false);
        return $disk->uploadServerFile($path);
    }

	public function process2(Request $request)
	{

        try {
            $result = $this->uploader->upload($request , 'file');
            return json_encode(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

    }
	
	public function testme()
	{
		return new Response('TESTME OK');
	}
	
}
