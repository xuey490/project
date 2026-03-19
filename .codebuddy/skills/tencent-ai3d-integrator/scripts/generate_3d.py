#!/usr/bin/env python3
"""
腾讯云混元 3D 生成 API 调用脚本

支持文本生成 3D 模型、图片生成 3D 模型，以及任务状态查询。

使用方法:
    # 文本生成 3D
    python3 generate_3d.py text "一只可爱的小猫"

    # 图片生成 3D (URL)
    python3 generate_3d.py image --image-url "https://example.com/cat.jpg"

    # 图片生成 3D (本地文件)
    python3 generate_3d.py image --image-file ./cat.jpg

    # 查询任务状态
    python3 generate_3d.py query <job_id>

    # 高级选项
    python3 generate_3d.py text "一个可爱的机器人" --enable-pbr --face-count 500000 --generate-type Normal

    # 仅提交任务，不等待完成
    python3 generate_3d.py text "一只猫" --no-wait

    # 仅打印 URL，不下载文件
    python3 generate_3d.py text "一只猫" --no-download

依赖:
    pip install tencentcloud-sdk-python-ai3d requests python-dotenv
"""

import os
import sys
import json
import time
import base64
import argparse
import requests
from pathlib import Path


def _load_dotenv():
    """自动加载 .env 文件（向上查找到项目根目录）"""
    try:
        from dotenv import load_dotenv

        # 从当前目录向上查找 .env
        current = Path.cwd()
        for d in [current, *current.parents]:
            env_file = d / ".env"
            if env_file.is_file():
                load_dotenv(env_file, override=False)
                print(f"[信息] 已加载环境变量: {env_file}", file=sys.stderr)
                return
    except ImportError:
        pass  # python-dotenv 未安装，跳过


_load_dotenv()
from typing import Optional, Dict, Any

from tencentcloud.common import credential
from tencentcloud.common.profile.client_profile import ClientProfile
from tencentcloud.common.profile.http_profile import HttpProfile
from tencentcloud.common.exception.tencent_cloud_sdk_exception import TencentCloudSDKException
from tencentcloud.ai3d.v20250513 import ai3d_client, models


# ============================================================
# 客户端初始化
# ============================================================

def create_client(region: Optional[str] = None) -> ai3d_client.Ai3dClient:
    """
    创建腾讯云 AI3D 客户端

    认证优先级:
    1. 环境变量 TENCENTCLOUD_SECRET_ID / TENCENTCLOUD_SECRET_KEY
    2. 沙箱模式 (检测 X_IDE_AUTH_PROXY 环境变量)

    Args:
        region: 地域，默认 ap-guangzhou

    Returns:
        Ai3dClient 实例

    Raises:
        ValueError: 未配置认证信息
    """
    is_sandbox = os.environ.get("X_IDE_AUTH_PROXY") is not None
    secret_id = os.environ.get("TENCENTCLOUD_SECRET_ID", "mock_secret_id" if is_sandbox else "")
    secret_key = os.environ.get("TENCENTCLOUD_SECRET_KEY", "mock_secret_key" if is_sandbox else "")
    region = region or os.environ.get("TENCENTCLOUD_REGION", "ap-guangzhou")

    if not secret_id or not secret_key:
        raise ValueError(
            "未配置认证信息。请设置环境变量:\n"
            "  export TENCENTCLOUD_SECRET_ID=your_secret_id\n"
            "  export TENCENTCLOUD_SECRET_KEY=your_secret_key\n"
            "或在 Genie 沙箱环境中运行（自动使用代理认证）。"
        )

    # 判断是否使用沙箱代理
    is_mock = secret_id == "mock_secret_id" or secret_key == "mock_secret_key"
    use_sandbox = is_sandbox and is_mock

    cred = credential.Credential(secret_id, secret_key)

    http_profile = HttpProfile()
    http_profile.reqMethod = "POST"
    http_profile.reqTimeout = 60

    if use_sandbox:
        http_profile.endpoint = "ai3d.tencent_cloud.auth-proxy.local"
        http_profile.scheme = "http"
        print("[信息] 使用沙箱代理模式", file=sys.stderr)
    else:
        http_profile.endpoint = "ai3d.tencentcloudapi.com"
        http_profile.scheme = "https"

    client_profile = ClientProfile()
    client_profile.httpProfile = http_profile

    return ai3d_client.Ai3dClient(cred, region, client_profile)


# ============================================================
# 任务提交
# ============================================================

