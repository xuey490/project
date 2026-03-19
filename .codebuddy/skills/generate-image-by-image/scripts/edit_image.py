#!/usr/bin/env python3
"""
腾讯混元图生图 API 调用脚本

使用方法:
    python3 edit_image.py "编辑描述" --image-url "图片路径" [选项]

示例:
    # 基础使用
    python3 edit_image.py "把这只猫变成橘色" --image-url cat.jpg
    
    # 指定尺寸和质量
    python3 edit_image.py "添加夕阳背景" --image-url landscape.png --size 768x1280 --quality high
    
    # 添加水印和改写提示词
    python3 edit_image.py "增强色彩饱和度" --image-url photo.jpg --footnote "我的水印" --revise
    
    # 指定下载目录和文件名前缀
    python3 edit_image.py "转换为卡通风格" --image-url portrait.png --download-dir ./edited --download-prefix cartoon
    
    # 仅返回 URL,不下载图片
    python3 edit_image.py "去除背景" --image-url product.jpg --no-download
"""

import os
import sys
import json
import argparse
import requests
import random
import string
import base64
from typing import Optional, Dict, Any, List
from pathlib import Path
from urllib.parse import urlparse

def encode_image_to_base64(image_url: str) -> str:
    """
    将图片文件编码为 base64 字符串
    
    Args:
        image_url: 图片文件路径
        
    Returns:
        base64 编码的图片字符串
        
    Raises:
        FileNotFoundError: 当图片文件不存在时
        IOError: 当读取图片文件失败时
    """
    if not os.path.exists(image_url):
        raise FileNotFoundError(f"图片文件不存在: {image_url}")
    
    try:
        with open(image_url, 'rb') as image_file:
            image_data = image_file.read()
            base64_string = base64.b64encode(image_data).decode('utf-8')
            return base64_string
    except IOError as e:
        raise IOError(f"读取图片文件失败: {e}")


def download_image_from_url(image_url: str) -> str:
    """
    从URL下载图片并编码为 base64 字符串
    
    Args:
        image_url: 图片URL
        
    Returns:
        base64 编码的图片字符串
        
    Raises:
        requests.RequestException: 当下载失败时
    """
    try:
        response = requests.get(image_url, timeout=30)
        response.raise_for_status()
        image_data = response.content
        base64_string = base64.b64encode(image_data).decode('utf-8')
        return base64_string
    except requests.exceptions.RequestException as e:
        raise requests.RequestException(f"下载图片失败: {e}")

