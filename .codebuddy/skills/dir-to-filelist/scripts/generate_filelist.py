#!/usr/bin/env python3
"""
目录文件清单生成工具

扫描指定目录下的所有文件，生成 Markdown 格式的文件清单，记录文件名和对应路径。

使用方法:
    python3 generate_filelist.py <target_dir> [options]

示例:
    # 基础使用
    python3 generate_filelist.py game-basic/assets/images -o assets/list.md

    # 排除某个文件夹
    python3 generate_filelist.py game-basic/assets/images -o assets/list.md --exclude-dir temp

    # 排除某个文件
    python3 generate_filelist.py game-basic/assets/images -o assets/list.md --exclude-file placeholder.png

    # 同时排除多个
    python3 generate_filelist.py game-basic/assets/images -o assets/list.md --exclude-dir temp --exclude-dir raw --exclude-file .DS_Store
"""

import os
import sys
import argparse
from pathlib import Path
from collections import defaultdict


def should_exclude(path: Path, exclude_dirs: list, exclude_files: list) -> bool:
    """检查文件或目录是否应被排除"""
    # 检查文件名排除
    if path.name in exclude_files:
        return True

    # 检查目录排除（路径中任何一级目录匹配即排除）
    for part in path.parts:
        if part in exclude_dirs:
            return True

    return False


def scan_directory(target_dir: str, exclude_dirs: list, exclude_files: list) -> dict:
    """
    扫描目录，返回按子目录分组的文件列表
    返回: { "category_path": [ (filename, relative_path) ] }
    """
    target = Path(target_dir)
    if not target.exists():
        print(f"Error: Directory '{target_dir}' does not exist.", file=sys.stderr)
        sys.exit(1)
    if not target.is_dir():
        print(f"Error: '{target_dir}' is not a directory.", file=sys.stderr)
        sys.exit(1)

    grouped = defaultdict(list)

    for root, dirs, files in os.walk(target):
        root_path = Path(root)
        rel_root = root_path.relative_to(target)

        # 排除隐藏目录和指定排除的目录
        dirs[:] = [
            d for d in sorted(dirs)
            if not d.startswith('.') and d not in exclude_dirs
        ]

        for f in sorted(files):
            # 跳过隐藏文件
            if f.startswith('.'):
                continue

            file_path = root_path / f
            rel_file = file_path.relative_to(target)

            if should_exclude(rel_file, exclude_dirs, exclude_files):
                continue

            # 分组 key：直接子目录名，如果在根目录则用 "(root)"
            category = str(rel_root) if str(rel_root) != '.' else '(root)'
            rel_path_str = str(target / rel_file)

            grouped[category].append((f, rel_path_str))

    return grouped


def generate_markdown(grouped: dict, target_dir: str, output_path: str) -> str:
    """生成 Markdown 内容"""
    lines = []
    lines.append("# Asset List (素材清单)\n")
    lines.append(f"> Auto-generated file list from `{target_dir}`.\n")
    lines.append(f"> **Total**: {sum(len(v) for v in grouped.values())} files\n")

    total_files = 0

    # 按 category 排序输出
    for category in sorted(grouped.keys()):
        files = grouped[category]
        total_files += len(files)

        # 用目录名作为二级标题
        if category == '(root)':
            lines.append(f"## Root\n")
        else:
            # 美化目录名作为标题
            display_name = category.replace('/', ' / ').title()
            lines.append(f"## {display_name}\n")

        lines.append("| File Name | Path |")
        lines.append("| :--- | :--- |")

        for filename, filepath in files:
            lines.append(f"| `{filename}` | `{filepath}` |")

        lines.append("")

    return '\n'.join(lines)


def main():
    parser = argparse.ArgumentParser(
        description='Scan a directory and generate a Markdown file listing all files with their paths.'
    )
    parser.add_argument(
        'target_dir',
        help='Target directory to scan'
    )
    parser.add_argument(
        '-o', '--output',
        default=None,
        help='Output markdown file path (default: <target_dir>/list.md)'
    )
    parser.add_argument(
        '--exclude-dir',
        action='append',
        default=[],
        help='Directory name to exclude (can be specified multiple times)'
    )
    parser.add_argument(
        '--exclude-file',
        action='append',
        default=[],
        help='File name to exclude (can be specified multiple times)'
    )

    args = parser.parse_args()

    # 默认输出路径
    output_path = args.output if args.output else os.path.join(args.target_dir, 'list.md')

    # 扫描目录
    grouped = scan_directory(args.target_dir, args.exclude_dir, args.exclude_file)

    if not grouped:
        print(f"Warning: No files found in '{args.target_dir}'.", file=sys.stderr)

    # 生成 Markdown
    md_content = generate_markdown(grouped, args.target_dir, output_path)

    # 确保输出目录存在
    output_dir = os.path.dirname(output_path)
    if output_dir:
        os.makedirs(output_dir, exist_ok=True)

    # 写入文件
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(md_content)

    total = sum(len(v) for v in grouped.values())
    categories = len(grouped)
    print(f"✅ Generated: {output_path}")
    print(f"   {total} files in {categories} categories")
    if args.exclude_dir:
        print(f"   Excluded dirs: {', '.join(args.exclude_dir)}")
    if args.exclude_file:
        print(f"   Excluded files: {', '.join(args.exclude_file)}")


if __name__ == '__main__':
    main()
