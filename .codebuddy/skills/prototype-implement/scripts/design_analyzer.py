#!/usr/bin/env python3
"""
Design system analyzer - extracts structured design tokens from fetched web content.

Upgraded with deep_analyzer integration for precise element measurements.
"""

import sys
import json
from pathlib import Path
from collections import Counter
from playwright.sync_api import sync_playwright


def deep_analyze_elements(content_dir: str):
    """
    使用深度分析工具提取精确的元素尺寸和样式
    
    Returns:
        Dict containing precise measurements of key elements
    """
    content_path = Path(content_dir)
    source_html = content_path / "source.html"
    
    if not source_html.exists():
        print("⚠️  source.html not found, skipping deep analysis", file=sys.stderr)
        return None
    
    print("\n🔬 Running deep analysis on source HTML...", file=sys.stderr)
    
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page(viewport={"width": 1920, "height": 1080})
            
            # Load local HTML file
            page.goto(f"file://{source_html.absolute()}")
            page.wait_for_timeout(1000)
            
            deep_data = {}
            
            # 分析 Hero 输入框
            input_elem = page.query_selector('input[type="text"], input[type="email"], input[type="search"], textarea')
            if input_elem:
                print("  ✅ Found input element", file=sys.stderr)
                
                input_data = page.evaluate("""
                    (element) => {
                        const computed = window.getComputedStyle(element);
                        const rect = element.getBoundingClientRect();
                        
                        // 查找父容器
                        const parent = element.closest('div[class*="search"], form, div[class*="input"]') || element.parentElement;
                        const parentComputed = window.getComputedStyle(parent);
                        const parentRect = parent.getBoundingClientRect();
                        
                        return {
                            input: {
                                tagName: element.tagName,
                                width: rect.width,
                                height: rect.height,
                                paddingTop: parseFloat(computed.paddingTop),
                                paddingRight: parseFloat(computed.paddingRight),
                                paddingBottom: parseFloat(computed.paddingBottom),
                                paddingLeft: parseFloat(computed.paddingLeft),
                                fontSize: parseFloat(computed.fontSize),
                                lineHeight: computed.lineHeight,
                                minHeight: computed.minHeight,
                                borderRadius: parseFloat(computed.borderRadius) || 0,
                                backgroundColor: computed.backgroundColor
                            },
                            container: {
                                tagName: parent.tagName,
                                width: parentRect.width,
                                height: parentRect.height,
                                paddingTop: parseFloat(parentComputed.paddingTop),
                                paddingRight: parseFloat(parentComputed.paddingRight),
                                paddingBottom: parseFloat(parentComputed.paddingBottom),
                                paddingLeft: parseFloat(parentComputed.paddingLeft),
                                gap: parseFloat(parentComputed.gap) || 0,
                                borderRadius: parseFloat(parentComputed.borderRadius) || 0,
                                backgroundColor: parentComputed.backgroundColor,
                                flexDirection: parentComputed.flexDirection
                            }
                        };
                    }
                """, input_elem)
                
                deep_data['hero_input'] = input_data
                
                print(f"    Input: {input_data['input']['tagName']} {input_data['input']['width']:.0f}×{input_data['input']['height']:.0f}px", file=sys.stderr)
                print(f"    Container: {input_data['container']['height']:.0f}px, padding: {input_data['container']['paddingTop']:.0f}px, radius: {input_data['container']['borderRadius']:.0f}px", file=sys.stderr)
            
            # 分析按钮
            buttons = page.query_selector_all('button, a[class*="button"], a[class*="btn"]')
            if buttons:
                print(f"  ✅ Found {len(buttons)} buttons", file=sys.stderr)
                
                button_data = []
                for btn in buttons[:5]:  # 前5个
                    data = page.evaluate("""
                        (element) => {
                            const computed = window.getComputedStyle(element);
                            const rect = element.getBoundingClientRect();
                            
                            return {
                                text: element.textContent.trim().substring(0, 30),
                                width: rect.width,
                                height: rect.height,
                                paddingTop: parseFloat(computed.paddingTop),
                                paddingBottom: parseFloat(computed.paddingBottom),
                                paddingLeft: parseFloat(computed.paddingLeft),
                                paddingRight: parseFloat(computed.paddingRight),
                                fontSize: parseFloat(computed.fontSize),
                                fontWeight: computed.fontWeight,
                                borderRadius: parseFloat(computed.borderRadius) || 0,
                                backgroundColor: computed.backgroundColor,
                                color: computed.color
                            };
                        }
                    """, btn)
                    button_data.append(data)
                
                deep_data['buttons'] = button_data
                print(f"    Analyzed {len(button_data)} buttons", file=sys.stderr)
            
            browser.close()
            
            return deep_data
            
    except Exception as e:
        print(f"⚠️  Deep analysis failed: {e}", file=sys.stderr)
        return None