def edit_image(
    prompt: str,
    image_url: Optional[str] = None,
    image: Optional[str] = None,
    model: Optional[str] = None,
    background: Optional[str] = None,
    moderation: Optional[str] = None,
    input_moderation: Optional[str] = None,
    n: int = 1,
    seed: Optional[int] = None,
    output_compression: Optional[int] = None,
    output_format: Optional[str] = None,
    partial_images: Optional[int] = None,
    quality: Optional[str] = None,
    response_format: Optional[str] = None,
    size: str = "1024x1024",
    stream: bool = False,
    style: Optional[str] = None,
    user: Optional[str] = None,
    footnote: Optional[str] = None,
    revise: bool = False,
    strength: Optional[float] = None,
) -> Dict[str, Any]:
    """
    调用腾讯混元图生图 API 编辑图片
    
    Args:
        prompt: 编辑描述文本
        image_url: 输入图片文件路径或URL
        image: 输入图片 base64 字符串
        model: 使用的模型名称,如果不指定则根据环境自动选择
        background: 背景透明度设置 (transparent/opaque)
        moderation: 内容审核级别
        input_moderation: 是否开启输入内容审核,默认为 true
        n: 生成图片个数,默认为1,目前只支持生成一张,固定为1,传其他的值会报错
        seed: 生成种子,仅当生成图片数为1时生效,范围[1, 4294967295],不传时默认随机
        output_compression: 图片压缩级别 (0-100)
        output_format: 输出格式 (png/webp/jpeg)
        partial_images: 部分图片数量 (0-3)
        quality: 图片质量 (low/medium/high)
        response_format: 响应格式 (url/b64_json)
        size: 图片尺寸 (640x1408/704x1344/768x1280/832x1216/896x1152/960x1088/1024x1024/1088x960/1152x896/1216x832/1280x768/1344x704/1408x640)
        stream: 是否启用流式模式
        style: 图片风格
        user: 用户唯一标识符
        footnote: 自定义水印 (最多16个字符)
        revise: 是否改写prompt
        strength: 编辑强度 (0.0-1.0)
        
    Returns:
        API 响应的 JSON 数据
        
    Raises:
        ValueError: 当参数无效时
        requests.RequestException: 当 API 请求失败时
        FileNotFoundError: 当输入图片文件不存在时
        
    环境变量:
        X_IDE_AUTH_PROXY: 如果设置,将使用零配置 auth proxy 模式
        X_IDE_SPACE_REGION: 如果值为 ap-singapore,将使用 default-image-edit 模型
        COPILOT_API_KEY: 如果设置,将直接调用腾讯 API (需要有效的 API Key)
        注意: 必须至少设置 X_IDE_AUTH_PROXY 或 COPILOT_API_KEY 之一，否则会报错
    """
    # 确定默认模型
    if model is None:
        space_region = os.environ.get("X_IDE_SPACE_REGION", "")
        if space_region == "ap-singapore":
            model = "default-image-edit"
        else:
            model = "hunyuan-image-v2.0-general-edit"
    
    # 检查是否在 auth proxy 环境中
    use_auth_proxy = os.environ.get("X_IDE_AUTH_PROXY") is not None
    
    # 从环境变量获取 API Key (仅在非 auth proxy 模式下需要)
    api_key = os.environ.get("COPILOT_API_KEY", "")
    
    # 确定图片输入方式并获取 base64 编码
    image_base64 = None
    
    if image:
        # 直接使用提供的 base64 字符串
        image_base64 = image
    elif image_url:
        try:
            # 判断是远程 URL 还是本地文件路径
            parsed = urlparse(image_url)
            is_remote_url = parsed.scheme in ("http", "https")
            
            if is_remote_url:
                # 从远程 URL 下载图片
                image_base64 = download_image_from_url(image_url)
            elif os.path.exists(image_url):
                # 从本地文件路径读取
                image_base64 = encode_image_to_base64(image_url)
            else:
                # 既不是有效 URL，也不是存在的本地文件
                raise FileNotFoundError(f"图片文件不存在: {image_url}")
        except (FileNotFoundError, IOError) as e:
            raise e
    else:
        raise ValueError("必须提供 image_path、image_url 或 image 参数之一")
    
    # 构建请求 URL 和请求头
    if use_auth_proxy:
        # 使用 auth proxy 模式 (零配置模式)
        url = "http://auth.proxy/codebuddy/v2/images/edits"
        headers = {
            "Content-Type": "application/json",
        }
    elif api_key:
        # 使用直接 API 访问模式 (需要 API Key)
        url = "https://copilot.tencent.com/v2/images/edits"
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {api_key}"
        }
    else:
        # 既没有 auth proxy 环境变量，也没有 API key
        raise ValueError(
            "缺少必要的认证配置。请设置以下环境变量之一:\n"
            "  - X_IDE_AUTH_PROXY: 在 Cloud Studio 环境中自动设置\n"
            "  - COPILOT_API_KEY: 用于直接调用腾讯 API"
        )
    
    # 构建请求体
    payload: Dict[str, Any] = {
        "model": model,
        "image": [image_base64],
        "prompt": prompt,
        "n": n,
        "size": size,
    }
    
    # 添加可选参数
    if output_format:
        payload["output_format"] = output_format
    if partial_images:
        payload["partial_images"] = partial_images
    if quality:
        payload["quality"] = quality
    if response_format:
        payload["response_format"] = response_format
    if stream:
        payload["stream"] = stream
    if strength:
        payload["strength"] = strength
    if background:
        payload["background"] = background
    if moderation:
        payload["moderation"] = moderation
    if seed is not None:
        payload["seed"] = seed
    if input_moderation:
        payload["input_moderation"] = input_moderation
    if output_compression is not None:
        payload["output_compression"] = output_compression
    if style:
        payload["style"] = style
    if user:
        payload["user"] = user
    if footnote:
        if len(footnote) > 16:
            raise ValueError("footnote 参数不能超过 16 个字符")
        payload["footnote"] = footnote
    if revise:
        payload["revise"] = {"value": True}

    # print('payload: ', payload)

    # 保存为 JSON 格式
    with open('data.json', 'w', encoding='utf-8') as file:
        json.dump(payload, file, ensure_ascii=False, indent=2)

    print("数据已保存为 JSON 格式")

    # 发送请求
    try:
        response = requests.post(url, headers=headers, json=payload, timeout=120)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"API 请求失败: {e}", file=sys.stderr)
        if hasattr(e, 'response') and e.response is not None:
            print(f"响应内容: {e.response.text}", file=sys.stderr)
        raise


