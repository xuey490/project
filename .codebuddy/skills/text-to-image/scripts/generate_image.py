#!/usr/bin/env python3
"""
腾讯混元文生图 API 调用脚本

使用方法:
    python3 generate_image.py "图片描述" [选项]

示例:
    # 基础使用
    python3 generate_image.py "一只可爱的橘猫坐在窗台上"
    
    # 指定尺寸和质量
    python3 generate_image.py "未来城市" --size 768x1280 --quality high
    
    # 添加水印和改写提示词
    python3 generate_image.py "夕阳下的海滩" --footnote "我的水印" --revise
    
    # 指定下载目录和文件名前缀
    python3 generate_image.py "雪山日出" --download-dir ./images --download-prefix mountain
    
    # 仅返回 URL，不下载图片
    python3 generate_image.py "星空下的森林" --no-download
"""

import os
import sys
import json
import argparse
import requests
import random
import string
import time
from typing import Optional, Dict, Any, List
from pathlib import Path
from urllib.parse import urlparse


def generate_image(
    prompt: str,
    model: Optional[str] = None,
    background: Optional[str] = None,
    moderation: Optional[str] = None,
    n: int = 1,
    output_compression: Optional[int] = None,
    output_format: str = "png",
    partial_images: int = 0,
    quality: str = "medium",
    response_format: str = "url",
    size: str = "1024x1024",
    stream: bool = False,
    style: Optional[str] = None,
    user: Optional[str] = None,
    footnote: Optional[str] = None,
    revise: bool = False,
) -> Dict[str, Any]:
    """
    调用腾讯混元文生图 API 生成图片
    
    Args:
        prompt: 图片描述文本
        model: 使用的模型名称,如果不指定则根据环境自动选择
        background: 背景透明度设置 (transparent/opaque)
        moderation: 内容审核级别
        n: 生成图片数量 (1-10)
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
        
    Returns:
        API 响应的 JSON 数据
        
    Raises:
        ValueError: 当参数无效时
        requests.RequestException: 当 API 请求失败时
        
    环境变量:
        X_IDE_AUTH_PROXY: 如果设置,将使用零配置 auth proxy 模式
        X_IDE_SPACE_REGION: 如果值为 ap-singapore,将使用 default-image-generation 模型
        COPILOT_API_KEY: 如果设置,将直接调用腾讯 API (需要有效的 API Key)
        注意: 必须至少设置 X_IDE_AUTH_PROXY 或 COPILOT_API_KEY 之一，否则会报错
    """
    # 确定默认模型
    if model is None:
        space_region = os.environ.get("X_IDE_SPACE_REGION", "")
        if space_region == "ap-singapore":
            model = "default-image-generation"
        else:
            model = "hunyuan-image-v3.0"
    
    # 检查是否在 auth proxy 环境中
    use_auth_proxy = os.environ.get("X_IDE_AUTH_PROXY") is not None
    
    # 从环境变量获取 API Key (仅在非 auth proxy 模式下需要)
    api_key = os.environ.get("COPILOT_API_KEY", "")
    
    # 构建请求 URL 和请求头
    if use_auth_proxy:
        # 使用 auth proxy 模式 (零配置模式)
        url = "http://auth.proxy/codebuddy/v2/images/generations"
        headers = {
            "Content-Type": "application/json",
        }
    elif api_key:
        # 使用直接 API 访问模式 (需要 API Key)
        url = "https://copilot.tencent.com/v2/images/generations"
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
        "prompt": prompt,
        "model": model,
        "n": n,
        "output_format": output_format,
        "partial_images": partial_images,
        "quality": quality,
        "response_format": response_format,
        "size": size,
        "stream": stream,
    }
    
    # 添加可选参数
    if background:
        payload["background"] = background
    if moderation:
        payload["moderation"] = moderation
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

    # 在发送请求前打印参数详情（尤其是 prompt），便于排查
    request_debug = {
        "url": url,
        "payload": payload,
    }
    print("即将发送文生图请求参数如下（含 prompt）:\n" + json.dumps(request_debug, ensure_ascii=False, indent=2), file=sys.stderr)

    # 发送请求,遇到429错误时自动重试
    retry_count = 0
    while True:
        try:
            response = requests.post(url, headers=headers, json=payload, timeout=60)
            
            # 如果返回429,等待1秒后重试
            if response.status_code == 429:
                retry_count += 1
                print(f"收到429错误 (请求过于频繁),等待1秒后重试 (第 {retry_count} 次)...", file=sys.stderr)
                time.sleep(1)
                continue
            
            # 其他错误直接抛出
            response.raise_for_status()
            return response.json()
            
        except requests.exceptions.HTTPError as e:
            # HTTPError在raise_for_status()之后抛出,但429已经被处理了
            print(f"API 请求失败: {e}", file=sys.stderr)
            if hasattr(e, 'response') and e.response is not None:
                print(f"响应内容: {e.response.text}", file=sys.stderr)
            raise
        except requests.exceptions.RequestException as e:
            print(f"API 请求失败: {e}", file=sys.stderr)
            if hasattr(e, 'response') and e.response is not None:
                print(f"响应内容: {e.response.text}", file=sys.stderr)
            raise