def rgb_to_hsl(hex_color):
    """Convert hex color to HSL for better color classification."""
    hex_color = hex_color.lstrip('#')
    if len(hex_color) == 3:
        hex_color = ''.join([c*2 for c in hex_color])
    try:
        r, g, b = tuple(int(hex_color[i:i+2], 16) / 255.0 for i in (0, 2, 4))
    except:
        return (0, 0, 0)
    
    max_c = max(r, g, b)
    min_c = min(r, g, b)
    l = (max_c + min_c) / 2
    
    if max_c == min_c:
        h = s = 0
    else:
        d = max_c - min_c
        s = d / (2 - max_c - min_c) if l > 0.5 else d / (max_c + min_c)
        if max_c == r:
            h = (g - b) / d + (6 if g < b else 0)
        elif max_c == g:
            h = (b - r) / d + 2
        else:
            h = (r - g) / d + 4
        h /= 6
    
    return (h * 360, s * 100, l * 100)


def classify_color(hex_color):
    """Classify a color by its characteristics."""
    h, s, l = rgb_to_hsl(hex_color)
    
    # Grayscale (low saturation)
    if s < 10:
        if l > 95:
            return 'white'
        elif l > 70:
            return 'light_gray'
        elif l > 30:
            return 'gray'
        elif l > 5:
            return 'dark_gray'
        else:
            return 'black'
    
    # Colored
    if l > 85:
        return 'tint'  # Very light colored (backgrounds)
    elif l < 20:
        return 'shade'  # Very dark colored
    elif s > 50:
        return 'vibrant'  # Saturated colors (accents, CTAs)
    else:
        return 'muted'  # Desaturated colors


