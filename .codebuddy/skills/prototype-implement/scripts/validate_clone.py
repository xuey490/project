#!/usr/bin/env python3
"""
Clone Validator - Automated quality checker for generated website clones.
Validates colors, images, layout, typography, and content against extracted data.

Enhanced with blueprint-based validation to catch:
- Tailwind approximations instead of exact CSS values
- Missing interaction component state management
- Omitted classNames from the original
"""

import sys
import json
import re
from pathlib import Path
from typing import Dict, List, Any


# Tailwind approximation patterns - maps Tailwind utilities to what they approximate
TAILWIND_APPROXIMATIONS = {
    # Font sizes - Tailwind uses rem, originals often use px
    'text-xs': '12px',
    'text-sm': '14px', 
    'text-base': '16px',
    'text-lg': '18px',
    'text-xl': '20px',
    'text-2xl': '24px',
    'text-3xl': '30px',
    'text-4xl': '36px',
    'text-5xl': '48px',
    'text-6xl': '60px',
    'text-7xl': '72px',
    'text-8xl': '96px',
    'text-9xl': '128px',
    # Font weights
    'font-thin': '100',
    'font-extralight': '200',
    'font-light': '300',
    'font-normal': '400',
    'font-medium': '500',
    'font-semibold': '600',
    'font-bold': '700',
    'font-extrabold': '800',
    'font-black': '900',
    # Generic colors (these should be replaced with exact hex values)
    'bg-black': '#000000',
    'bg-white': '#ffffff',
    'text-black': '#000000',
    'text-white': '#ffffff',
}