def download_images(
    image_urls: List[str],
    output_dir: Optional[str] = None,
    prefix: str = "generated"
) -> List[str]:
    """
    从 URL 下载图片到指定目录
    
    Args:
        image_urls: 图片 URL 列表
        output_dir: 输出目录路径，默认为项目的 frontend/public/ 目录
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
            # 如果找不到，默认创建在当前目录的 frontend/public
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
        description="腾讯混元文生图 API 调用工具",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
示例:
  # 基础使用
  %(prog)s "一只可爱的橘猫坐在窗台上"
  
  # 指定尺寸和质量
  %(prog)s "未来城市" --size 768x1280 --quality high
  
  # 添加水印和改写提示词
  %(prog)s "夕阳下的海滩" --footnote "我的水印" --revise
  
  # 指定下载目录和文件名
  %(prog)s "雪山日出" --download-dir ./images --download-prefix mountain
  
  # 仅返回 URL，不下载图片
  %(prog)s "星空下的森林" --no-download
        """
    )
    
    # 必填参数
    parser.add_argument("prompt", help="图片描述文本")
    
    # 可选参数
    parser.add_argument("--model", help="模型名称 (默认: 根据环境自动选择, ap-singapore 区域使用 default-image-generation, 其他区域使用 hunyuan-image-v3.0)")
    parser.add_argument("--background", choices=["transparent", "opaque"], help="背景透明度")
    parser.add_argument("--moderation", help="内容审核级别")
    parser.add_argument("--n", type=int, default=1, help="生成图片数量 (1-10, 默认: 1)")
    parser.add_argument("--output-compression", type=int, help="图片压缩级别 (0-100)")
    parser.add_argument("--output-format", choices=["png", "webp", "jpeg"], default="png", help="输出格式 (默认: png)")
    parser.add_argument("--partial-images", type=int, default=0, help="部分图片数量 (0-3, 默认: 0)")
    parser.add_argument("--quality", choices=["low", "medium", "high"], default="medium", help="图片质量 (默认: medium)")
    parser.add_argument("--response-format", choices=["url", "b64_json"], default="url", help="响应格式 (默认: url)")
    parser.add_argument("--size", choices=["640x1408", "704x1344", "768x1280", "832x1216", "896x1152", "960x1088", "1024x1024", "1088x960", "1152x896", "1216x832", "1280x768", "1344x704", "1408x640"], default="1024x1024", help="图片尺寸 (默认: 1024x1024)")
    parser.add_argument("--stream", action="store_true", help="启用流式模式")
    parser.add_argument("--style", help="图片风格")
    parser.add_argument("--user", help="用户唯一标识符")
    parser.add_argument("--footnote", help="自定义水印 (最多16个字符)")
    parser.add_argument("--revise", action="store_true", help="改写prompt")
    parser.add_argument("--output", "-o", help="保存响应到文件")
    parser.add_argument("--download-dir", help="指定下载目录 (默认: frontend/public/)")
    parser.add_argument("--download-prefix", default="generated", help="下载文件名前缀 (默认: generated)")
    parser.add_argument("--no-download", action="store_true", help="不下载图片，仅返回 URL")
    
    args = parser.parse_args()
    
    try:
        # 调用 API
        result = generate_image(
            prompt=args.prompt,
            model=args.model,
            background=args.background,
            moderation=args.moderation,
            n=args.n,
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
        )
        
        # 输出结果
        if args.output:
            with open(args.output, 'w', encoding='utf-8') as f:
                json.dump(result, f, ensure_ascii=False, indent=2)
            print(f"响应已保存到: {args.output}")
        else:
            print(json.dumps(result, ensure_ascii=False, indent=2))
        
        # 如果成功，提取并显示图片 URL
        if result.get("code") == 0 and "data" in result:
            data = result["data"]
            if "data" in data and len(data["data"]) > 0:
                print("\n生成的图片:")
                image_urls = []
                for i, img in enumerate(data["data"], 1):
                    if "url" in img:
                        image_urls.append(img['url'])
                        print(f"{i}. URL: {img['url']}")
                    if "revised_prompt" in img:
                        print(f"   改写后的提示词: {img['revised_prompt']}")
                
                # 下载图片（默认行为）
                if image_urls and not args.no_download:
                    print("\n开始下载图片...", file=sys.stderr)
                    downloaded_files = download_images(
                        image_urls=image_urls,
                        output_dir=args.download_dir,
                        prefix=args.download_prefix
                    )
                    
                    if downloaded_files:
                        print(f"\n成功下载 {len(downloaded_files)} 张图片:")
                        for filepath in downloaded_files:
                            filename = Path(filepath).name
                            print(f"  - {filename}")

        
        return 0
        
    except ValueError as e:
        print(f"错误: {e}", file=sys.stderr)
        return 1
    except Exception as e:
        print(f"未预期的错误: {e}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