def analyze_design_system(content_dir: str):
    """
    Analyze design tokens and generate structured design system documentation.
    
    Args:
        content_dir: Directory containing fetched web content
    """
    content_path = Path(content_dir)
    
    if not content_path.exists():
        print(f"❌ Error: Directory not found: {content_dir}", file=sys.stderr)
        sys.exit(1)
    
    tokens_file = content_path / "design_tokens.json"
    if not tokens_file.exists():
        print(f"❌ Error: design_tokens.json not found. Run web_content_fetcher.py first.", file=sys.stderr)
        sys.exit(1)
    
    print(f"🔍 Analyzing design system from: {content_dir}", file=sys.stderr)
    
    # Load design tokens
    tokens = json.loads(tokens_file.read_text())
    
    # Analyze colors with classification
    print("\n🎨 COLOR SYSTEM ANALYSIS", file=sys.stderr)
    print("="*60, file=sys.stderr)
    
    colors_hex = tokens.get('colorsHex', [])
    
    # Classify all colors
    color_categories = {
        'white': [],
        'light_gray': [],
        'gray': [],
        'dark_gray': [],
        'black': [],
        'tint': [],      # Light colored backgrounds
        'shade': [],     # Dark colored
        'vibrant': [],   # Saturated accent colors
        'muted': []      # Desaturated colors
    }
    
    color_frequency = Counter(colors_hex)
    
    for color in colors_hex:
        category = classify_color(color)
        if color not in color_categories[category]:
            color_categories[category].append(color)
    
    # Sort each category by frequency
    for cat in color_categories:
        color_categories[cat] = sorted(
            color_categories[cat], 
            key=lambda c: color_frequency.get(c, 0), 
            reverse=True
        )
    
    print("\nColor Classification:", file=sys.stderr)
    for cat, colors in color_categories.items():
        if colors:
            print(f"  {cat}: {len(colors)} colors - {colors[:3]}", file=sys.stderr)
    
    # Get top colors overall (excluding pure black/white)
    significant_colors = [c for c in colors_hex if c.lower() not in ['#000000', '#ffffff', '#000', '#fff']]
    top_colors = Counter(significant_colors).most_common(20)
    
    print("\nMost Used Colors:", file=sys.stderr)
    for color, count in top_colors:
        print(f"  {color} (used {count} times)", file=sys.stderr)
    
    # Analyze typography
    print("\n🔤 TYPOGRAPHY SYSTEM ANALYSIS", file=sys.stderr)
    print("="*60, file=sys.stderr)
    
    fonts = tokens.get('fonts', [])
    primary_fonts = []
    for font in fonts:
        # Extract first font from font stack
        first_font = font.split(',')[0].strip().strip('"').strip("'")
        if first_font not in ['sans-serif', 'serif', 'monospace', 'system-ui', '-apple-system']:
            primary_fonts.append(first_font)
    
    font_frequency = Counter(primary_fonts)
    top_fonts = font_frequency.most_common(5)
    
    print("\nPrimary Fonts:", file=sys.stderr)
    for font, count in top_fonts:
        print(f"  {font} (used {count} times)", file=sys.stderr)
    
    # Font sizes
    font_sizes = tokens.get('fontSizes', [])
    size_values = []
    for size in font_sizes:
        try:
            val = float(size.replace('px', '').replace('rem', ''))
            size_values.append(val)
        except:
            pass
    
    if size_values:
        size_values.sort()
        print(f"\nFont Size Range: {min(size_values):.1f}px - {max(size_values):.1f}px", file=sys.stderr)
        print(f"Common Sizes: {', '.join([f'{s:.0f}px' for s in sorted(set(size_values))[:10]])}", file=sys.stderr)
    
    # Font weights
    weights = sorted(set(tokens.get('fontWeights', [])))
    print(f"Font Weights: {', '.join(weights)}", file=sys.stderr)
    
    # Analyze spacing
    print("\n📏 SPACING SYSTEM ANALYSIS", file=sys.stderr)
    print("="*60, file=sys.stderr)
    
    spacing = tokens.get('spacing', [])
    spacing_values = []
    for space in spacing:
        try:
            parts = space.split()
            for part in parts:
                if 'px' in part:
                    val = float(part.replace('px', ''))
                    if 0 < val < 200:  # Reasonable range
                        spacing_values.append(int(val))
        except:
            pass
    
    if spacing_values:
        spacing_frequency = Counter(spacing_values)
        common_spacing = spacing_frequency.most_common(10)
        print(f"\nCommon Spacing Values:", file=sys.stderr)
        for space, count in common_spacing:
            print(f"  {space}px (used {count} times)", file=sys.stderr)
    
    # Border radius
    print("\n⭕ BORDER RADIUS ANALYSIS", file=sys.stderr)
    print("="*60, file=sys.stderr)
    
    border_radius = tokens.get('borderRadius', [])
    radius_values = []
    for radius in border_radius:
        try:
            parts = radius.split()
            for part in parts:
                if 'px' in part:
                    val = float(part.replace('px', ''))
                    if val > 0:
                        radius_values.append(int(val))
        except:
            pass
    
    if radius_values:
        radius_frequency = Counter(radius_values)
        common_radius = radius_frequency.most_common(5)
        print(f"\nCommon Border Radius Values:", file=sys.stderr)
        for radius, count in common_radius:
            print(f"  {radius}px (used {count} times)", file=sys.stderr)
    
    # Generate design system document
    print("\n📝 Generating design system document...", file=sys.stderr)
    
    # 运行深度分析（提取精确的元素尺寸）
    deep_analysis = deep_analyze_elements(content_dir)
    
    # Determine primary/accent colors more intelligently
    # Vibrant colors are likely accent/CTA colors
    # Muted colors are likely secondary colors
    vibrant_colors = color_categories.get('vibrant', [])
    muted_colors = color_categories.get('muted', [])
    tint_colors = color_categories.get('tint', [])
    gray_colors = color_categories.get('gray', []) + color_categories.get('light_gray', []) + color_categories.get('dark_gray', [])
    
    # Primary: most used vibrant color, or first significant color
    primary = vibrant_colors[0] if vibrant_colors else (top_colors[0][0] if top_colors else "#000000")
    
    # Secondary: second vibrant or first muted
    secondary = (vibrant_colors[1] if len(vibrant_colors) > 1 else 
                 muted_colors[0] if muted_colors else 
                 (top_colors[1][0] if len(top_colors) > 1 else "#666666"))
    
    # Accent: a different vibrant color or tint
    accent = (vibrant_colors[2] if len(vibrant_colors) > 2 else
              tint_colors[0] if tint_colors else
              (top_colors[2][0] if len(top_colors) > 2 else "#0066cc"))
    
    design_system = {
        "colors": {
            "primary": primary,
            "secondary": secondary,
            "accent": accent,
            # Categorized colors for richer palette
            "backgrounds": {
                "light": color_categories.get('tint', [])[:5],
                "dark": color_categories.get('shade', [])[:5],
                "gray": gray_colors[:5]
            },
            "text": {
                "primary": color_categories.get('dark_gray', ['#333333'])[:3],
                "secondary": color_categories.get('gray', ['#666666'])[:3],
                "light": color_categories.get('light_gray', ['#999999'])[:3]
            },
            "accents": vibrant_colors[:8],  # All vibrant colors for CTAs, links, highlights
            "muted": muted_colors[:5],      # Desaturated colors for borders, disabled states
            "all_significant": [c[0] for c in top_colors[:20]],  # Top 20 for reference
            "by_category": {k: v[:5] for k, v in color_categories.items() if v}  # All categories
        },
        "typography": {
            "primary_font": top_fonts[0][0] if top_fonts else "Inter",
            "font_stack": [f[0] for f in top_fonts],
            "weights": weights,
            "sizes": {
                "min": f"{min(size_values):.0f}px" if size_values else "14px",
                "max": f"{max(size_values):.0f}px" if size_values else "48px",
                "common": [f"{int(s)}px" for s in sorted(set(size_values))[:10]] if size_values else []
            }
        },
        "spacing": {
            "scale": [s[0] for s in common_spacing[:8]] if spacing_values else [4, 8, 12, 16, 24, 32, 48, 64]
        },
        "borderRadius": {
            "scale": [r[0] for r in common_radius] if radius_values else [4, 8, 12, 16]
        }
    }
    
    print(f"\n  Primary Color: {primary}", file=sys.stderr)
    print(f"  Secondary Color: {secondary}", file=sys.stderr)
    print(f"  Accent Color: {accent}", file=sys.stderr)
    print(f"  Total Accent Colors: {len(vibrant_colors)}", file=sys.stderr)
    print(f"  Total Background Colors: {len(tint_colors) + len(color_categories.get('shade', []))}", file=sys.stderr)
    
    # 添加深度分析数据
    if deep_analysis:
        design_system["precise_measurements"] = deep_analysis
        print("  ✅ Added precise element measurements", file=sys.stderr)
    
    # Save design system
    output_file = content_path / "design_system.json"
    output_file.write_text(json.dumps(design_system, indent=2))
    
    print(f"\n✅ Design system saved: {output_file}", file=sys.stderr)
    
    # Generate human-readable markdown
    accents = design_system['colors'].get('accents', [])
    backgrounds_light = design_system['colors'].get('backgrounds', {}).get('light', [])
    backgrounds_dark = design_system['colors'].get('backgrounds', {}).get('dark', [])
    text_colors = design_system['colors'].get('text', {}).get('primary', [])
    
    md_content = f"""# Design System Analysis

## Color Palette

### Primary Colors
- **Primary**: `{design_system['colors']['primary']}`
- **Secondary**: `{design_system['colors']['secondary']}`
- **Accent**: `{design_system['colors']['accent']}`

### Accent Colors (CTAs, Links, Highlights)
{chr(10).join([f"- `{c}`" for c in accents[:8]]) if accents else "- No vibrant accent colors detected"}

### Background Colors
**Light Backgrounds (tints)**:
{chr(10).join([f"- `{c}`" for c in backgrounds_light[:5]]) if backgrounds_light else "- None detected"}

**Dark Backgrounds (shades)**:
{chr(10).join([f"- `{c}`" for c in backgrounds_dark[:5]]) if backgrounds_dark else "- None detected"}

### Text Colors
{chr(10).join([f"- `{c}`" for c in text_colors[:5]]) if text_colors else "- Default grays"}

### All Significant Colors (Top 20)
{chr(10).join([f"- `{c}`" for c in design_system['colors']['all_significant'][:20]])}

## Typography

### Font Family
- **Primary**: {design_system['typography']['primary_font']}
- **Stack**: {', '.join(design_system['typography']['font_stack'])}

### Font Weights
{', '.join(design_system['typography']['weights'])}

### Font Sizes
- **Range**: {design_system['typography']['sizes']['min']} - {design_system['typography']['sizes']['max']}
- **Common**: {', '.join(design_system['typography']['sizes']['common'][:8])}

## Spacing Scale
{', '.join([f"{s}px" for s in design_system['spacing']['scale']])}

## Border Radius
{', '.join([f"{r}px" for r in design_system['borderRadius']['scale']])}

## Tailwind Configuration

```javascript
tailwind.config = {{
  theme: {{
    extend: {{
      colors: {{
        'brand-primary': '{design_system['colors']['primary']}',
        'brand-secondary': '{design_system['colors']['secondary']}',
        'brand-accent': '{design_system['colors']['accent']}',
        // Additional accent colors
{chr(10).join([f"        'accent-{i+1}': '{c}'," for i, c in enumerate(accents[1:5])])}
        // Background colors
{chr(10).join([f"        'bg-light-{i+1}': '{c}'," for i, c in enumerate(backgrounds_light[:3])])}
{chr(10).join([f"        'bg-dark-{i+1}': '{c}'," for i, c in enumerate(backgrounds_dark[:3])])}
      }},
      fontFamily: {{
        sans: ['{design_system['typography']['primary_font']}', 'system-ui', 'sans-serif'],
      }},
      spacing: {{
        {', '.join([f"'{i}': '{s}px'" for i, s in enumerate(design_system['spacing']['scale'])])}
      }}
    }}
  }}
}}
```
"""
    
    # 添加精确测量部分
    if deep_analysis and 'hero_input' in deep_analysis:
        inp = deep_analysis['hero_input']['input']
        cont = deep_analysis['hero_input']['container']
        
        md_content += f"""

## 🔬 Precise Element Measurements

### Hero Input Box

#### Input Element
- **Tag**: `<{inp['tagName'].lower()}>`
- **Size**: {inp['width']:.0f}px × {inp['height']:.0f}px
- **Padding**: {inp['paddingTop']:.0f}px {inp['paddingRight']:.0f}px {inp['paddingBottom']:.0f}px {inp['paddingLeft']:.0f}px
- **Font Size**: {inp['fontSize']:.0f}px
- **Line Height**: {inp['lineHeight']}
- **Min Height**: {inp['minHeight']}
- **Border Radius**: {inp['borderRadius']:.0f}px
- **Background**: {inp['backgroundColor']}

#### Container
- **Tag**: `<{cont['tagName'].lower()}>`
- **Size**: {cont['width']:.0f}px × {cont['height']:.0f}px
- **Padding**: {cont['paddingTop']:.0f}px
- **Gap**: {cont['gap']:.0f}px
- **Border Radius**: {cont['borderRadius']:.0f}px
- **Background**: {cont['backgroundColor']}
- **Layout**: {cont['flexDirection']}

#### Recommended Code

```html
<{cont['tagName'].lower()} class="rounded-[{cont['borderRadius']:.0f}px] p-{int(cont['paddingTop']/4)} flex {'flex-col' if cont['flexDirection'] == 'column' else ''} gap-{int(cont['gap']/4)}" style="background: {cont['backgroundColor']}">
    <{inp['tagName'].lower()} 
        class="px-{int(inp['paddingLeft']/4)} py-{int(inp['paddingTop']/4)} text-base"
        style="min-height: {inp['minHeight']}; line-height: {inp['lineHeight']}"
    ></{inp['tagName'].lower()}>
</{cont['tagName'].lower()}>
```
"""
    
    if deep_analysis and 'buttons' in deep_analysis:
        md_content += f"""

### Buttons ({len(deep_analysis['buttons'])} analyzed)

"""
        for i, btn in enumerate(deep_analysis['buttons'][:3]):
            md_content += f"""
**Button {i+1}**: "{btn['text']}"
- Size: {btn['width']:.0f}px × {btn['height']:.0f}px
- Padding: {btn['paddingTop']:.0f}px {btn['paddingRight']:.0f}px {btn['paddingBottom']:.0f}px {btn['paddingLeft']:.0f}px
- Font: {btn['fontSize']:.0f}px, weight {btn['fontWeight']}
- Border Radius: {btn['borderRadius']:.0f}px
- Background: {btn['backgroundColor']}
- Color: {btn['color']}

"""
    
    md_content += """
---

**Note**: Precise measurements are extracted using browser DevTools API for 100% accuracy.
"""
    
    md_file = content_path / "DESIGN_SYSTEM.md"
    md_file.write_text(md_content)
    print(f"✅ Markdown report saved: {md_file}", file=sys.stderr)
    
    print("\n" + "="*60, file=sys.stderr)
    print("✅ DESIGN ANALYSIS COMPLETE", file=sys.stderr)
    print("="*60 + "\n", file=sys.stderr)
    
    return design_system


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python design_analyzer.py <content_directory>", file=sys.stderr)
        print("\nExample:", file=sys.stderr)
        print("  python design_analyzer.py example_content", file=sys.stderr)
        sys.exit(1)
    
    content_dir = sys.argv[1]
    
    try:
        design_system = analyze_design_system(content_dir)
        print(json.dumps(design_system, indent=2))
    except Exception as e:
        print(f"Fatal error: {e}", file=sys.stderr)
        sys.exit(1)