def download_images(
    image_urls: List[str],
    output_dir: Optional[str] = None,
    prefix: str = "edited"
) -> List[str]:
    """
    从 URL 下载图片到指定目录
    
    Args:
        image_urls: 图片 URL 列表
        output_dir: 输出目录路径,默认为项目的 frontend/public/ 目录
        prefix: 文件名前缀
        
    Returns:
        下载的图片本地路径列表
        
    Raises:
        requests.RequestException: 当下载失败时
    """
    # 确定输出目录
    if output_dir is None:
        # 查找项目根目录的 frontend/public/ 目录
        current_dir = Path.cwd()
        
        # 尝试多个可能的路径
        possible_paths = [
            current_dir / "frontend" / "public",
            current_dir.parent / "frontend" / "public",
            current_dir.parent.parent / "frontend" / "public",
        ]
        
        output_path = None
        for path in possible_paths:
            if path.exists():
                output_path = path
                break
        
        if output_path is None:
            # 如果找不到,默认创建在当前目录的 frontend/public
            output_path = current_dir / "frontend" / "public"
            output_path.mkdir(parents=True, exist_ok=True)
            print(f"创建输出目录: {output_path}", file=sys.stderr)
    else:
        output_path = Path(output_dir)
        output_path.mkdir(parents=True, exist_ok=True)
    
    downloaded_files = []
    
    # 生成6位随机字符串（字母+数字）
    random_suffix = ''.join(random.choices(string.ascii_lowercase + string.digits, k=6))
    
    for i, url in enumerate(image_urls, 1):
        try:
            # 获取文件扩展名
            parsed_url = urlparse(url)
            ext = Path(parsed_url.path).suffix or ".png"
            
            # 生成唯一文件名：prefix_randomstr_index.ext
            filename = f"{prefix}_{random_suffix}_{i}{ext}"
            filepath = output_path / filename
            
            # 下载图片
            print(f"正在下载图片 {i}/{len(image_urls)}: {url}", file=sys.stderr)
            response = requests.get(url, timeout=30, stream=True)
            response.raise_for_status()
            
            # 保存到文件
            with open(filepath, 'wb') as f:
                for chunk in response.iter_content(chunk_size=8192):
                    f.write(chunk)
            
            downloaded_files.append(str(filepath))
            print(f"已保存到: {filepath}", file=sys.stderr)
            
        except requests.exceptions.RequestException as e:
            print(f"下载图片 {i} 失败: {e}", file=sys.stderr)
            continue
    
    return downloaded_files