def submit_text_to_3d(
    client: ai3d_client.Ai3dClient,
    prompt: str,
    enable_pbr: bool = False,
    face_count: Optional[int] = None,
    generate_type: Optional[str] = None,
    polygon_type: Optional[str] = None,
) -> Dict[str, str]:
    """
    提交文本生成 3D 任务

    Args:
        client: AI3D 客户端
        prompt: 3D 模型描述文本，最多 200 字符
        enable_pbr: 是否启用 PBR 材质
        face_count: 面数 (40000-1500000)
        generate_type: 生成类型 (Normal/LowPoly/Geometry/Sketch)
        polygon_type: 多边形类型 (triangle/quadrilateral)，仅 LowPoly 模式有效

    Returns:
        {"job_id": str, "request_id": str}
    """
    if not prompt or not prompt.strip():
        raise ValueError("prompt 不能为空")
    if len(prompt) > 200:
        raise ValueError("prompt 不能超过 200 个字符")

    req = models.SubmitHunyuanTo3DProJobRequest()
    params = {
        "Prompt": prompt,
        "EnablePBR": enable_pbr,
    }
    if face_count is not None:
        params["FaceCount"] = face_count
    if generate_type is not None:
        params["GenerateType"] = generate_type
    if polygon_type is not None:
        params["PolygonType"] = polygon_type

    req.from_json_string(json.dumps(params))

    print(f"[信息] 提交文本生成 3D 任务: \"{prompt}\"", file=sys.stderr)
    resp = client.SubmitHunyuanTo3DProJob(req)
    result = json.loads(resp.to_json_string())

    return {
        "job_id": result["JobId"],
        "request_id": result["RequestId"],
    }


def submit_image_to_3d(
    client: ai3d_client.Ai3dClient,
    image_url: Optional[str] = None,
    image_file: Optional[str] = None,
    enable_pbr: bool = False,
    face_count: Optional[int] = None,
    generate_type: Optional[str] = None,
    polygon_type: Optional[str] = None,
) -> Dict[str, str]:
    """
    提交图片生成 3D 任务

    Args:
        client: AI3D 客户端
        image_url: 图片 URL
        image_file: 本地图片文件路径 (与 image_url 二选一)
        enable_pbr: 是否启用 PBR 材质
        face_count: 面数 (40000-1500000)
        generate_type: 生成类型 (Normal/LowPoly/Geometry/Sketch)
        polygon_type: 多边形类型 (triangle/quadrilateral)

    Returns:
        {"job_id": str, "request_id": str}
    """
    if not image_url and not image_file:
        raise ValueError("必须提供 --image-url 或 --image-file")

    req = models.SubmitHunyuanTo3DProJobRequest()
    params: Dict[str, Any] = {
        "EnablePBR": enable_pbr,
    }

    if image_file:
        # 读取本地文件并转为 base64
        file_path = Path(image_file)
        if not file_path.exists():
            raise FileNotFoundError(f"图片文件不存在: {image_file}")
        with open(file_path, "rb") as f:
            image_data = f.read()
        params["ImageBase64"] = base64.b64encode(image_data).decode("utf-8")
        print(f"[信息] 提交图片生成 3D 任务 (本地文件): {image_file}", file=sys.stderr)
    else:
        params["ImageUrl"] = image_url
        print(f"[信息] 提交图片生成 3D 任务 (URL): {image_url}", file=sys.stderr)

    if face_count is not None:
        params["FaceCount"] = face_count
    if generate_type is not None:
        params["GenerateType"] = generate_type
    if polygon_type is not None:
        params["PolygonType"] = polygon_type

    req.from_json_string(json.dumps(params))

    resp = client.SubmitHunyuanTo3DProJob(req)
    result = json.loads(resp.to_json_string())

    return {
        "job_id": result["JobId"],
        "request_id": result["RequestId"],
    }


# ============================================================
# 任务查询与等待
# ============================================================

def query_task(client: ai3d_client.Ai3dClient, job_id: str) -> Dict[str, Any]:
    """
    查询任务状态

    Args:
        client: AI3D 客户端
        job_id: 任务 ID

    Returns:
        {
            "status": str,         # WAIT/RUN/DONE/FAIL
            "error_code": str,
            "error_message": str,
            "files": [{"preview_image_url": str, "type": str, "url": str}],
            "request_id": str,
        }
    """
    req = models.QueryHunyuanTo3DProJobRequest()
    req.from_json_string(json.dumps({"JobId": job_id}))

    resp = client.QueryHunyuanTo3DProJob(req)
    result = json.loads(resp.to_json_string())

    files = []
    for f in result.get("ResultFile3Ds") or []:
        files.append({
            "preview_image_url": f.get("PreviewImageUrl", ""),
            "type": f.get("Type", ""),
            "url": f.get("Url", ""),
        })

    return {
        "status": result.get("Status", ""),
        "error_code": result.get("ErrorCode", ""),
        "error_message": result.get("ErrorMessage", ""),
        "files": files,
        "request_id": result.get("RequestId", ""),
    }


