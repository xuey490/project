#!/usr/bin/env python3
"""
CSS Merger Script for Mode 1 URL Clone
合并提取的 CSS 文件并生成 original.css

⚠️ IMPORTANT: 过滤第三方 UI 库样式，避免与 Tailwind/shadcn 冲突

Usage:
    python3 scripts/css_merger.py <project>_content/css/ frontend/src/styles/original.css

Output:
    - Merged CSS file with filtered original styles
    - Validation report showing what was included/excluded
"""

import os
import sys
import re
from pathlib import Path


# =============================================================================
# 第三方 UI 库样式过滤规则
# 这些样式会与 Tailwind/shadcn-ui 冲突，必须过滤掉
# =============================================================================

# TDesign UI 库 (腾讯)
TDESIGN_PATTERNS = [
    r'\.t-[a-zA-Z]',           # .t-button, .t-dialog, etc.
    r'var\(--td-[^)]+\)',      # --td-brand-color, --td-font-*, etc.
]

# Ant Design (阿里)
ANTD_PATTERNS = [
    r'\.ant-[a-zA-Z]',         # .ant-btn, .ant-modal, etc.
    r'\.anticon',              # .anticon-*
]

# Element UI (饿了么)
ELEMENT_PATTERNS = [
    r'\.el-[a-zA-Z]',          # .el-button, .el-dialog, etc.
]

# Arco Design (字节)
ARCO_PATTERNS = [
    r'\.arco-[a-zA-Z]',        # .arco-btn, etc.
]

# 需要过滤的 CSS 规则模式
FILTER_PATTERNS = TDESIGN_PATTERNS + ANTD_PATTERNS + ELEMENT_PATTERNS + ARCO_PATTERNS


def should_filter_rule(css_rule: str) -> bool:
    """检查 CSS 规则是否应该被过滤"""
    for pattern in FILTER_PATTERNS:
        if re.search(pattern, css_rule):
            return True
    return False


def filter_css_content(content: str) -> tuple[str, dict]:
    """
    过滤 CSS 内容，移除第三方 UI 库样式
    
    Returns:
        tuple: (过滤后的内容, 统计信息)
    """
    stats = {
        'total_rules': 0,
        'filtered_rules': 0,
        'kept_rules': 0,
        'filtered_libraries': set(),
    }
    
    # 分割成规则块（简化处理，按 } 分割）
    # 注意：这是简化处理，对于嵌套的 @media 等可能不完美
    lines = content.split('\n')
    filtered_lines = []
    current_block = []
    brace_count = 0
    in_filtered_block = False
    
    for line in lines:
        # 检查是否进入第三方库的样式块
        if brace_count == 0 and should_filter_rule(line):
            in_filtered_block = True
            stats['filtered_rules'] += 1
            # 识别是哪个库
            if re.search(r'\.t-|--td-', line):
                stats['filtered_libraries'].add('TDesign')
            elif re.search(r'\.ant-|\.anticon', line):
                stats['filtered_libraries'].add('AntDesign')
            elif re.search(r'\.el-', line):
                stats['filtered_libraries'].add('ElementUI')
            elif re.search(r'\.arco-', line):
                stats['filtered_libraries'].add('ArcoDesign')
        
        # 计算花括号
        brace_count += line.count('{') - line.count('}')
        
        if in_filtered_block:
            # 跳过这个块
            if brace_count <= 0:
                in_filtered_block = False
                brace_count = 0
        else:
            filtered_lines.append(line)
            if '{' in line or '}' in line:
                stats['kept_rules'] += 1
    
    stats['total_rules'] = stats['filtered_rules'] + stats['kept_rules']
    
    return '\n'.join(filtered_lines), stats


def extract_font_faces(css_content: str) -> list[str]:
    """Extract @font-face rules"""
    pattern = r'@font-face\s*\{[^}]+\}'
    return re.findall(pattern, css_content, re.DOTALL)


