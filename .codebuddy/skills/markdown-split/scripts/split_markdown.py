#!/usr/bin/env python3
"""
Markdown 文档智能拆分工具

自动分析文档结构，智能选择拆分级别，将大型 Markdown 文档拆分成多个独立文件。

使用方法:
    python3 split_markdown.py <input_file> [--output-dir <dir>]

示例:
    # 基础使用 - 自动检测拆分级别
    python3 split_markdown.py docs/guide.md
    
    # 指定输出目录
    python3 split_markdown.py docs/guide.md --output-dir ./chapters
    
    # 手动指定拆分级别（1-6）
    python3 split_markdown.py docs/guide.md --level 2
    
    # 添加文件名前缀
    python3 split_markdown.py docs/guide.md -o ./chapters -p "chapter_"
"""

import os
import sys
import re
import argparse
from typing import List, Tuple, Dict
from pathlib import Path
from collections import Counter


def sanitize_filename(title: str) -> str:
    """
    清理标题，生成合法的文件名
    
    Args:
        title: 原始标题文本
        
    Returns:
        清理后的文件名（不含扩展名）
    """
    # 移除 Markdown 链接语法
    title = re.sub(r'\[([^\]]+)\]\([^)]+\)', r'\1', title)
    # 移除 HTML 标签
    title = re.sub(r'<[^>]+>', '', title)
    # 移除特殊字符，保留中文、字母、数字、空格、下划线、连字符
    title = re.sub(r'[^\w\s\u4e00-\u9fff-]', '', title)
    # 将空格替换为下划线
    title = re.sub(r'\s+', '_', title.strip())
    # 移除连续的下划线
    title = re.sub(r'_+', '_', title)
    # 移除首尾下划线
    title = title.strip('_')
    
    # 如果清理后为空，使用默认名称
    if not title:
        title = "untitled"
    
    # 限制文件名长度
    if len(title) > 50:
        title = title[:50].rstrip('_')
    
    return title


def remove_code_blocks(content: str) -> Tuple[str, List[str]]:
    """
    移除代码块，返回处理后的内容和代码块列表
    """
    code_blocks = []
    code_pattern = re.compile(r'```[\s\S]*?```', re.MULTILINE)
    
    def save_code_block(match):
        code_blocks.append(match.group(0))
        return f'\x00CODE_BLOCK_{len(code_blocks) - 1}\x00'
    
    content_without_code = code_pattern.sub(save_code_block, content)
    return content_without_code, code_blocks


def restore_code_blocks(text: str, code_blocks: List[str]) -> str:
    """
    还原代码块
    """
    for i, block in enumerate(code_blocks):
        text = text.replace(f'\x00CODE_BLOCK_{i}\x00', block)
    return text


def analyze_heading_structure(content: str) -> Dict[int, int]:
    """
    分析文档的标题结构，统计各级标题数量
    
    Args:
        content: 不含代码块的 Markdown 内容
        
    Returns:
        字典，key 为标题级别 (1-6)，value 为该级别标题数量
    """
    heading_counts = Counter()
    
    # 匹配所有级别的标题
    heading_pattern = re.compile(r'^(#{1,6}) +(.+)$', re.MULTILINE)
    
    for match in heading_pattern.finditer(content):
        level = len(match.group(1))
        heading_counts[level] += 1
    
    return dict(heading_counts)


def detect_split_level(heading_counts: Dict[int, int]) -> int:
    """
    智能检测最适合的拆分级别
    
    策略：
    1. 找到最高级别（数字最小）的标题
    2. 如果最高级别只有 1 个，尝试下一级别
    3. 选择数量在 2-10 之间的最高级别
    4. 如果都不满足，选择数量最多且 >= 2 的级别
    
    Args:
        heading_counts: 各级标题数量统计
        
    Returns:
        推荐的拆分级别 (1-6)，如果无法确定返回 1
    """
    if not heading_counts:
        return 1
    
    # 按级别排序（从高到低，即数字从小到大）
    sorted_levels = sorted(heading_counts.keys())
    
    # 策略1: 找数量在 2-10 之间的最高级别
    for level in sorted_levels:
        count = heading_counts[level]
        if 2 <= count <= 15:
            return level
    
    # 策略2: 如果最高级别只有 1 个，选择下一级别
    for level in sorted_levels:
        count = heading_counts[level]
        if count >= 2:
            return level
    
    # 策略3: 返回最高级别
    return sorted_levels[0] if sorted_levels else 1


def parse_markdown_by_level(content: str, level: int) -> List[Tuple[str, str]]:
    """
    按指定级别的标题拆分 Markdown 内容
    
    Args:
        content: Markdown 文件内容
        level: 拆分的标题级别 (1-6)
        
    Returns:
        列表，每个元素为 (标题, 内容) 的元组
    """
    sections = []
    
    # 移除代码块
    content_without_code, code_blocks = remove_code_blocks(content)
    
    # 构建匹配指定级别标题的正则表达式
    heading_pattern = re.compile(rf'^{"#" * level} +(.+)$', re.MULTILINE)
    
    # 找到所有指定级别标题的位置
    matches = list(heading_pattern.finditer(content_without_code))
    
    if not matches:
        # 没有找到指定级别的标题，返回整个内容
        return [("全文", content.strip())]
    
    # 处理第一个标题之前的内容（前言）
    first_match = matches[0]
    if first_match.start() > 0:
        preamble = content_without_code[:first_match.start()].strip()
        if preamble:
            sections.append(("前言", restore_code_blocks(preamble, code_blocks)))
    
    # 处理每个标题及其内容
    for i, match in enumerate(matches):
        title = match.group(1).strip()
        
        # 确定内容的结束位置
        if i < len(matches) - 1:
            end_pos = matches[i + 1].start()
        else:
            end_pos = len(content_without_code)
        
        # 提取内容（包含标题行）
        section_content = content_without_code[match.start():end_pos].strip()
        
        if section_content:
            # 还原代码块
            section_content = restore_code_blocks(section_content, code_blocks)
            sections.append((title, section_content))
    
    return sections