class CloneValidator:
    def __init__(self, content_dir: str, generated_html: str):
        self.content_dir = Path(content_dir)
        self.generated_html = Path(generated_html).read_text(encoding='utf-8')
        
        # Load extracted data - with fallbacks for missing files
        self.design_system = self._load_json('design_system.json', {})
        self.image_manifest = self._load_json('image_manifest.json', [])
        
        # Load blueprint for enhanced validation
        self.blueprint = self._parse_blueprint()
        
        self.issues = []
        self.recommendations = []
    
    def _load_json(self, filename: str, default: Any) -> Any:
        """Safely load JSON file with fallback."""
        path = self.content_dir / filename
        if path.exists():
            try:
                return json.loads(path.read_text(encoding='utf-8'))
            except json.JSONDecodeError:
                return default
        return default
    
    def _parse_blueprint(self) -> Dict[str, Any]:
        """Parse clone_blueprint.md to extract validation requirements."""
        blueprint_path = self.content_dir / 'clone_blueprint.md'
        if not blueprint_path.exists():
            return {'style_bindings': [], 'interactions': [], 'colors': []}
        
        content = blueprint_path.read_text(encoding='utf-8')
        blueprint = {
            'style_bindings': [],
            'interactions': [],
            'colors': []
        }
        
        # Parse style bindings table
        style_section = re.search(r'## 1\. 样式绑定表[\s\S]*?(?=## 2\.|$)', content)
        if style_section:
            # Match table rows: | element | className | styles |
            rows = re.findall(r'\| ([^|]+) \| `?([^|`]+)`? \| ([^|]+) \|', style_section.group())
            for element, classname, styles in rows:
                if element.strip() not in ['元素', '---']:
                    blueprint['style_bindings'].append({
                        'element': element.strip(),
                        'className': classname.strip() if classname.strip() != '-' else '',
                        'styles': styles.strip()
                    })
        
        # Parse interaction components table
        interaction_section = re.search(r'## 2\. 交互组件表[\s\S]*?(?=## 3\.|$)', content)
        if interaction_section:
            rows = re.findall(r'\| ([^|]+) \| ([^|]+) \| ([^|]+) \| ([^|]+) \|', interaction_section.group())
            for section, comp_type, spec, implementation in rows:
                if section.strip() not in ['Section', '---', '(无交互组件)']:
                    blueprint['interactions'].append({
                        'section': section.strip(),
                        'type': comp_type.strip(),
                        'spec': spec.strip(),
                        'implementation': implementation.strip()
                    })
        
        # Parse color mapping table
        color_section = re.search(r'## 3\. 颜色映射表[\s\S]*?(?=## 4\.|$)', content)
        if color_section:
            rows = re.findall(r'\| ([^|]+) \| ([^|]+) \| ([^|]+) \|', color_section.group())
            for section, bg_color, text_color in rows:
                if section.strip() not in ['Section', '---']:
                    blueprint['colors'].append({
                        'section': section.strip(),
                        'backgroundColor': bg_color.strip() if bg_color.strip() != '-' else None,
                        'color': text_color.strip() if text_color.strip() != '-' else None
                    })
        
        return blueprint
        
    def validate_colors(self) -> Dict[str, Any]:
        """Validate color usage against extracted design system."""
        print("🎨 Validating colors...")
        
        # Safely get extracted colors with fallback
        colors_data = self.design_system.get('colors', {})
        extracted_colors = colors_data.get('all_significant', [])
        
        # Find all hex colors in HTML
        hex_pattern = r'#[0-9a-fA-F]{6}'
        found_colors = set(re.findall(hex_pattern, self.generated_html))
        
        # Check for generic Tailwind colors
        generic_patterns = [
            r'bg-blue-\d+', r'text-blue-\d+',
            r'bg-red-\d+', r'text-red-\d+',
            r'bg-green-\d+', r'text-green-\d+',
        ]
        
        generic_count = 0
        for pattern in generic_patterns:
            generic_count += len(re.findall(pattern, self.generated_html))
        
        # Check if custom colors are defined in Tailwind config
        has_custom_colors = 'lovable-' in self.generated_html or 'brand-' in self.generated_html
        
        # Calculate score
        score = 100
        status = "PASS"
        
        if generic_count > 5:
            score -= 30
            status = "WARN"
            self.issues.append(f"Found {generic_count} generic Tailwind colors instead of custom colors")
            self.recommendations.append({
                "priority": "HIGH",
                "message": f"Replace generic colors with extracted colors: {', '.join(extracted_colors[:3])}"
            })
        
        if not has_custom_colors:
            score -= 40
            status = "FAIL"
            self.issues.append("No custom color variables defined")
            self.recommendations.append({
                "priority": "CRITICAL",
                "message": "Define custom colors in Tailwind config"
            })
        
        # Check if extracted colors are actually used
        used_extracted = sum(1 for color in extracted_colors if color.lower() in self.generated_html.lower())
        if used_extracted < len(extracted_colors) * 0.5:
            score -= 20
            status = "WARN" if status == "PASS" else status
            self.recommendations.append({
                "priority": "MEDIUM",
                "message": f"Only {used_extracted}/{len(extracted_colors)} extracted colors are used"
            })
        
        return {
            "status": status,
            "score": max(0, score),
            "found_colors": len(found_colors),
            "generic_colors": generic_count,
            "has_custom_config": has_custom_colors,
            "extracted_colors_used": used_extracted
        }
    
    def validate_images(self) -> Dict[str, Any]:
        """Validate image references against manifest."""
        print("🖼️  Validating images...")
        
        # Find all image sources
        img_pattern = r'src=["\']([^"\']+)["\']'
        found_imgs = re.findall(img_pattern, self.generated_html)
        
        # Check for placeholder services
        placeholder_services = [
            'unsplash.com', 'placehold.co', 'placeholder.com',
            'picsum.photos', 'lorempixel.com', 'dummyimage.com'
        ]
        
        placeholder_count = 0
        for img in found_imgs:
            if any(service in img.lower() for service in placeholder_services):
                placeholder_count += 1
        
        # Check for local images from manifest
        local_img_count = 0
        for img in found_imgs:
            if any(img_data['local_path'] in img for img_data in self.image_manifest):
                local_img_count += 1
        
        # Calculate score
        score = 100
        status = "PASS"
        
        if placeholder_count > 0:
            score -= placeholder_count * 10
            status = "FAIL"
            self.issues.append(f"Found {placeholder_count} placeholder image URLs")
            self.recommendations.append({
                "priority": "CRITICAL",
                "message": f"Replace placeholder images with local images from manifest"
            })
        
        if local_img_count < len(self.image_manifest) * 0.3:
            score -= 30
            status = "WARN" if status == "PASS" else status
            self.recommendations.append({
                "priority": "HIGH",
                "message": f"Only {local_img_count}/{len(self.image_manifest)} manifest images are used"
            })
        
        return {
            "status": status,
            "score": max(0, score),
            "total_images": len(found_imgs),
            "placeholder_count": placeholder_count,
            "local_images": local_img_count,
            "manifest_total": len(self.image_manifest)
        }
    
    def validate_typography(self) -> Dict[str, Any]:
        """Validate font usage."""
        print("🔤 Validating typography...")
        
        # Safely get typography data with fallback
        typography_data = self.design_system.get('typography', {})
        primary_font = typography_data.get('primary_font', 'Unknown')
        
        # Check if font is loaded
        font_loaded = 'fonts.googleapis.com' in self.generated_html or '@font-face' in self.generated_html
        
        # Check if system fonts are used as fallback
        has_fallback = 'system-ui' in self.generated_html or 'sans-serif' in self.generated_html
        
        # Calculate score
        score = 100
        status = "PASS"
        
        if not font_loaded:
            score -= 50
            status = "FAIL"
            self.issues.append("No custom font loaded")
            self.recommendations.append({
                "priority": "HIGH",
                "message": f"Load font: {primary_font}"
            })
        
        if not has_fallback:
            score -= 20
            status = "WARN" if status == "PASS" else status
            self.recommendations.append({
                "priority": "MEDIUM",
                "message": "Add fallback fonts (system-ui, sans-serif)"
            })
        
        return {
            "status": status,
            "score": max(0, score),
            "font_loaded": font_loaded,
            "has_fallback": has_fallback,
            "primary_font": primary_font
        }
    
    def validate_layout(self) -> Dict[str, Any]:
        """Validate HTML structure and sections."""
        print("📐 Validating layout...")
        
        # Check for common sections
        sections = {
            'navigation': r'<nav|class="[^"]*nav',
            'hero': r'<section[^>]*hero|class="[^"]*hero',
            'footer': r'<footer',
            'grid': r'grid-cols-|grid',
        }
        
        found_sections = {}
        for name, pattern in sections.items():
            found_sections[name] = len(re.findall(pattern, self.generated_html, re.IGNORECASE)) > 0
        
        # Check for responsive design
        has_responsive = 'md:' in self.generated_html or 'lg:' in self.generated_html
        
        # Calculate score
        score = 100
        status = "PASS"
        
        missing_sections = [name for name, found in found_sections.items() if not found]
        if missing_sections:
            score -= len(missing_sections) * 15
            status = "WARN"
            self.recommendations.append({
                "priority": "MEDIUM",
                "message": f"Missing sections: {', '.join(missing_sections)}"
            })
        
        if not has_responsive:
            score -= 20
            status = "WARN" if status == "PASS" else status
            self.recommendations.append({
                "priority": "MEDIUM",
                "message": "Add responsive breakpoints (md:, lg:)"
            })
        
        return {
            "status": status,
            "score": max(0, score),
            "sections_found": found_sections,
            "has_responsive": has_responsive
        }
    
    def validate_content(self) -> Dict[str, Any]:
        """Validate content quality."""
        print("📝 Validating content...")
        
        # Check for placeholder text
        placeholders = [
            'lorem ipsum', 'dolor sit amet', 'placeholder',
            'todo', 'coming soon', 'your content here'
        ]
        
        placeholder_count = 0
        for placeholder in placeholders:
            placeholder_count += len(re.findall(placeholder, self.generated_html, re.IGNORECASE))
        
        # Check for empty elements
        empty_pattern = r'<(h[1-6]|p|button|a)[^>]*>\s*</\1>'
        empty_count = len(re.findall(empty_pattern, self.generated_html))
        
        # Calculate score
        score = 100
        status = "PASS"
        
        if placeholder_count > 0:
            score -= placeholder_count * 10
            status = "FAIL"
            self.issues.append(f"Found {placeholder_count} placeholder texts")
            self.recommendations.append({
                "priority": "CRITICAL",
                "message": "Remove all placeholder text"
            })
        
        if empty_count > 3:
            score -= 15
            status = "WARN" if status == "PASS" else status
            self.recommendations.append({
                "priority": "MEDIUM",
                "message": f"Found {empty_count} empty elements"
            })
        
        return {
            "status": status,
            "score": max(0, score),
            "placeholder_count": placeholder_count,
            "empty_elements": empty_count
        }
    
    def validate_blueprint_compliance(self) -> Dict[str, Any]:
        """Validate against clone_blueprint.md requirements.
        
        This is the key validation for ensuring high fidelity:
        1. Detects Tailwind approximations instead of exact CSS values
        2. Verifies interaction components have proper useState
        3. Checks if specified classNames are preserved
        """
        print("📋 Validating blueprint compliance...")
        
        if not self.blueprint['style_bindings'] and not self.blueprint['interactions']:
            return {
                "status": "SKIP",
                "score": 100,
                "message": "No blueprint found, skipping compliance check"
            }
        
        approximations_found = []
        missing_classnames = []
        missing_interactions = []
        
        # 1. Detect Tailwind approximations
        for tw_class, approx_value in TAILWIND_APPROXIMATIONS.items():
            pattern = rf'\b{re.escape(tw_class)}\b'
            matches = re.findall(pattern, self.generated_html)
            if matches:
                # Check if blueprint specifies an exact value that this approximates
                for binding in self.blueprint['style_bindings']:
                    styles = binding.get('styles', '')
                    # Check if the blueprint has a specific pixel value
                    if 'font-size:' in styles:
                        exact_size = re.search(r'font-size:\s*(\d+)px', styles)
                        if exact_size:
                            exact_px = exact_size.group(1)
                            if tw_class.startswith('text-') and approx_value != f"{exact_px}px":
                                approximations_found.append({
                                    'found': tw_class,
                                    'should_be': f"font-size: {exact_px}px (use style={{{{ fontSize: '{exact_px}px' }}}})",
                                    'element': binding['element']
                                })
                                break
        
        # Also check for generic color classes when exact colors are specified
        generic_color_patterns = [
            (r'\bbg-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-\d+\b', 'background'),
            (r'\btext-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-\d+\b', 'text'),
        ]
        
        for pattern, color_type in generic_color_patterns:
            matches = re.findall(pattern, self.generated_html)
            for match in matches:
                # Check if we have exact colors in blueprint
                for color_spec in self.blueprint['colors']:
                    exact_color = color_spec.get('backgroundColor' if color_type == 'background' else 'color')
                    if exact_color and exact_color.startswith('#'):
                        approximations_found.append({
                            'found': match,
                            'should_be': f"{color_type}: {exact_color}",
                            'element': color_spec['section']
                        })
                        break
        
        # 2. Check for missing classNames from blueprint
        for binding in self.blueprint['style_bindings']:
            classname = binding.get('className', '')
            if classname and classname != '-':
                # Check if the exact className appears in the generated code
                if classname not in self.generated_html:
                    missing_classnames.append({
                        'className': classname,
                        'element': binding['element']
                    })
        
        # 3. Check interaction components have proper implementation
        for interaction in self.blueprint['interactions']:
            comp_type = interaction.get('type', '')
            impl = interaction.get('implementation', '')
            section = interaction.get('section', '')
            
            # Extract useState hook name from implementation
            state_match = re.search(r'useState.*?(\w+State|active\w*|selected\w*|is\w+)', impl, re.IGNORECASE)
            
            # Check for presence of useState for this component type
            if comp_type in ['Tab', 'Selector', 'Accordion', 'Carousel', 'Toggle']:
                # Look for relevant useState patterns
                state_patterns = [
                    r'useState.*?\(',  # Basic useState
                    r'\[\w+,\s*set\w+\]',  # Destructured state
                ]
                
                has_state = any(re.search(p, self.generated_html) for p in state_patterns)
                
                if not has_state:
                    missing_interactions.append({
                        'type': comp_type,
                        'section': section,
                        'expected': impl,
                        'issue': 'Missing useState hook'
                    })
                else:
                    # Check for click/change handlers
                    handler_patterns = [r'onClick', r'onChange', r'onSelect']
                    has_handler = any(re.search(p, self.generated_html) for p in handler_patterns)
                    if not has_handler:
                        missing_interactions.append({
                            'type': comp_type,
                            'section': section,
                            'expected': impl,
                            'issue': 'Missing event handler'
                        })
        
        # Calculate score
        score = 100
        status = "PASS"
        
        # Penalize approximations heavily
        if approximations_found:
            penalty = min(50, len(approximations_found) * 10)
            score -= penalty
            status = "FAIL"
            self.issues.append(f"Found {len(approximations_found)} Tailwind approximations instead of exact values")
            for approx in approximations_found[:5]:  # Show first 5
                self.recommendations.append({
                    "priority": "CRITICAL",
                    "message": f"Replace `{approx['found']}` with `{approx['should_be']}` for {approx['element']}"
                })
        
        # Penalize missing classNames
        if missing_classnames:
            penalty = min(30, len(missing_classnames) * 5)
            score -= penalty
            status = "WARN" if status == "PASS" else status
            self.issues.append(f"Missing {len(missing_classnames)} original classNames")
            for missing in missing_classnames[:3]:
                self.recommendations.append({
                    "priority": "HIGH",
                    "message": f"Add original className `{missing['className']}` to {missing['element']}"
                })
        
        # Penalize missing interactions
        if missing_interactions:
            penalty = min(40, len(missing_interactions) * 15)
            score -= penalty
            status = "FAIL" if status != "FAIL" else status
            self.issues.append(f"Missing {len(missing_interactions)} interaction implementations")
            for missing in missing_interactions:
                self.recommendations.append({
                    "priority": "CRITICAL",
                    "message": f"{missing['type']} in {missing['section']}: {missing['issue']}. Expected: {missing['expected']}"
                })
        
        return {
            "status": status,
            "score": max(0, score),
            "approximations_found": len(approximations_found),
            "approximation_details": approximations_found[:10],
            "missing_classnames": len(missing_classnames),
            "missing_classname_details": missing_classnames[:10],
            "missing_interactions": len(missing_interactions),
            "missing_interaction_details": missing_interactions
        }
    
    def run_validation(self) -> Dict[str, Any]:
        """Run all validations and generate report."""
        print("\n" + "="*70)
        print("🔍 CLONE VALIDATION REPORT")
        print("="*70 + "\n")
        
        results = {
            "colors": self.validate_colors(),
            "images": self.validate_images(),
            "typography": self.validate_typography(),
            "layout": self.validate_layout(),
            "content": self.validate_content(),
            "blueprint": self.validate_blueprint_compliance()
        }
        
        # Calculate overall score - blueprint compliance is most important
        weights = {
            "colors": 0.15,
            "images": 0.15,
            "typography": 0.10,
            "layout": 0.15,
            "content": 0.15,
            "blueprint": 0.30  # Blueprint compliance is weighted highest
        }
        
        overall_score = sum(results[cat]["score"] * weights[cat] for cat in weights)
        
        # Determine overall status
        if overall_score >= 85:
            overall_status = "PASS"
        elif overall_score >= 70:
            overall_status = "WARN"
        else:
            overall_status = "FAIL"
        
        report = {
            "overall_score": round(overall_score, 1),
            "overall_status": overall_status,
            "validation_results": results,
            "critical_issues": [issue for issue in self.issues],
            "recommendations": sorted(self.recommendations, 
                                    key=lambda x: {"CRITICAL": 0, "HIGH": 1, "MEDIUM": 2, "LOW": 3}[x["priority"]])
        }
        
        self._print_report(report)
        
        return report
    
    def _print_report(self, report: Dict[str, Any]):
        """Print formatted validation report."""
        
        print(f"\n📊 OVERALL SCORE: {report['overall_score']}/100 - {report['overall_status']}")
        print("="*70)
        
        # Category breakdown
        print("\n📋 Category Breakdown:")
        for category, result in report['validation_results'].items():
            status_emoji = {"PASS": "✅", "WARN": "⚠️", "FAIL": "❌", "SKIP": "⏭️"}.get(result['status'], "❓")
            print(f"  {status_emoji} {category.capitalize()}: {result['score']}/100 ({result['status']})")
        
        # Critical issues
        if report['critical_issues']:
            print(f"\n🚨 Critical Issues ({len(report['critical_issues'])}):")
            for issue in report['critical_issues']:
                print(f"  ❌ {issue}")
        
        # Recommendations
        if report['recommendations']:
            print(f"\n💡 Recommendations ({len(report['recommendations'])}):")
            for i, rec in enumerate(report['recommendations'][:10], 1):
                priority_emoji = {"CRITICAL": "🔴", "HIGH": "🟠", "MEDIUM": "🟡", "LOW": "🟢"}[rec['priority']]
                print(f"  {i}. {priority_emoji} [{rec['priority']}] {rec['message']}")
        
        # Pass/Fail verdict
        print("\n" + "="*70)
        if report['overall_status'] == "PASS":
            print("✅ VALIDATION PASSED - Clone is ready for delivery")
        elif report['overall_status'] == "WARN":
            print("⚠️  VALIDATION WARNING - Fix high-priority issues and re-validate")
        else:
            print("❌ VALIDATION FAILED - Major issues require attention")
        print("="*70 + "\n")


def main():
    if len(sys.argv) < 3:
        print("Usage: python validate_clone.py <content_directory> <generated_html>")
        print("\nExample:")
        print("  python validate_clone.py lovable_clone lovable-clone.html")
        sys.exit(1)
    
    content_dir = sys.argv[1]
    generated_html = sys.argv[2]
    
    try:
        validator = CloneValidator(content_dir, generated_html)
        report = validator.run_validation()
        
        # Save report
        report_path = Path(generated_html).parent / f"{Path(generated_html).stem}_validation.json"
        report_path.write_text(json.dumps(report, indent=2), encoding='utf-8')
        print(f"📄 Detailed report saved: {report_path}")
        
        # Exit code based on status
        sys.exit(0 if report['overall_status'] in ['PASS', 'WARN'] else 1)
        
    except Exception as e:
        print(f"❌ Validation error: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