def extract_keyframes(css_content: str) -> list[str]:
    """Extract @keyframes rules"""
    full_pattern = r'@(-webkit-)?keyframes\s+[\w-]+\s*\{(?:[^{}]|\{[^{}]*\})*\}'
    return re.findall(full_pattern, css_content, re.DOTALL)


def extract_css_variables(css_content: str) -> list[str]:
    """Extract CSS custom properties (variables)"""
    pattern = r':root\s*\{[^}]+\}'
    return re.findall(pattern, css_content, re.DOTALL)


def extract_class_names(css_content: str) -> set[str]:
    """Extract all class names from CSS"""
    pattern = r'\.([a-zA-Z_-][a-zA-Z0-9_-]*)'
    return set(re.findall(pattern, css_content))


def extract_custom_styles(css_content: str) -> str:
    """
    从 CSS 内容中提取自定义样式，过滤第三方库
    优先保留：
    - 页面特定类 (.hero-*, .header, .footer, .nav-*, .cta-*, etc.)
    - @font-face
    - @keyframes (非 t-* 前缀)
    - @media 查询中的自定义样式
    """
    filtered_content, _ = filter_css_content(css_content)
    return filtered_content


def merge_css_files(css_dir: str, output_file: str, filter_third_party: bool = True) -> dict:
    """
    Merge all CSS files from directory into single output file
    
    Args:
        css_dir: Source CSS directory
        output_file: Output file path
        filter_third_party: Whether to filter third-party UI library styles
    
    Returns:
        dict: Statistics about what was merged
    """
    css_path = Path(css_dir)
    output_path = Path(output_file)
    
    if not css_path.exists():
        print(f"Error: CSS directory not found: {css_dir}")
        sys.exit(1)
    
    # Ensure output directory exists
    output_path.parent.mkdir(parents=True, exist_ok=True)
    
    # Collect all CSS files
    css_files = sorted(css_path.glob('*.css'))
    
    if not css_files:
        print(f"Warning: No CSS files found in {css_dir}")
        sys.exit(1)
    
    stats = {
        'files': len(css_files),
        'total_size': 0,
        'font_faces': 0,
        'keyframes': 0,
        'classes': set(),
        'variables': 0,
        'filtered_libraries': set(),
        'filtered_rules': 0,
    }
    
    merged_content = []
    merged_content.append("/* =========================================== */")
    merged_content.append("/* ORIGINAL CSS - Auto-merged from source site */")
    merged_content.append("/* Third-party UI library styles FILTERED OUT  */")
    merged_content.append("/* Use these class names in React components   */")
    merged_content.append("/* =========================================== */\n")
    
    all_font_faces = []
    all_keyframes = []
    all_variables = []
    
    for css_file in css_files:
        file_size = css_file.stat().st_size
        stats['total_size'] += file_size
        
        content = css_file.read_text(encoding='utf-8', errors='ignore')
        
        # Filter third-party styles if enabled
        if filter_third_party:
            content, filter_stats = filter_css_content(content)
            stats['filtered_libraries'].update(filter_stats['filtered_libraries'])
            stats['filtered_rules'] += filter_stats['filtered_rules']
        
        # Extract special rules from filtered content
        font_faces = extract_font_faces(content)
        keyframes = extract_keyframes(content)
        variables = extract_css_variables(content)
        classes = extract_class_names(content)
        
        all_font_faces.extend(font_faces)
        all_keyframes.extend(keyframes)
        all_variables.extend(variables)
        stats['classes'].update(classes)
        
        # Only add if there's content after filtering
        if content.strip():
            merged_content.append(f"\n/* === Source: {css_file.name} (original: {file_size} bytes) === */\n")
            merged_content.append(content)
    
    stats['font_faces'] = len(all_font_faces)
    stats['keyframes'] = len(all_keyframes)
    stats['variables'] = len(all_variables)
    
    # Write merged content
    final_content = '\n'.join(merged_content)
    output_path.write_text(final_content, encoding='utf-8')
    
    output_size = output_path.stat().st_size
    
    return {
        **stats,
        'output_size': output_size,
        'classes_count': len(stats['classes']),
    }


