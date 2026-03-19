#!/usr/bin/env python3
"""
Comprehensive web content fetcher for screenshot-to-code.
Extracts HTML, CSS, images, and design tokens from a target website.

Output format: meta.md + sections/*.md for step-by-step AI processing.
"""

import sys
import json
import base64
from pathlib import Path
from urllib.parse import urljoin, urlparse
import hashlib
import re

try:
    from playwright.sync_api import sync_playwright
except ImportError:
    print("Error: Playwright not installed", file=sys.stderr)
    print("Run: pip install playwright && playwright install chromium", file=sys.stderr)
    sys.exit(1)


def fetch_web_content(url: str, output_dir: str):
    """
    Fetch complete web content including HTML, CSS, images, and design analysis.
    
    Args:
        url: Target URL to fetch
        output_dir: Directory to save fetched resources
    """
    output_path = Path(output_dir)
    output_path.mkdir(parents=True, exist_ok=True)
    
    print(f"🔄 Fetching web content from: {url}", file=sys.stderr)
    
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(viewport={"width": 1920, "height": 1080})
        page = context.new_page()
        
        try:
            # Navigate to the URL with intelligent loading strategy
            print("📡 Loading page...", file=sys.stderr)
            
            # Strategy 1: Try 'load' first (works for 80% of websites)
            try:
                page.goto(url, wait_until="load", timeout=30000)
                print("✅ Loaded with 'load' strategy", file=sys.stderr)
            except Exception as load_error:
                # Strategy 2: Fallback to 'domcontentloaded' (best for SPAs)
                print("⚠️  'load' timeout, falling back to 'domcontentloaded'...", file=sys.stderr)
                page.goto(url, wait_until="domcontentloaded", timeout=30000)
                print("✅ Loaded with 'domcontentloaded' strategy", file=sys.stderr)
            
            # Extra wait for dynamic content and lazy loading
            page.wait_for_timeout(2000)
            
            # Verify content quality
            content_length = len(page.content())
            if content_length < 10000:
                print("⚠️  Content seems incomplete, waiting longer...", file=sys.stderr)
                page.wait_for_timeout(3000)
            
            # 1. Capture full-page screenshot
            print("📸 Capturing screenshot...", file=sys.stderr)
            screenshot_path = output_path / "screenshot.png"
            page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✅ Screenshot saved: {screenshot_path}", file=sys.stderr)
            
            # 2. Extract HTML source
            print("📄 Extracting HTML...", file=sys.stderr)
            html_content = page.content()
            html_path = output_path / "source.html"
            html_path.write_text(html_content, encoding='utf-8')
            print(f"✅ HTML saved: {html_path}", file=sys.stderr)
            
            # 3. Extract all images
            print("🖼️  Extracting images...", file=sys.stderr)
            images = page.evaluate("""
                () => {
                    const images = [];
                    
                    // IMG tags
                    document.querySelectorAll('img').forEach(img => {
                        if (img.src && !img.src.startsWith('data:')) {
                            images.push({
                                type: 'img',
                                src: img.src,
                                alt: img.alt || '',
                                width: img.width,
                                height: img.height
                            });
                        }
                    });
                    
                    // Background images
                    document.querySelectorAll('*').forEach(el => {
                        const bgImage = window.getComputedStyle(el).backgroundImage;
                        if (bgImage && bgImage !== 'none') {
                            const match = bgImage.match(/url\\(["']?([^"')]+)["']?\\)/);
                            if (match && !match[1].startsWith('data:')) {
                                images.push({
                                    type: 'background',
                                    src: match[1],
                                    element: el.tagName
                                });
                            }
                        }
                    });
                    
                    return images;
                }
            """)
            
            images_dir = output_path / "images"
            images_dir.mkdir(exist_ok=True)
            
            downloaded_images = []
            for img_data in images:
                try:
                    img_url = urljoin(url, img_data['src'])
                    response = context.request.get(img_url)
                    
                    if response.ok:
                        img_bytes = response.body()
                        img_hash = hashlib.md5(img_bytes).hexdigest()[:12]
                        ext = Path(urlparse(img_url).path).suffix or '.jpg'
                        img_filename = f"img_{img_hash}{ext}"
                        img_path = images_dir / img_filename
                        img_path.write_bytes(img_bytes)
                        
                        downloaded_images.append({
                            'original_url': img_data['src'],
                            'local_path': str(img_path.relative_to(output_path)),
                            'type': img_data['type'],
                            'metadata': {k: v for k, v in img_data.items() if k not in ['src', 'type']}
                        })
                except Exception as e:
                    print(f"⚠️  Failed to download {img_data['src']}: {e}", file=sys.stderr)
            
            print(f"✅ Downloaded {len(downloaded_images)} images", file=sys.stderr)
            
            # Save image manifest
            manifest_path = output_path / "image_manifest.json"
            manifest_path.write_text(json.dumps(downloaded_images, indent=2), encoding='utf-8')
            print(f"✅ Image manifest saved: {manifest_path}", file=sys.stderr)
            
            # 4. Extract CSS stylesheets (simplified)
            print("💅 Extracting CSS...", file=sys.stderr)
            
            css_dir = output_path / "css"
            css_dir.mkdir(exist_ok=True)
            
            stylesheets = page.evaluate("""
                () => {
                    return Array.from(document.styleSheets)
                        .filter(sheet => sheet.href)
                        .map(sheet => sheet.href);
                }
            """)
            
            for i, css_url in enumerate(stylesheets):
                try:
                    response = context.request.get(css_url)
                    if response.ok:
                        css_content = response.text()
                        css_filename = f"external_{i}.css"
                        (css_dir / css_filename).write_text(css_content, encoding='utf-8')
                except Exception as e:
                    print(f"⚠️  Failed to fetch {css_url}: {e}", file=sys.stderr)
            
            inline_styles = page.evaluate("""
                () => {
                    const styles = [];
                    document.querySelectorAll('style').forEach(style => {
                        if (style.textContent && style.textContent.trim()) {
                            styles.push(style.textContent);
                        }
                    });
                    return styles;
                }
            """)
            
            for i, style_content in enumerate(inline_styles):
                (css_dir / f"inline_{i}.css").write_text(style_content, encoding='utf-8')
            
            print(f"✅ Extracted {len(stylesheets)} external + {len(inline_styles)} inline stylesheets", file=sys.stderr)
            
            # 5. Extract primary theme colors
            print("🎨 Extracting theme colors...", file=sys.stderr)
            theme_colors = page.evaluate("""
                () => {
                    const colors = {
                        dark_bg: null,
                        light_bg: null,
                        accent: null,
                        text_on_dark: null,
                        text_on_light: null
                    };
                    
                    function rgbToHex(rgb) {
                        if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return null;
                        const match = rgb.match(/rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)/);
                        if (!match) return rgb;
                        return '#' + [match[1], match[2], match[3]]
                            .map(x => parseInt(x).toString(16).padStart(2, '0'))
                            .join('');
                    }
                    
                    // Get header background (often dark_bg)
                    const header = document.querySelector('header, [class*="header"], nav');
                    if (header) {
                        const bg = rgbToHex(window.getComputedStyle(header).backgroundColor);
                        if (bg && bg !== '#ffffff' && bg !== '#000000') {
                            colors.dark_bg = bg;
                        }
                        const textColor = rgbToHex(window.getComputedStyle(header.querySelector('a') || header).color);
                        if (textColor) {
                            colors.text_on_dark = textColor;
                        }
                    }
                    
                    // Get first CTA button color (often accent)
                    const buttons = document.querySelectorAll('button, [class*="btn"], [class*="button"], a[class*="cta"]');
                    for (const btn of buttons) {
                        if (btn.offsetWidth > 0 && btn.offsetHeight > 0) {
                            const bg = rgbToHex(window.getComputedStyle(btn).backgroundColor);
                            if (bg && bg !== '#ffffff' && bg !== '#000000' && !bg.startsWith('#f') && !bg.startsWith('#e')) {
                                colors.accent = bg;
                                break;
                            }
                        }
                    }
                    
                    // Find dark and light backgrounds from sections
                    const sections = document.querySelectorAll('section, header, footer, main > div, [class*="section"]');
                    sections.forEach(section => {
                        if (section.offsetHeight < 100) return;
                        const bg = rgbToHex(window.getComputedStyle(section).backgroundColor);
                        if (bg) {
                            try {
                                const r = parseInt(bg.slice(1, 3), 16);
                                const g = parseInt(bg.slice(3, 5), 16);
                                const b = parseInt(bg.slice(5, 7), 16);
                                const brightness = (r + g + b) / 3;
                                if (brightness < 50 && !colors.dark_bg) {
                                    colors.dark_bg = bg;
                                } else if (brightness > 200 && !colors.light_bg) {
                                    colors.light_bg = bg;
                                }
                            } catch(e) {}
                        }
                    });
                    
                    // Defaults
                    if (!colors.dark_bg) colors.dark_bg = '#0a0a0f';
                    if (!colors.light_bg) colors.light_bg = '#ffffff';
                    if (!colors.text_on_dark) colors.text_on_dark = '#ffffff';
                    if (!colors.text_on_light) colors.text_on_light = '#1a1a1a';
                    
                    return colors;
                }
            """)
            print(f"✅ Theme colors: dark={theme_colors['dark_bg']}, accent={theme_colors['accent']}", file=sys.stderr)
            
            # 6. Extract sections with complete content and DOM skeleton
            print("📦 Extracting sections with DOM skeleton...", file=sys.stderr)
            sections_data = page.evaluate("""
                () => {
                    const sections = [];
                    
                    function rgbToHex(rgb) {
                        if (!rgb || rgb === 'rgba(0, 0, 0, 0)' || rgb === 'transparent') return null;
                        const match = rgb.match(/rgba?\\((\\d+),\\s*(\\d+),\\s*(\\d+)/);
                        if (!match) return rgb;
                        return '#' + [match[1], match[2], match[3]]
                            .map(x => parseInt(x).toString(16).padStart(2, '0'))
                            .join('');
                    }
                    
                    function getLayoutHint(el) {
                        const styles = window.getComputedStyle(el);
                        const display = styles.display;
                        const flexDir = styles.flexDirection;
                        const gridCols = styles.gridTemplateColumns;
                        const justifyContent = styles.justifyContent;
                        const alignItems = styles.alignItems;
                        
                        let hint = '';
                        if (display === 'flex') {
                            hint = flexDir === 'column' ? 'flex-col' : 'flex-row';
                            if (justifyContent === 'space-between') hint += ' justify-between';
                            if (justifyContent === 'center') hint += ' justify-center';
                            if (alignItems === 'center') hint += ' items-center';
                        } else if (display === 'grid') {
                            const cols = gridCols.split(' ').filter(c => c !== '').length;
                            hint = `grid-cols-${cols}`;
                        }
                        return hint;
                    }
                    
                    // ========== DOM Skeleton Extraction ==========
                    
                    // Tags to skip entirely
                    const SKIP_TAGS = new Set(['script', 'style', 'noscript', 'meta', 'link', 'br', 'hr']);
                    
                    function getKeyClass(classList) {
                        // Find a semantic class name for identification
                        for (const cls of classList) {
                            if (/hero|header|footer|nav|banner|feature|pricing|cta|faq|card|container|wrapper|content|sidebar|menu|logo|btn|button/.test(cls)) {
                                return cls;
                            }
                        }
                        return classList[0] || '';
                    }
                    
                    function shouldSkipElement(el) {
                        if (!el || el.nodeType !== 1) return true;
                        const tag = el.tagName.toLowerCase();
                        if (SKIP_TAGS.has(tag)) return true;
                        
                        // Skip invisible elements
                        const styles = window.getComputedStyle(el);
                        if (styles.display === 'none' || styles.visibility === 'hidden') return true;
                        if (el.offsetWidth === 0 && el.offsetHeight === 0) return true;
                        
                        return false;
                    }
                    
                    function getMeaningfulChildren(el) {
                        const children = [];
                        for (const child of el.children) {
                            if (!shouldSkipElement(child)) {
                                children.push(child);
                            }
                        }
                        return children;
                    }
                    
                    // Build node line WITH key layout hints embedded
                    // Format: tag.class [bg:#xxx, flex-row] → "content"
                    function buildNodeLine(el) {
                        const tag = el.tagName.toLowerCase();
                        const classList = Array.from(el.classList);
                        
                        // Build tag.class part
                        let nodePart = tag;
                        const keyClass = getKeyClass(classList);
                        if (keyClass) {
                            nodePart += '.' + keyClass;
                        }
                        
                        // Extract KEY layout hints (only the most important ones)
                        const layoutHints = [];
                        const styles = window.getComputedStyle(el);
                        
                        // 1. Background color (critical for dark/light sections)
                        const bgColor = styles.backgroundColor;
                        if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                            const hex = rgbToHex(bgColor);
                            if (hex && hex !== '#000000' && hex !== '#ffffff') {
                                // Only add non-black/white backgrounds
                                layoutHints.push(`bg:${hex}`);
                            } else if (hex === '#ffffff' || (bgColor.includes('255') && bgColor.includes('255') && bgColor.includes('255'))) {
                                // Explicitly mark white backgrounds (important for dark-theme sites)
                                layoutHints.push('bg:#ffffff');
                            }
                        }
                        
                        // 2. Layout direction (critical for structure)
                        const display = styles.display;
                        if (display === 'flex') {
                            const direction = styles.flexDirection;
                            if (direction === 'row' || direction === 'row-reverse' || direction === '') {
                                layoutHints.push('flex-row');
                            } else if (direction === 'column' || direction === 'column-reverse') {
                                layoutHints.push('flex-col');
                            }
                        } else if (display === 'grid') {
                            const cols = styles.gridTemplateColumns;
                            if (cols && cols !== 'none') {
                                const colCount = cols.split(/\s+/).filter(c => c && c !== 'none').length;
                                if (colCount > 1 && colCount <= 6) {
                                    layoutHints.push(`grid-${colCount}`);
                                }
                            }
                        }
                        
                        // 3. Text color (important for readability on dark/light bg)
                        // Only add if it's clearly light text (for dark backgrounds)
                        const color = styles.color;
                        if (color) {
                            const textHex = rgbToHex(color);
                            if (textHex) {
                                const r = parseInt(textHex.slice(1, 3), 16);
                                const g = parseInt(textHex.slice(3, 5), 16);
                                const b = parseInt(textHex.slice(5, 7), 16);
                                const brightness = (r + g + b) / 3;
                                // Only mark very light text (for dark bg) or very dark text (for light bg)
                                if (brightness > 200) {
                                    layoutHints.push('text-light');
                                } else if (brightness < 50) {
                                    layoutHints.push('text-dark');
                                }
                            }
                        }
                        
                        // Build layout hint string
                        let hintStr = '';
                        if (layoutHints.length > 0) {
                            hintStr = ` [${layoutHints.join(', ')}]`;
                        }
                        
                        // Content for leaf nodes
                        let contentStr = '';
                        
                        if (tag === 'img') {
                            const alt = el.getAttribute('alt') || '';
                            contentStr = alt ? ` → alt:"${alt.substring(0, 30)}"` : '';
                        } else if (tag === 'a') {
                            const text = el.textContent.trim().substring(0, 40);
                            if (text) contentStr = ` → "${text}"`;
                        } else if (tag === 'svg') {
                            contentStr = ' → [icon]';
                        } else if (['h1','h2','h3','h4','h5','h6','p','span','button','li','label'].includes(tag)) {
                            const text = el.textContent.trim();
                            if (text) {
                                const truncated = text.length > 50 ? text.substring(0, 47) + '...' : text;
                                contentStr = ` → "${truncated}"`;
                            }
                        } else if (tag === 'input') {
                            const type = el.getAttribute('type') || 'text';
                            const placeholder = el.getAttribute('placeholder') || '';
                            contentStr = ` → [${type}]`;
                            if (placeholder) contentStr += ` "${placeholder.substring(0, 20)}"`;
                        }
                        
                        return nodePart + hintStr + contentStr;
                    }
                    
                    function extractSkeleton(el, depth = 0, maxDepth = 8) {
                        if (depth > maxDepth) return { lines: ['... (深层省略)'], hasContent: true };
                        if (shouldSkipElement(el)) return { lines: [], hasContent: false };
                        
                        const tag = el.tagName.toLowerCase();
                        const nodeLine = buildNodeLine(el);
                        
                        // For SVG, don't recurse into children
                        if (tag === 'svg') {
                            return { lines: [nodeLine], hasContent: true };
                        }
                        
                        const children = getMeaningfulChildren(el);
                        
                        // If no children, return just this node
                        if (children.length === 0) {
                            return { lines: [nodeLine], hasContent: true };
                        }
                        
                        // Handle repeated similar children (like list items, cards)
                        // Show first 3, then "... (N more tag)" for the rest
                        let childResults = [];
                        let consecutiveCount = 1;
                        let lastChildTag = '';
                        
                        for (let i = 0; i < children.length; i++) {
                            const child = children[i];
                            const childTag = child.tagName.toLowerCase();
                            
                            // Track consecutive same-tag children
                            if (childTag === lastChildTag) {
                                consecutiveCount++;
                            } else {
                                consecutiveCount = 1;
                            }
                            lastChildTag = childTag;
                            
                            // After 3 consecutive same-tag elements, show summary and skip rest
                            if (consecutiveCount > 3) {
                                if (consecutiveCount === 4) {
                                    // Count remaining same-tag elements
                                    let remaining = 1;
                                    for (let j = i + 1; j < children.length; j++) {
                                        if (children[j].tagName.toLowerCase() === childTag) {
                                            remaining++;
                                        } else {
                                            break;
                                        }
                                    }
                                    childResults.push({ lines: [`... (${remaining} more ${childTag})`], hasContent: true });
                                }
                                continue;
                            }
                            
                            const result = extractSkeleton(child, depth + 1, maxDepth);
                            if (result.hasContent) {
                                childResults.push(result);
                            }
                        }
                        
                        // Build tree with proper prefixes
                        const lines = [nodeLine];
                        for (let i = 0; i < childResults.length; i++) {
                            const isLast = (i === childResults.length - 1);
                            const prefix = isLast ? '└─ ' : '├─ ';
                            const continuation = isLast ? '   ' : '│  ';
                            
                            const childLines = childResults[i].lines;
                            if (childLines.length > 0) {
                                lines.push(prefix + childLines[0]);
                                for (let j = 1; j < childLines.length; j++) {
                                    lines.push(continuation + childLines[j]);
                                }
                            }
                        }
                        
                        return { lines, hasContent: true };
                    }
                    
                    // ========== End DOM Skeleton Extraction ==========
                    
                    // Find all major sections
                    const sectionSelectors = [
                        'section', '[class$="-section"]',
                        'header', 'footer', 'main > div',
                        '[class*="hero"]', '[class*="banner"]', '[class*="feature"]',
                        '[class*="about"]', '[class*="pricing"]', '[class*="contact"]',
                        '[class*="testimonial"]', '[class*="cta"]', '[class*="faq"]',
                        '[class*="trusted"]', '[class*="modes"]', '[class*="intro"]',
                        '[class*="footer"]', '[class*="services"]', '[class*="team"]',
                        '[class*="gallery"]', '[class*="portfolio"]', '[class*="blog"]'
                    ];
                    
                    const foundSections = new Map();
                    sectionSelectors.forEach(sel => {
                        try {
                            document.querySelectorAll(sel).forEach(el => {
                                if (el.offsetHeight > 150 && !foundSections.has(el)) {
                                    foundSections.set(el, true);
                                }
                            });
                        } catch(e) {}
                    });
                    
                    // Remove nested sections
                    const allSections = Array.from(foundSections.keys());
                    const topLevelSections = allSections.filter(el => {
                        return !allSections.some(other => other !== el && other.contains(el));
                    });
                    
                    // Sort by vertical position
                    const sortedSections = topLevelSections
                        .map(el => ({ el, top: el.getBoundingClientRect().top + window.scrollY }))
                        .sort((a, b) => a.top - b.top)
                        .slice(0, 15);
                    
                    sortedSections.forEach((item, idx) => {
                        const el = item.el;
                        const styles = window.getComputedStyle(el);
                        
                        // Get background color
                        let bgColor = rgbToHex(styles.backgroundColor) || 'transparent';
                        
                        // Get section name
                        let name = '';
                        const classList = Array.from(el.classList);
                        const sectionClass = classList.find(c => 
                            c.includes('section') || c.includes('hero') || c.includes('banner') || 
                            c.includes('cta') || c.includes('faq') || c.includes('footer') ||
                            c.includes('header') || c.includes('feature') || c.includes('pricing')
                        );
                        if (sectionClass) {
                            name = sectionClass;
                        } else {
                            const heading = el.querySelector('h1, h2, h3');
                            if (heading) {
                                name = heading.textContent.trim().substring(0, 30);
                            } else {
                                name = el.id || classList[0] || el.tagName.toLowerCase();
                            }
                        }
                        
                        // Get layout hint
                        const layout = getLayoutHint(el);
                        
                        // Extract content
                        const content = {
                            headings: [],
                            paragraphs: [],
                            buttons: [],
                            links: [],
                            listItems: [],
                            images: []
                        };
                        
                        // Headings - COMPLETE text with level
                        el.querySelectorAll('h1, h2, h3, h4').forEach(h => {
                            const text = h.textContent.trim();
                            if (text && text.length > 0) {
                                content.headings.push({
                                    level: h.tagName.toLowerCase(),
                                    text: text
                                });
                            }
                        });
                        
                        // Paragraphs - COMPLETE text
                        el.querySelectorAll('p').forEach(p => {
                            const text = p.textContent.trim();
                            if (text && text.length > 10) {
                                content.paragraphs.push(text);
                            }
                        });
                        
                        // Buttons
                        el.querySelectorAll('button, [class*="btn"], [class*="button"], a[class*="cta"]').forEach(btn => {
                            const text = btn.textContent.trim();
                            if (text && text.length > 0 && text.length < 50) {
                                const btnBg = rgbToHex(window.getComputedStyle(btn).backgroundColor);
                                content.buttons.push({
                                    text: text,
                                    color: btnBg
                                });
                            }
                        });
                        
                        // Links (nav items)
                        el.querySelectorAll('a').forEach(a => {
                            const text = a.textContent.trim();
                            if (text && text.length > 0 && text.length < 30) {
                                // Avoid duplicating buttons
                                if (!content.buttons.some(b => b.text === text)) {
                                    content.links.push(text);
                                }
                            }
                        });
                        
                        // List items
                        el.querySelectorAll('li').forEach(li => {
                            const text = li.textContent.trim();
                            if (text && text.length > 5 && text.length < 200) {
                                content.listItems.push(text);
                            }
                        });
                        
                        // Images in this section
                        el.querySelectorAll('img').forEach(img => {
                            if (img.src && !img.src.startsWith('data:') && img.offsetWidth > 50) {
                                content.images.push({
                                    alt: img.alt || 'image',
                                    src: img.src
                                });
                            }
                        });
                        
                        // Deduplicate
                        content.buttons = content.buttons.filter((b, i, arr) => 
                            arr.findIndex(x => x.text === b.text) === i
                        ).slice(0, 10);
                        content.links = [...new Set(content.links)].slice(0, 15);
                        content.listItems = [...new Set(content.listItems)].slice(0, 20);
                        
                        // ========================================
                        // 泛化交互组件检测（基于结构特征，不依赖类名）
                        // ========================================
                        const interactions = [];
                        
                        // ------------------------------------------
                        // 1. Tab 检测：多个并列按钮/链接 + 内容切换
                        // ------------------------------------------
                        let isTab = false;
                        let tabItems = [];
                        
                        // Method 1a: ARIA role
                        const ariaTabElements = el.querySelectorAll('[role="tab"], [role="tablist"]');
                        if (ariaTabElements.length > 0) {
                            isTab = true;
                            ariaTabElements.forEach(tab => {
                                const text = tab.textContent?.trim();
                                if (text && text.length < 50) tabItems.push(text);
                            });
                        }
                        
                        // Method 1b: 结构检测 - 水平排列的多个按钮/链接，紧邻内容区
                        if (!isTab) {
                            const buttonGroups = el.querySelectorAll('div, nav, ul');
                            for (const group of buttonGroups) {
                                const style = window.getComputedStyle(group);
                                const isHorizontal = style.display === 'flex' && 
                                    (style.flexDirection === 'row' || style.flexDirection === '');
                                
                                if (isHorizontal) {
                                    const buttons = group.querySelectorAll('button, a');
                                    // 3-8 个并列的短文本按钮
                                    if (buttons.length >= 3 && buttons.length <= 8) {
                                        const allShortText = Array.from(buttons).every(b => 
                                            b.textContent.trim().length > 0 && 
                                            b.textContent.trim().length < 30
                                        );
                                        if (allShortText) {
                                            // 检查是否有相邻的内容区域
                                            const nextSibling = group.nextElementSibling;
                                            if (nextSibling && nextSibling.children.length > 0) {
                                                isTab = true;
                                                buttons.forEach(b => tabItems.push(b.textContent.trim()));
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        if (isTab && tabItems.length > 0) {
                            interactions.push({
                                type: 'Tab',
                                items: [...new Set(tabItems)].slice(0, 8),
                                hint: '多个选项切换显示不同内容'
                            });
                        }
                        
                        // ------------------------------------------
                        // 2. Accordion 检测：可折叠的标题+内容对
                        // ------------------------------------------
                        let isAccordion = false;
                        let accordionCount = 0;
                        
                        // Method 2a: ARIA/HTML5
                        const ariaAccordions = el.querySelectorAll('[aria-expanded], details, summary');
                        if (ariaAccordions.length >= 2) {
                            isAccordion = true;
                            accordionCount = ariaAccordions.length;
                        }
                        
                        // Method 2b: 结构检测 - 多个相似的 标题+内容 块
                        if (!isAccordion) {
                            // 查找包含箭头/加号图标的可点击元素
                            const potentialHeaders = el.querySelectorAll('button, div[role="button"], h3, h4');
                            let faqLikeCount = 0;
                            
                            for (const header of potentialHeaders) {
                                // 检查是否有展开/收起指示器（+、-、箭头）
                                const hasIcon = header.querySelector('svg, i, span[class*="icon"], span[class*="arrow"]');
                                const textContent = header.textContent.trim();
                                
                                // FAQ 特征：问号、短标题、后面跟着较长内容
                                if (textContent.includes('?') || 
                                    (textContent.length > 10 && textContent.length < 100 && hasIcon)) {
                                    const nextEl = header.nextElementSibling;
                                    if (nextEl && nextEl.textContent.trim().length > textContent.length) {
                                        faqLikeCount++;
                                    }
                                }
                            }
                            
                            if (faqLikeCount >= 3) {
                                isAccordion = true;
                                accordionCount = faqLikeCount;
                            }
                        }
                        
                        // Method 2c: 关键词检测
                        if (!isAccordion) {
                            const sectionText = el.textContent.toLowerCase();
                            if (sectionText.includes('faq') || 
                                sectionText.includes('frequently asked') ||
                                sectionText.includes('常见问题')) {
                                // 计算问号数量
                                const questionCount = (el.textContent.match(/\?/g) || []).length;
                                if (questionCount >= 3) {
                                    isAccordion = true;
                                    accordionCount = questionCount;
                                }
                            }
                        }
                        
                        if (isAccordion) {
                            interactions.push({
                                type: 'Accordion',
                                count: accordionCount,
                                hint: '可展开/折叠的内容块，如 FAQ'
                            });
                        }
                        
                        // ------------------------------------------
                        // 3. Carousel/Slider 检测：横向滚动的多个相似项
                        // ------------------------------------------
                        let isCarousel = false;
                        let carouselHint = '';
                        
                        // Method 3a: 横向滚动容器
                        const allElements = el.querySelectorAll('*');
                        for (const container of allElements) {
                            const style = window.getComputedStyle(container);
                            if (style.overflowX === 'auto' || style.overflowX === 'scroll' ||
                                style.scrollSnapType.includes('x')) {
                                const children = container.children;
                                if (children.length >= 3) {
                                    isCarousel = true;
                                    carouselHint = '横向滚动容器';
                                    break;
                                }
                            }
                        }
                        
                        // Method 3b: 导航指示器（dots、arrows）
                        if (!isCarousel) {
                            const hasDots = el.querySelectorAll('[class*="dot"], [class*="indicator"], [class*="bullet"], [class*="pagination"]').length >= 2;
                            const hasArrows = el.querySelectorAll('[class*="arrow"], [class*="prev"], [class*="next"], [class*="chevron"]').length >= 1;
                            
                            if (hasDots || hasArrows) {
                                // 确认有多个相似的子元素
                                const repeatingElements = el.querySelectorAll('[class*="card"], [class*="item"], [class*="slide"], [class*="testimonial"]');
                                if (repeatingElements.length >= 3) {
                                    isCarousel = true;
                                    carouselHint = hasDots ? '带指示器的轮播' : '带箭头的轮播';
                                }
                            }
                        }
                        
                        // Method 3c: 多头像选择器（常见于 testimonials）
                        if (!isCarousel) {
                            const avatarImages = el.querySelectorAll('img');
                            let roundAvatarCount = 0;
                            
                            for (const img of avatarImages) {
                                const style = window.getComputedStyle(img);
                                const isRound = style.borderRadius === '50%' || 
                                    style.borderRadius === '9999px' ||
                                    img.className.includes('round') ||
                                    img.className.includes('circle') ||
                                    img.className.includes('avatar');
                                const isSmall = img.offsetWidth <= 80 && img.offsetHeight <= 80;
                                
                                if (isRound && isSmall) roundAvatarCount++;
                            }
                            
                            if (roundAvatarCount >= 5) {
                                isCarousel = true;
                                carouselHint = '头像选择器+内容切换（如用户评价）';
                            }
                        }
                        
                        // Method 3d: 多个相似卡片但只显示部分
                        if (!isCarousel) {
                            const cards = [];
                            const potentialCards = el.querySelectorAll('div');
                            
                            for (const div of potentialCards) {
                                const children = div.children;
                                // 卡片特征：有边框/阴影/圆角，包含图片或文本
                                const style = window.getComputedStyle(div);
                                const hasCardStyle = style.borderRadius !== '0px' || 
                                    style.boxShadow !== 'none' ||
                                    style.border !== '';
                                
                                if (hasCardStyle && children.length >= 2) {
                                    cards.push(div);
                                }
                            }
                            
                            // 检查是否有多个相似卡片在同一个容器中
                            if (cards.length >= 4) {
                                // 检查父容器是否限制了显示数量
                                const parent = cards[0].parentElement;
                                if (parent) {
                                    const parentStyle = window.getComputedStyle(parent);
                                    const isFlexOrGrid = parentStyle.display === 'flex' || parentStyle.display === 'grid';
                                    if (isFlexOrGrid && cards.length > 3) {
                                        isCarousel = true;
                                        carouselHint = '多卡片展示（可能带滑动）';
                                    }
                                }
                            }
                        }
                        
                        if (isCarousel) {
                            interactions.push({
                                type: 'Carousel',
                                hint: carouselHint || '横向滚动/轮播组件'
                            });
                        }
                        
                        // ------------------------------------------
                        // 4. Dropdown/Select 检测
                        // ------------------------------------------
                        const dropdowns = el.querySelectorAll('select, [role="listbox"], [role="combobox"], [class*="dropdown"], [class*="select"]');
                        if (dropdowns.length > 0) {
                            interactions.push({
                                type: 'Dropdown',
                                count: dropdowns.length,
                                hint: '下拉选择器'
                            });
                        }
                        
                        // ------------------------------------------
                        // 5. Modal/Popup 触发检测
                        // ------------------------------------------
                        const modalTriggers = el.querySelectorAll('[data-modal], [data-toggle="modal"], [class*="modal-trigger"], button[class*="open"]');
                        const hasHiddenContent = el.querySelectorAll('[class*="modal"], [class*="popup"], [class*="dialog"], [role="dialog"]').length > 0;
                        
                        if (modalTriggers.length > 0 || hasHiddenContent) {
                            interactions.push({
                                type: 'Modal',
                                hint: '点击触发弹窗/对话框'
                            });
                        }
                        
                        // ------------------------------------------
                        // 6. Toggle/Switch 检测
                        // ------------------------------------------
                        const toggles = el.querySelectorAll('[role="switch"], [type="checkbox"][class*="toggle"], [class*="switch"], [class*="toggle"]');
                        if (toggles.length > 0) {
                            interactions.push({
                                type: 'Toggle',
                                count: toggles.length,
                                hint: '开关切换'
                            });
                        }
                        
                        // Extract DOM skeleton for this section (now with embedded layout hints)
                        const skeletonResult = extractSkeleton(el, 0, 8);
                        const skeleton = skeletonResult.lines.join('\\n');
                        
                        sections.push({
                            index: idx + 1,
                            name: name,
                            tag: el.tagName.toLowerCase(),
                            bgColor: bgColor,
                            layout: layout,
                            content: content,
                            interactions: interactions,
                            skeleton: skeleton
                        });
                    });
                    
                    return sections;
                }
            """)
            print(f"✅ Extracted {len(sections_data)} sections", file=sys.stderr)
            
            # 7. Extract navigation links
            print("🔗 Extracting navigation links...", file=sys.stderr)
            nav_links = page.evaluate("""
                () => {
                    const links = [];
                    const seen = new Set();
                    const currentHost = window.location.host;
                    
                    const navElements = document.querySelectorAll('nav, header nav, [role="navigation"], .navbar, .nav-menu');
                    navElements.forEach(nav => {
                        nav.querySelectorAll('a[href]').forEach(a => {
                            const href = a.getAttribute('href');
                            const text = a.textContent.trim();
                            
                            if (!href || !text) return;
                            if (href.startsWith('#') || href.startsWith('javascript:') || 
                                href.startsWith('mailto:') || href.startsWith('tel:')) return;
                            
                            let isInternal = false;
                            if (href.startsWith('/') || href.startsWith('./') || href.startsWith('../')) {
                                isInternal = true;
                            } else {
                                try {
                                    const linkUrl = new URL(href, window.location.origin);
                                    isInternal = linkUrl.host === currentHost;
                                } catch(e) {}
                            }
                            
                            if (isInternal && !seen.has(href)) {
                                seen.add(href);
                                const cleanHref = href.split('#')[0];
                                if (cleanHref) {
                                    links.push({ text: text.substring(0, 30), href: cleanHref });
                                }
                            }
                        });
                    });
                    
                    return links.slice(0, 15);
                }
            """)
            print(f"✅ Found {len(nav_links)} navigation links", file=sys.stderr)
            
            # 8. Generate output files
            print("📝 Generating output files...", file=sys.stderr)
            
            # Create sections directory
            sections_dir = output_path / "sections"
            sections_dir.mkdir(exist_ok=True)
            
            # Generate meta.md
            meta_lines = [
                "# 网站元信息",
                "",
                "> ⛔ **这是数据转换任务，不是内容创作。**",
                "> 引号内的文案必须原样使用，不得修改。",
                "",
                "## 主题色",
                "",
                f"- 深色背景: `{theme_colors['dark_bg']}`",
                f"- 浅色背景: `{theme_colors['light_bg']}`",
            ]
            if theme_colors['accent']:
                meta_lines.append(f"- 强调色/按钮: `{theme_colors['accent']}`")
            meta_lines.extend([
                f"- 深底文字: `{theme_colors['text_on_dark']}`",
                f"- 浅底文字: `{theme_colors['text_on_light']}`",
                "",
                "## Section 列表",
                "",
            ])
            
            for section in sections_data:
                meta_lines.append(f"{section['index']}. {section['name']}")
            
            meta_lines.extend([
                "",
                "## 导航链接",
                "",
            ])
            
            if nav_links:
                for link in nav_links:
                    meta_lines.append(f"- {link['text']}: `{link['href']}`")
            else:
                meta_lines.append("- (无导航链接)")
            
            meta_lines.extend([
                "",
                "## 统计",
                "",
                f"- Section 数量: **{len(sections_data)}**",
                f"- 图片数量: **{len(downloaded_images)}**",
                f"- 导航页面: **{len(nav_links)}**",
            ])
            
            meta_path = output_path / "meta.md"
            meta_path.write_text('\n'.join(meta_lines), encoding='utf-8')
            print(f"✅ meta.md saved", file=sys.stderr)
            
            # Generate individual section files
            for section in sections_data:
                section_lines = []
                
                # Section header with background color and layout
                section_lines.append(f"## {section['name']}")
                section_lines.append("")
                section_lines.append(f"**背景色**: `{section['bgColor']}`")
                section_lines.append(f"**整体布局**: `{section['layout']}`")
                section_lines.append("")
                
                # DOM Skeleton with embedded layout hints
                if section.get('skeleton'):
                    section_lines.append("### 结构骨架")
                    section_lines.append("")
                    section_lines.append("> 骨架中 `[...]` 内的是关键布局信息，必须遵循：")
                    section_lines.append("> - `bg:#xxx` = 背景色，用 `style={{ backgroundColor: '#xxx' }}`")
                    section_lines.append("> - `flex-row` = 横向排列（左右分栏），用 `className=\"flex flex-row\"`")
                    section_lines.append("> - `flex-col` = 纵向排列（上下堆叠），用 `className=\"flex flex-col\"`")
                    section_lines.append("> - `grid-N` = N列网格，用 `className=\"grid grid-cols-N\"`")
                    section_lines.append("> - `text-light` = 浅色文字，用 `className=\"text-white\"` 或 `text-gray-xxx`")
                    section_lines.append("> - `text-dark` = 深色文字，用 `className=\"text-gray-900\"` 或 `text-black`")
                    section_lines.append("")
                    section_lines.append("```")
                    section_lines.append(section['skeleton'])
                    section_lines.append("```")
                    section_lines.append("")
                
                content = section['content']
                
                # Text content section
                section_lines.append("### 文案清单")
                section_lines.append("")
                
                # Headings with level indicators
                for h in content['headings']:
                    level = h['level']
                    text = h['text']
                    section_lines.append(f'- {level}: "{text}"')
                
                # Paragraphs
                for p in content['paragraphs']:
                    # Escape quotes in paragraph
                    escaped = p.replace('"', '\\"')
                    section_lines.append(f'- p: "{escaped}"')
                
                # Buttons
                if content['buttons']:
                    for btn in content['buttons']:
                        if btn['color']:
                            section_lines.append(f'- button: "{btn["text"]}" ({btn["color"]})')
                        else:
                            section_lines.append(f'- button: "{btn["text"]}"')
                
                # Links (for header/nav sections)
                if content['links']:
                    for link in content['links'][:10]:
                        section_lines.append(f'- a: "{link}"')
                
                # List items
                if content['listItems']:
                    for item in content['listItems'][:15]:
                        escaped = item.replace('"', '\\"')
                        section_lines.append(f'- li: "{escaped}"')
                
                section_lines.append("")
                
                # Images
                if content['images']:
                    section_lines.append("### 图片")
                    for img in content['images'][:5]:
                        # Find local path from manifest
                        local_path = None
                        for dl_img in downloaded_images:
                            if dl_img['original_url'] == img['src']:
                                local_path = dl_img['local_path']
                                break
                        if local_path:
                            section_lines.append(f"- {local_path} (alt: {img['alt']})")
                        else:
                            section_lines.append(f"- [未下载] (alt: {img['alt']})")
                    section_lines.append("")
                
                # Interactions (enhanced output for all types)
                if section['interactions']:
                    section_lines.append("### 交互组件")
                    section_lines.append("")
                    for interaction in section['interactions']:
                        itype = interaction['type']
                        hint = interaction.get('hint', '')
                        
                        if itype == 'Tab':
                            items = interaction.get('items', [])
                            items_str = ' | '.join([f'"{item}"' for item in items]) if items else ''
                            section_lines.append(f"- **Tab 切换**: {items_str}")
                            section_lines.append(f"  - 说明: {hint}" if hint else "  - 说明: 多选项切换")
                            section_lines.append(f"  - 实现: `const [activeTab, setActiveTab] = useState(0)`")
                            section_lines.append(f"  - 样式: 选中项有下划线/背景变化")
                            
                        elif itype == 'Accordion':
                            count = interaction.get('count', 0)
                            section_lines.append(f"- **Accordion 折叠**: {count} 项")
                            section_lines.append(f"  - 说明: {hint}" if hint else "  - 说明: FAQ 或可折叠内容")
                            section_lines.append(f"  - 实现: `const [expanded, setExpanded] = useState<boolean[]>(Array({count}).fill(false))`")
                            section_lines.append(f"  - 样式: 展开项显示内容，箭头旋转")
                            
                        elif itype == 'Carousel':
                            section_lines.append(f"- **Carousel 轮播**: {hint}")
                            section_lines.append(f"  - 实现: `const [currentIndex, setCurrentIndex] = useState(0)`")
                            section_lines.append(f"  - 样式: 当前选中项高亮边框/背景")
                            section_lines.append(f"  - 注意: 若有头像选择器，点击头像切换内容")
                            
                        elif itype == 'Dropdown':
                            count = interaction.get('count', 1)
                            section_lines.append(f"- **Dropdown 下拉**: {count} 个选择器")
                            section_lines.append(f"  - 实现: `const [selected, setSelected] = useState('')`")
                            section_lines.append(f"  - 样式: 点击展开选项列表")
                            
                        elif itype == 'Modal':
                            section_lines.append(f"- **Modal 弹窗**: {hint}")
                            section_lines.append(f"  - 实现: `const [isOpen, setIsOpen] = useState(false)`")
                            section_lines.append(f"  - 样式: 半透明遮罩 + 居中弹窗")
                            
                        elif itype == 'Toggle':
                            count = interaction.get('count', 1)
                            section_lines.append(f"- **Toggle 开关**: {count} 个")
                            section_lines.append(f"  - 实现: `const [enabled, setEnabled] = useState(false)`")
                            section_lines.append(f"  - 样式: 开/关状态颜色变化")
                            
                        else:
                            section_lines.append(f"- **{itype}**: {hint}")
                            
                        section_lines.append("")
                    
                # Write section file
                section_filename = f"{section['index']:02d}_{sanitize_filename(section['name'])}.md"
                section_path = sections_dir / section_filename
                section_path.write_text('\n'.join(section_lines), encoding='utf-8')
            
            print(f"✅ Generated {len(sections_data)} section files in sections/", file=sys.stderr)
            
            # Summary
            print("\n" + "="*60, file=sys.stderr)
            print("✅ WEB CONTENT EXTRACTION COMPLETE", file=sys.stderr)
            print("="*60, file=sys.stderr)
            print(f"\n📁 Output directory: {output_path.absolute()}", file=sys.stderr)
            print(f"📋 meta.md: 主题色、Section 列表、导航链接", file=sys.stderr)
            print(f"📂 sections/: {len(sections_data)} 个 Section 文件", file=sys.stderr)
            print(f"🖼️  images/: {len(downloaded_images)} 张图片", file=sys.stderr)
            print(f"📸 screenshot.png: 视觉参考", file=sys.stderr)
            print("="*60 + "\n", file=sys.stderr)
            
            return {
                "url": url,
                "output_dir": str(output_path.absolute()),
                "sections": len(sections_data),
                "images": len(downloaded_images),
                "nav_links": len(nav_links)
            }
            
        except Exception as e:
            print(f"\n❌ Error during extraction: {e}", file=sys.stderr)
            raise
        finally:
            browser.close()