def split_markdown(
    input_file: str,
    output_dir: str = None,
    prefix: str = "",
    level: int = None,
    generate_index: bool = True
) -> List[str]:
    """
    将 Markdown 文件按标题拆分成多个文件
    
    Args:
        input_file: 输入文件路径
        output_dir: 输出目录，默认为输入文件所在目录下的 split_output/
        prefix: 输出文件名前缀
        level: 拆分的标题级别 (1-6)，None 表示自动检测
        generate_index: 是否生成索引文件
        
    Returns:
        生成的文件路径列表
        
    Raises:
        FileNotFoundError: 输入文件不存在
        IOError: 读写文件失败
    """
    input_path = Path(input_file)
    
    if not input_path.exists():
        raise FileNotFoundError(f"输入文件不存在: {input_file}")
    
    # 确定输出目录
    if output_dir is None:
        output_path = input_path.parent / "split_output"
    else:
        output_path = Path(output_dir)
    
    # 创建输出目录
    output_path.mkdir(parents=True, exist_ok=True)
    
    # 读取输入文件
    print(f"正在读取文件: {input_file}", file=sys.stderr)
    with open(input_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 移除代码块后分析结构
    content_without_code, _ = remove_code_blocks(content)
    heading_counts = analyze_heading_structure(content_without_code)
    
    # 显示文档结构
    print(f"文档标题结构分析:", file=sys.stderr)
    for lvl in sorted(heading_counts.keys()):
        print(f"  H{lvl} ({'#' * lvl}): {heading_counts[lvl]} 个", file=sys.stderr)
    
    # 确定拆分级别
    if level is None:
        level = detect_split_level(heading_counts)
        print(f"自动检测拆分级别: H{level} ({'#' * level})", file=sys.stderr)
    else:
        print(f"使用指定拆分级别: H{level} ({'#' * level})", file=sys.stderr)
    
    # 解析并拆分
    sections = parse_markdown_by_level(content, level)
    
    if not sections:
        print("警告: 文件内容为空或无法解析", file=sys.stderr)
        return []
    
    print(f"找到 {len(sections)} 个章节", file=sys.stderr)
    
    # 写入拆分后的文件
    generated_files = []
    index_entries = []
    
    for i, (title, section_content) in enumerate(sections):
        # 生成文件名
        clean_title = sanitize_filename(title)
        filename = f"{prefix}{i:02d}_{clean_title}.md"
        filepath = output_path / filename
        
        # 写入文件
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(section_content)
            f.write('\n')
        
        generated_files.append(str(filepath))
        index_entries.append((filename, title))
        print(f"  已生成: {filename}", file=sys.stderr)
    
    # 生成索引文件
    if generate_index and len(generated_files) > 1:
        index_path = output_path / "index.md"
        with open(index_path, 'w', encoding='utf-8') as f:
            f.write("# 目录\n\n")
            # f.write(f"本文档由 `{input_path.name}` 拆分生成，共 {len(sections)} 个章节。\n\n")
            for filename, title in index_entries:
                f.write(f"- [{title}]({filename})\n")
            f.write('\n')
        
        generated_files.insert(0, str(index_path))
        print(f"  已生成索引: index.md", file=sys.stderr)
    
    return generated_files


def main():
    parser = argparse.ArgumentParser(
        description="智能拆分 Markdown 文档（自动检测或手动指定拆分级别）",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
示例:
  # 自动检测拆分级别
  %(prog)s docs/guide.md
  
  # 指定输出目录
  %(prog)s docs/guide.md --output-dir ./chapters
  
  # 手动指定按二级标题拆分
  %(prog)s docs/guide.md --level 2
  
  # 按三级标题拆分
  %(prog)s docs/guide.md -l 3
  
  # 添加文件名前缀
  %(prog)s docs/guide.md -o ./chapters -p "chapter_"
  
  # 不生成索引文件
  %(prog)s docs/guide.md --no-index
        """
    )
    
    parser.add_argument("input_file", help="输入的 Markdown 文件路径")
    parser.add_argument(
        "--output-dir", "-o",
        help="输出目录，默认为输入文件所在目录下的 split_output/"
    )
    parser.add_argument(
        "--level", "-l",
        type=int,
        choices=[1, 2, 3, 4, 5, 6],
        help="拆分的标题级别 (1-6)，不指定则自动检测"
    )
    parser.add_argument(
        "--prefix", "-p",
        default="",
        help="输出文件名前缀，默认为空"
    )
    parser.add_argument(
        "--index", "-i",
        action="store_true",
        default=True,
        help="生成索引文件 index.md（默认行为）"
    )
    parser.add_argument(
        "--no-index",
        action="store_true",
        help="不生成索引文件"
    )
    
    args = parser.parse_args()
    
    try:
        generate_index = not args.no_index
        
        generated_files = split_markdown(
            input_file=args.input_file,
            output_dir=args.output_dir,
            prefix=args.prefix,
            level=args.level,
            generate_index=generate_index
        )
        
        print(f"\n拆分完成！共生成 {len(generated_files)} 个文件:", file=sys.stderr)
        for filepath in generated_files:
            print(f"  - {filepath}")
        
        return 0
        
    except FileNotFoundError as e:
        print(f"错误: {e}", file=sys.stderr)
        return 1
    except Exception as e:
        print(f"未预期的错误: {e}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