def print_report(stats: dict, css_dir: str, output_file: str):
    """Print validation report"""
    print("\n" + "=" * 60)
    print("CSS MERGER REPORT")
    print("=" * 60)
    print(f"\nSource Directory: {css_dir}")
    print(f"Output File: {output_file}")
    print("-" * 60)
    
    print(f"\n[Files Processed]")
    print(f"  CSS files merged: {stats['files']}")
    print(f"  Total input size: {stats['total_size']:,} bytes ({stats['total_size']/1024:.1f} KB)")
    print(f"  Output file size: {stats['output_size']:,} bytes ({stats['output_size']/1024:.1f} KB)")
    
    # Filtering info
    if stats['filtered_libraries']:
        print(f"\n[Third-Party Libraries FILTERED OUT]")
        for lib in sorted(stats['filtered_libraries']):
            print(f"  - {lib} (styles removed to avoid conflicts)")
        print(f"  Total rules filtered: {stats['filtered_rules']}")
    
    print(f"\n[CSS Features Extracted]")
    print(f"  @font-face rules: {stats['font_faces']}")
    print(f"  @keyframes animations: {stats['keyframes']}")
    print(f"  CSS variables (:root): {stats['variables']}")
    print(f"  Unique class names: {stats['classes_count']}")
    
    # Validation
    print(f"\n[Validation]")
    
    issues = []
    # 过滤后文件可能较小，调整阈值
    min_size = 5000 if stats['filtered_libraries'] else 50000
    if stats['output_size'] < min_size:
        issues.append(f"  WARNING: Output size ({stats['output_size']} bytes) seems small")
    if stats['font_faces'] < 1:
        issues.append("  WARNING: No @font-face rules found!")
    
    if issues:
        print("  STATUS: NEEDS REVIEW")
        for issue in issues:
            print(issue)
    else:
        print("  STATUS: PASS")
        print(f"  - Output size OK")
        if stats['filtered_libraries']:
            print(f"  - Third-party styles filtered (no conflicts)")
        print(f"  - Font faces present" if stats['font_faces'] else "")
    
    # Sample class names for reference (exclude filtered patterns)
    if stats['classes']:
        # 过滤掉第三方库的类名
        custom_classes = [c for c in stats['classes'] 
                        if not c.startswith('t-') 
                        and not c.startswith('ant-')
                        and not c.startswith('el-')
                        and not c.startswith('arco-')]
        
        sample_classes = sorted(custom_classes)[:20]
        if sample_classes:
            print(f"\n[Sample Class Names (use these in components)]")
            for cls in sample_classes:
                print(f"  .{cls}")
            if len(custom_classes) > 20:
                print(f"  ... and {len(custom_classes) - 20} more")
    
    print("\n" + "=" * 60)


def main():
    if len(sys.argv) < 3:
        print("Usage: python3 css_merger.py <css_directory> <output_file> [--no-filter]")
        print("Example: python3 scripts/css_merger.py codebuddy_content/css/ frontend/src/styles/original.css")
        print("\nOptions:")
        print("  --no-filter  Don't filter third-party UI library styles")
        sys.exit(1)
    
    css_dir = sys.argv[1]
    output_file = sys.argv[2]
    filter_third_party = '--no-filter' not in sys.argv
    
    print(f"Merging CSS files from: {css_dir}")
    print(f"Output to: {output_file}")
    if filter_third_party:
        print("Filtering: Third-party UI library styles will be removed")
    
    stats = merge_css_files(css_dir, output_file, filter_third_party)
    print_report(stats, css_dir, output_file)
    
    print("\nCSS merge completed successfully!")
    
    if stats['filtered_libraries']:
        print(f"\n⚠️  NOTE: {', '.join(stats['filtered_libraries'])} styles were filtered out.")
        print("   This prevents conflicts with Tailwind CSS and shadcn/ui.")
        print("   If you need these styles, run with --no-filter option.")


if __name__ == "__main__":
    main()