def sanitize_filename(name: str) -> str:
    """Convert section name to safe filename."""
    # Remove special characters
    name = re.sub(r'[^\w\s-]', '', name)
    # Replace spaces with underscores
    name = re.sub(r'\s+', '_', name)
    # Lowercase
    name = name.lower()
    # Limit length
    return name[:30] if name else 'section'


def get_default_output_dir(url: str) -> str:
    """Generate default output directory name from URL.
    
    Output directory is created in the CURRENT WORKING DIRECTORY (project root),
    not relative to the script location.
    """
    parsed = urlparse(url)
    domain = parsed.netloc.replace('www.', '').split('.')[0]
    # Use current working directory (project root), NOT script directory
    cwd = Path.cwd()
    return str(cwd / f"{domain}_content")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python web_content_fetcher.py <url> [output_directory]", file=sys.stderr)
        print("\nExamples:", file=sys.stderr)
        print("  python web_content_fetcher.py https://stripe.com", file=sys.stderr)
        print("  # Output: ./stripe_content/ (in current working directory)", file=sys.stderr)
        print("\n  python web_content_fetcher.py https://stripe.com my_output", file=sys.stderr)
        print("  # Output: my_output/", file=sys.stderr)
        print("\nOutput structure:", file=sys.stderr)
        print("  <output>/", file=sys.stderr)
        print("  ├── meta.md           # 主题色、Section 列表", file=sys.stderr)
        print("  ├── sections/         # 每个 Section 一个文件", file=sys.stderr)
        print("  │   ├── 01_header.md", file=sys.stderr)
        print("  │   ├── 02_hero.md", file=sys.stderr)
        print("  │   └── ...", file=sys.stderr)
        print("  ├── images/           # 下载的图片", file=sys.stderr)
        print("  ├── screenshot.png    # 视觉参考", file=sys.stderr)
        print("  └── source.html       # 原始 HTML", file=sys.stderr)
        sys.exit(1)
    
    url = sys.argv[1]
    output_dir = sys.argv[2] if len(sys.argv) >= 3 else get_default_output_dir(url)
    
    try:
        summary = fetch_web_content(url, output_dir)
        print(json.dumps(summary, indent=2))
    except Exception as e:
        print(f"Fatal error: {e}", file=sys.stderr)
        sys.exit(1)