def wait_for_completion(
    client: ai3d_client.Ai3dClient,
    job_id: str,
    poll_interval: int = 5,
    timeout: int = 300,
) -> Dict[str, Any]:
    """
    轮询等待任务完成

    Args:
        client: AI3D 客户端
        job_id: 任务 ID
        poll_interval: 轮询间隔 (秒)
        timeout: 最大等待时间 (秒)

    Returns:
        任务查询结果

    Raises:
        TimeoutError: 超时
        RuntimeError: 任务失败
    """
    start_time = time.time()

    while True:
        result = query_task(client, job_id)
        status = result["status"]

        if status == "DONE":
            print(f"[信息] 任务完成", file=sys.stderr)
            return result

        if status == "FAIL":
            error_msg = result.get("error_message") or result.get("error_code") or "未知错误"
            raise RuntimeError(f"任务失败: {error_msg}")

        elapsed = time.time() - start_time
        if elapsed > timeout:
            raise TimeoutError(f"任务超时: 等待超过 {timeout} 秒")

        print(
            f"[信息] 任务状态: {status}，已等待 {int(elapsed)} 秒，{poll_interval} 秒后重试...",
            file=sys.stderr,
        )
        time.sleep(poll_interval)


# ============================================================
# 文件下载
# ============================================================

def download_files(
    files: list,
    output_dir: Optional[str] = None,
) -> list:
    """
    下载 3D 模型文件

    Args:
        files: 文件列表 [{"url": str, "type": str, ...}]
        output_dir: 输出目录，默认当前目录

    Returns:
        下载成功的本地文件路径列表
    """
    if not files:
        print("[警告] 没有可下载的文件", file=sys.stderr)
        return []

    out_path = Path(output_dir) if output_dir else Path.cwd()
    out_path.mkdir(parents=True, exist_ok=True)

    downloaded = []
    for i, f in enumerate(files, 1):
        url = f.get("url", "")
        if not url:
            continue

        # 从 URL 推断文件名
        file_type = f.get("type", "zip").lower()
        filename = f"model_{i}.{file_type}"
        if url.endswith(".zip") or ".zip" in url:
            filename = f"model_{i}.zip"

        filepath = out_path / filename

        try:
            print(f"[信息] 下载文件 {i}/{len(files)}: {url}", file=sys.stderr)
            resp = requests.get(url, timeout=120, stream=True)
            resp.raise_for_status()

            with open(filepath, "wb") as fp:
                for chunk in resp.iter_content(chunk_size=8192):
                    fp.write(chunk)

            downloaded.append(str(filepath))
            print(f"[信息] 已保存到: {filepath}", file=sys.stderr)
        except requests.RequestException as e:
            print(f"[错误] 下载文件 {i} 失败: {e}", file=sys.stderr)

    # 下载预览图
    for i, f in enumerate(files, 1):
        preview_url = f.get("preview_image_url", "")
        if not preview_url:
            continue

        preview_path = out_path / f"preview_{i}.png"
        try:
            print(f"[信息] 下载预览图 {i}: {preview_url}", file=sys.stderr)
            resp = requests.get(preview_url, timeout=60, stream=True)
            resp.raise_for_status()

            with open(preview_path, "wb") as fp:
                for chunk in resp.iter_content(chunk_size=8192):
                    fp.write(chunk)

            downloaded.append(str(preview_path))
            print(f"[信息] 预览图已保存到: {preview_path}", file=sys.stderr)
        except requests.RequestException as e:
            print(f"[警告] 下载预览图失败: {e}", file=sys.stderr)

    return downloaded


# ============================================================
# CLI 入口
# ============================================================