def main():
    parser = argparse.ArgumentParser(
        description="腾讯混元图生图 API 调用工具 - 支持一次生成多个编辑版本",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
            示例:
            # 基础使用 - 生成3个编辑版本
            %(prog)s "把这只猫变成橘色" --image-url cat.jpg
            
            # 不同编辑描述生成多个版本
            %(prog)s --image-url portrait.jpg --prompts "转换为油画风格" "增强色彩饱和度" "添加艺术滤镜"
            
            # 指定尺寸和质量生成多个版本
            %(prog)s "风景美化" --image-url landscape.png --count 5 --size 768x1280 --quality high
            
            # 不同强度生成多个版本
            %(prog)s "人像美化" --image-url selfie.jpg --strength 0.3 0.5 0.7
            
            # 组合使用 - 多个描述和强度
            %(prog)s --image-url photo.jpg --prompts "专业修图" "艺术化处理"
        """
    )
    
    # 输入图片参数 (三选一)
    image_group = parser.add_mutually_exclusive_group(required=True)
    image_group.add_argument("--image-url", help="输入图片URL或者本地图片路径")
    image_group.add_argument("--image", help="输入图片base64字符串")
    
    # 编辑描述参数
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("prompt", nargs='?', help="单个编辑描述文本")
    group.add_argument("--prompts", nargs='+', help="多个编辑描述文本")
    parser.add_argument("--model", help="模型名称 (默认: 根据环境自动选择, ap-singapore 区域使用 default-image-edit, 其他区域使用 hunyuan-image-v2.0-general-edit)")
    parser.add_argument("--revise", action="store_true", help="是否经过 prompt 改写能力")
    parser.add_argument("--n", type=int, default=1, help="生成图片个数，默认为1，目前只支持生成一张，固定为1，传其他的值会报错")

    # 可选参数
    parser.add_argument("--seed", type=int, help="生成种子，仅当生成图片数为1时生效，范围[1, 4294967295]，不传时默认随机")
    parser.add_argument("--moderation", help="是否开启内容审核，默认为 true")
    parser.add_argument("--input_moderation", help="是否开启输入内容审核,默认为 true")
    parser.add_argument("--footnote", help="业务自定义水印内容，限制 16 个字符长度（不区分中英文），生成在图片右下角")

    # 编辑强度参数
    parser.add_argument("--strengths", nargs='+', type=float, help="多个编辑强度值 (0.0-1.0)")
    parser.add_argument("--strength", type=float, default=0.8, help="单个编辑强度 (0.0-1.0, 默认: 0.8)")
    parser.add_argument("--background", choices=["transparent", "opaque"], help="背景透明度")
    parser.add_argument("--output-compression", type=int, help="图片压缩级别 (0-100)")
    parser.add_argument("--output-format", choices=["png", "webp", "jpeg"], default="", help="输出格式 (默认: png)")
    parser.add_argument("--partial-images", type=int, default=0, help="部分图片数量 (0-3, 默认: 0)")
    parser.add_argument("--quality", choices=["low", "medium", "high"], default="", help="图片质量 (默认: medium)")
    parser.add_argument("--response-format", choices=["url", "b64_json"], default="", help="响应格式 (默认: url)")
    parser.add_argument("--size", choices=["640x1408", "704x1344", "768x1280", "832x1216", "896x1152", "960x1088", "1024x1024", "1088x960", "1152x896", "1216x832", "1280x768", "1344x704", "1408x640"], default="1024x1024", help="图片尺寸 (默认: 1024x1024)")
    parser.add_argument("--stream", action="store_true", help="启用流式模式")
    parser.add_argument("--style", help="图片风格")
    parser.add_argument("--user", help="用户唯一标识符")
    parser.add_argument("--output", "-o", help="保存响应到文件")
    parser.add_argument("--download-dir", help="指定下载目录 (默认: frontend/public/)")
    parser.add_argument("--download-prefix", default="edited", help="下载文件名前缀 (默认: edited)")
    parser.add_argument("--no-download", action="store_true", help="不下载图片,仅返回 URL")
    parser.add_argument("--count", type=int, help="生成图片个数 (默认: 1)")
    
    args = parser.parse_args()
    
    try:
        # 确定编辑任务列表
        edit_tasks = []
        
        if args.count:
            # 使用相同描述生成多个版本
            if not args.prompt:
                print("错误: 使用 --count 时必须提供单个 prompt", file=sys.stderr)
                return 1
            
            strengths = args.strengths if args.strengths else [args.strength] * args.count
            if len(strengths) < args.count:
                # 如果强度数量不够,用最后一个值填充
                strengths.extend([strengths[-1]] * (args.count - len(strengths)))
            
            for i in range(args.count):
                edit_tasks.append({
                    'prompt': args.prompt,
                    'strength': strengths[i] if i < len(strengths) else args.strength,
                    'task_id': i + 1
                })
                
        elif args.prompts:
            # 使用多个不同描述
            strengths = args.strengths if args.strengths else [args.strength] * len(args.prompts)
            if len(strengths) < len(args.prompts):
                # 如果强度数量不够,用最后一个值填充
                strengths.extend([strengths[-1]] * (len(args.prompts) - len(strengths)))
            
            for i, prompt in enumerate(args.prompts):
                edit_tasks.append({
                    'prompt': prompt,
                    'strength': strengths[i] if i < len(strengths) else args.strength,
                    'task_id': i + 1
                })
                
        else:
            # 单个编辑任务
            edit_tasks.append({
                'prompt': args.prompt,
                'strength': args.strength,
                'task_id': 1
            })
        
        print(f"准备执行 {len(edit_tasks)} 个编辑任务...\n")
        
        all_results = []
        all_image_urls = []
        
        # 顺序执行每个编辑任务
        for task in edit_tasks:
            print(f"执行任务 {task['task_id']}/{len(edit_tasks)}: {task['prompt']} (强度: {task['strength']})")
            
            try:
                # 调用 API
                result = edit_image(
                    prompt=task['prompt'],
                    image_url=args.image_url,
                    image=args.image,
                    model=args.model,
                    background=args.background,
                    moderation=args.moderation,
                    input_moderation=args.input_moderation,
                    n=1,  # 每次只生成一张图片
                    seed=args.seed,
                    output_compression=args.output_compression,
                    output_format=args.output_format,
                    partial_images=args.partial_images,
                    quality=args.quality,
                    response_format=args.response_format,
                    size=args.size,
                    stream=args.stream,
                    style=args.style,
                    user=args.user,
                    footnote=args.footnote,
                    revise=args.revise,
                    strength=task['strength'],
                )
                
                # 添加任务信息到结果
                result['task_info'] = {
                    'task_id': task['task_id'],
                    'prompt': task['prompt'],
                    'strength': task['strength']
                }
                all_results.append(result)
                
                # 提取图片 URL
                if result.get("code") == 0 and "data" in result:
                    data = result["data"]
                    if "data" in data and len(data["data"]) > 0:
                        for img in data["data"]:
                            if "url" in img:
                                all_image_urls.append({
                                    'url': img['url'],
                                    'task_id': task['task_id'],
                                    'prompt': task['prompt'],
                                    'strength': task['strength'],
                                    'revised_prompt': img.get('revised_prompt', '')
                                })
                        print(f"  ✓ 任务 {task['task_id']} 完成")
                    else:
                        print(f"  ✗ 任务 {task['task_id']} 失败: 无图片数据")
                else:
                    print(f"  ✗ 任务 {task['task_id']} 失败: {result.get('msg', '未知错误')}")
                
                # 添加延迟避免频率限制
                if task['task_id'] < len(edit_tasks):
                    import time
                    time.sleep(1)
                    
            except Exception as e:
                print(f"  ✗ 任务 {task['task_id']} 执行失败: {e}")
                continue
        
        # 输出汇总结果
        print(f"\n=== 编辑任务完成 ===")
        print(f"总任务数: {len(edit_tasks)}")
        print(f"成功生成: {len(all_image_urls)} 张图片")
        
        # 保存完整响应到文件
        if args.output:
            with open(args.output, 'w', encoding='utf-8') as f:
                json.dump(all_results, f, ensure_ascii=False, indent=2)
            print(f"完整响应已保存到: {args.output}")
        
        # 显示所有生成的图片
        if all_image_urls:
            print("\n编辑后的图片:")
            for i, img_info in enumerate(all_image_urls, 1):
                print(f"{i}. 任务 {img_info['task_id']}: {img_info['prompt']}")
                print(f"   强度: {img_info['strength']}")
                print(f"   URL: {img_info['url']}")
                if img_info['revised_prompt']:
                    print(f"   改写后的提示词: {img_info['revised_prompt']}")
                print()
            
            # 下载图片（默认行为）
            if not args.no_download:
                print("开始下载图片...", file=sys.stderr)
                image_urls = [img['url'] for img in all_image_urls]
                downloaded_files = download_images(
                    image_urls=image_urls,
                    output_dir=args.download_dir,
                    prefix=args.download_prefix
                )
                
                if downloaded_files:
                    print(f"\n成功下载 {len(downloaded_files)} 张图片:")
                    for i, filepath in enumerate(downloaded_files):
                        filename = Path(filepath).name
                        task_info = all_image_urls[i]
                        print(f"  - {filename} (任务 {task_info['task_id']}: {task_info['prompt']})")
        
        return 0
        
    except ValueError as e:
        print(f"错误: {e}", file=sys.stderr)
        return 1
    except FileNotFoundError as e:
        print(f"文件错误: {e}", file=sys.stderr)
        return 1
    except Exception as e:
        print(f"未预期的错误: {e}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())