def build_parser() -> argparse.ArgumentParser:
    """构建命令行参数解析器"""
    parser = argparse.ArgumentParser(
        description="腾讯云混元 3D 生成工具",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
示例:
  # 文本生成 3D
  %(prog)s text "一只可爱的小猫"

  # 启用 PBR 材质
  %(prog)s text "一个可爱的机器人" --enable-pbr --face-count 500000

  # 图片生成 3D (URL)
  %(prog)s image --image-url "https://example.com/cat.jpg"

  # 图片生成 3D (本地文件)
  %(prog)s image --image-file ./cat.jpg

  # 仅提交任务，不等待完成
  %(prog)s text "一只猫" --no-wait

  # 查询任务状态
  %(prog)s query <job_id>

  # 指定下载目录
  %(prog)s text "一只猫" --download-dir ./output
        """,
    )

    subparsers = parser.add_subparsers(dest="command", help="操作类型")

    # ---- text 子命令 ----
    text_parser = subparsers.add_parser("text", help="文本生成 3D 模型")
    text_parser.add_argument("prompt", help="3D 模型描述文本 (最多 200 字符)")
    _add_common_options(text_parser)

    # ---- image 子命令 ----
    image_parser = subparsers.add_parser("image", help="图片生成 3D 模型")
    image_group = image_parser.add_mutually_exclusive_group(required=True)
    image_group.add_argument("--image-url", help="图片 URL 地址")
    image_group.add_argument("--image-file", help="本地图片文件路径")
    _add_common_options(image_parser)

    # ---- query 子命令 ----
    query_parser = subparsers.add_parser("query", help="查询任务状态")
    query_parser.add_argument("job_id", help="任务 ID")
    query_parser.add_argument("--region", help="地域 (默认: ap-guangzhou)")

    return parser


def _add_common_options(parser: argparse.ArgumentParser):
    """为 text/image 子命令添加公共选项"""
    parser.add_argument("--enable-pbr", action="store_true", help="启用 PBR 材质生成")
    parser.add_argument(
        "--face-count", type=int, help="生成模型面数 (40000-1500000，默认 500000)"
    )
    parser.add_argument(
        "--generate-type",
        choices=["Normal", "LowPoly", "Geometry", "Sketch"],
        help="生成类型 (默认: Normal)",
    )
    parser.add_argument(
        "--polygon-type",
        choices=["triangle", "quadrilateral"],
        help="多边形类型，仅 LowPoly 模式有效 (默认: triangle)",
    )
    parser.add_argument("--region", help="地域 (默认: ap-guangzhou)")
    parser.add_argument(
        "--download-dir", help="下载输出目录 (默认: 当前目录)"
    )
    parser.add_argument(
        "--poll-interval", type=int, default=5, help="轮询间隔秒数 (默认: 5)"
    )
    parser.add_argument(
        "--timeout", type=int, default=300, help="最大等待秒数 (默认: 300)"
    )
    parser.add_argument(
        "--no-download", action="store_true", help="不下载文件，仅输出 URL"
    )
    parser.add_argument(
        "--no-wait", action="store_true", help="仅提交任务，不等待完成"
    )


def main():
    parser = build_parser()
    args = parser.parse_args()

    if not args.command:
        parser.print_help()
        return 1

    try:
        region = getattr(args, "region", None)
        client = create_client(region=region)

        # ---- query 子命令 ----
        if args.command == "query":
            result = query_task(client, args.job_id)
            print(json.dumps(result, ensure_ascii=False, indent=2))
            return 0

        # ---- text / image 子命令 ----
        if args.command == "text":
            submit_result = submit_text_to_3d(
                client,
                prompt=args.prompt,
                enable_pbr=args.enable_pbr,
                face_count=args.face_count,
                generate_type=args.generate_type,
                polygon_type=args.polygon_type,
            )
        elif args.command == "image":
            submit_result = submit_image_to_3d(
                client,
                image_url=getattr(args, "image_url", None),
                image_file=getattr(args, "image_file", None),
                enable_pbr=args.enable_pbr,
                face_count=args.face_count,
                generate_type=args.generate_type,
                polygon_type=args.polygon_type,
            )
        else:
            parser.print_help()
            return 1

        job_id = submit_result["job_id"]
        print(f"[信息] 任务已提交，JobId: {job_id}", file=sys.stderr)

        # 仅提交模式
        if args.no_wait:
            print(json.dumps(submit_result, ensure_ascii=False, indent=2))
            return 0

        # 等待任务完成
        print(f"[信息] 等待任务完成 (超时: {args.timeout}s，轮询间隔: {args.poll_interval}s)...", file=sys.stderr)
        task_result = wait_for_completion(
            client,
            job_id,
            poll_interval=args.poll_interval,
            timeout=args.timeout,
        )

        # 输出结果
        print(json.dumps(task_result, ensure_ascii=False, indent=2))

        # 下载文件
        if task_result["files"] and not args.no_download:
            print("\n开始下载文件...", file=sys.stderr)
            downloaded = download_files(
                task_result["files"],
                output_dir=args.download_dir,
            )
            if downloaded:
                print(f"\n成功下载 {len(downloaded)} 个文件:")
                for fp in downloaded:
                    print(f"  - {fp}")
        elif task_result["files"]:
            print("\n生成的文件 URL:")
            for i, f in enumerate(task_result["files"], 1):
                print(f"  {i}. 模型: {f['url']}")
                if f.get("preview_image_url"):
                    print(f"     预览: {f['preview_image_url']}")

        return 0

    except TencentCloudSDKException as e:
        print(f"[错误] 腾讯云 API 错误: {e}", file=sys.stderr)
        return 1
    except (ValueError, FileNotFoundError) as e:
        print(f"[错误] 参数错误: {e}", file=sys.stderr)
        return 1
    except TimeoutError as e:
        print(f"[错误] {e}", file=sys.stderr)
        return 1
    except RuntimeError as e:
        print(f"[错误] {e}", file=sys.stderr)
        return 1
    except Exception as e:
        print(f"[错误] 未预期的错误: {e}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
