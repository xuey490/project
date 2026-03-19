#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Design System Generator - Aggregates search results and applies reasoning
to generate comprehensive design system recommendations.

Usage:
    from design_system import generate_design_system
    result = generate_design_system("SaaS dashboard", "My Project")
    
    # With persistence (Master + Overrides pattern)
    result = generate_design_system("SaaS dashboard", "My Project", persist=True)
    result = generate_design_system("SaaS dashboard", "My Project", persist=True, page="dashboard")
"""

import csv
import json
import math
import os
from datetime import datetime
from pathlib import Path
from core import search, DATA_DIR


# ============ COLOR CONVERSION ============
def hex_to_oklch(hex_color: str) -> str:
    """Convert hex color to oklch() CSS format string.
    
    Uses approximate conversion: hex -> sRGB -> linear RGB -> OKLab -> OKLCH
    """
    hex_color = hex_color.lstrip('#')
    if len(hex_color) == 3:
        hex_color = ''.join([c * 2 for c in hex_color])
    
    # Parse hex to sRGB [0,1]
    r = int(hex_color[0:2], 16) / 255.0
    g = int(hex_color[2:4], 16) / 255.0
    b = int(hex_color[4:6], 16) / 255.0
    
    # sRGB to linear RGB
    def to_linear(c):
        return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    
    lr, lg, lb = to_linear(r), to_linear(g), to_linear(b)
    
    # Linear RGB to OKLab (via LMS)
    l_ = 0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb
    m_ = 0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb
    s_ = 0.0883024619 * lr + 0.2220049168 * lg + 0.6896926213 * lb
    
    l_cbrt = l_ ** (1/3) if l_ >= 0 else -((-l_) ** (1/3))
    m_cbrt = m_ ** (1/3) if m_ >= 0 else -((-m_) ** (1/3))
    s_cbrt = s_ ** (1/3) if s_ >= 0 else -((-s_) ** (1/3))
    
    L = 0.2104542553 * l_cbrt + 0.7936177850 * m_cbrt - 0.0040720468 * s_cbrt
    a = 1.9779984951 * l_cbrt - 2.4285922050 * m_cbrt + 0.4505937099 * s_cbrt
    b_val = 0.0259040371 * l_cbrt + 0.7827717662 * m_cbrt - 0.8086757660 * s_cbrt
    
    # OKLab to OKLCH
    C = math.sqrt(a * a + b_val * b_val)
    H = math.degrees(math.atan2(b_val, a)) % 360
    
    # Round to 3 decimal places
    L = round(L, 3)
    C = round(C, 3)
    H = round(H, 3)
    
    return f"oklch({L} {C} {H})"


# ============ CONFIGURATION ============
REASONING_FILE = "ui-reasoning.csv"

SEARCH_CONFIG = {
    "product": {"max_results": 1},
    "style": {"max_results": 3},
    "color": {"max_results": 5},
    "landing": {"max_results": 2},
    "typography": {"max_results": 2}
}


# ============ DESIGN SYSTEM GENERATOR ============
class DesignSystemGenerator:
    """Generates design system recommendations from aggregated searches."""

    def __init__(self):
        self.reasoning_data = self._load_reasoning()

    def _load_reasoning(self) -> list:
        """Load reasoning rules from CSV."""
        filepath = DATA_DIR / REASONING_FILE
        if not filepath.exists():
            return []
        with open(filepath, 'r', encoding='utf-8') as f:
            return list(csv.DictReader(f))

    def _multi_domain_search(self, query: str, style_priority: list = None) -> dict:
        """Execute searches across multiple domains."""
        results = {}
        for domain, config in SEARCH_CONFIG.items():
            if domain == "style" and style_priority:
                # For style, also search with priority keywords
                priority_query = " ".join(style_priority[:2]) if style_priority else query
                combined_query = f"{query} {priority_query}"
                results[domain] = search(combined_query, domain, config["max_results"])
            else:
                results[domain] = search(query, domain, config["max_results"])
        return results

    def _find_reasoning_rule(self, category: str) -> dict:
        """Find matching reasoning rule for a category."""
        category_lower = category.lower()

        # Try exact match first
        for rule in self.reasoning_data:
            if rule.get("UI_Category", "").lower() == category_lower:
                return rule

        # Try partial match
        for rule in self.reasoning_data:
            ui_cat = rule.get("UI_Category", "").lower()
            if ui_cat in category_lower or category_lower in ui_cat:
                return rule

        # Try keyword match
        for rule in self.reasoning_data:
            ui_cat = rule.get("UI_Category", "").lower()
            keywords = ui_cat.replace("/", " ").replace("-", " ").split()
            if any(kw in category_lower for kw in keywords):
                return rule

        return {}

    def _apply_reasoning(self, category: str, search_results: dict) -> dict:
        """Apply reasoning rules to search results."""
        rule = self._find_reasoning_rule(category)

        if not rule:
            return {
                "pattern": "Hero + Features + CTA",
                "style_priority": ["Minimalism", "Flat Design"],
                "color_mood": "Professional",
                "typography_mood": "Clean",
                "key_effects": "Subtle hover transitions",
                "anti_patterns": "",
                "decision_rules": {},
                "severity": "MEDIUM"
            }

        # Parse decision rules JSON
        decision_rules = {}
        try:
            decision_rules = json.loads(rule.get("Decision_Rules", "{}"))
        except json.JSONDecodeError:
            pass

        return {
            "pattern": rule.get("Recommended_Pattern", ""),
            "style_priority": [s.strip() for s in rule.get("Style_Priority", "").split("+")],
            "color_mood": rule.get("Color_Mood", ""),
            "typography_mood": rule.get("Typography_Mood", ""),
            "key_effects": rule.get("Key_Effects", ""),
            "anti_patterns": rule.get("Anti_Patterns", ""),
            "decision_rules": decision_rules,
            "severity": rule.get("Severity", "MEDIUM")
        }

    def _parse_design_variables(self, raw: str) -> dict:
        """Parse Design System Variables string into a dict.
        
        Input format: '--border-radius: 0px, --transition-duration: 0s, --font-weight: 700-900'
        Output: {'border-radius': '0px', 'transition-duration': '0s', 'font-weight': '700-900'}
        """
        variables = {}
        if not raw:
            return variables
        for pair in raw.split(", "):
            pair = pair.strip()
            if ": " in pair:
                key, value = pair.split(": ", 1)
                key = key.strip().lstrip("-")
                variables[key] = value.strip()
        return variables

    def _select_best_match(self, results: list, priority_keywords: list) -> dict:
        """Select best matching result based on priority keywords."""
        if not results:
            return {}

        if not priority_keywords:
            return results[0]

        # First: try exact style name match
        for priority in priority_keywords:
            priority_lower = priority.lower().strip()
            for result in results:
                style_name = result.get("Style Category", "").lower()
                if priority_lower in style_name or style_name in priority_lower:
                    return result

        # Second: score by keyword match in all fields
        scored = []
        for result in results:
            result_str = str(result).lower()
            score = 0
            for kw in priority_keywords:
                kw_lower = kw.lower().strip()
                # Higher score for style name match
                if kw_lower in result.get("Style Category", "").lower():
                    score += 10
                # Lower score for keyword field match
                elif kw_lower in result.get("Keywords", "").lower():
                    score += 3
                # Even lower for other field matches
                elif kw_lower in result_str:
                    score += 1
            scored.append((score, result))

        scored.sort(key=lambda x: x[0], reverse=True)
        return scored[0][1] if scored and scored[0][0] > 0 else results[0]

    def _select_best_color(self, results: list, color_mood: str, query: str) -> dict:
        """Select best color palette based on color mood and query keywords.
        
        Scores candidates by matching color mood keywords against the Notes field
        and Product Type field, rather than blindly taking the first BM25 result.
        """
        if not results:
            return {}
        
        if not color_mood and not query:
            return results[0]
        
        # Build keyword list from color_mood and query
        mood_keywords = [kw.strip().lower() for kw in (color_mood or "").replace("+", " ").replace(",", " ").split() if kw.strip()]
        query_keywords = [kw.strip().lower() for kw in query.split() if len(kw.strip()) > 2]
        all_keywords = mood_keywords + query_keywords
        
        if not all_keywords:
            return results[0]
        
        scored = []
        for result in results:
            notes = result.get("Notes", "").lower()
            product_type = result.get("Product Type", "").lower()
            score = 0
            
            for kw in all_keywords:
                # High score for product type match
                if kw in product_type:
                    score += 10
                # Medium score for notes match (color mood description)
                if kw in notes:
                    score += 5
            
            scored.append((score, result))
        
        scored.sort(key=lambda x: x[0], reverse=True)
        return scored[0][1] if scored and scored[0][0] > 0 else results[0]

    def _extract_results(self, search_result: dict) -> list:
        """Extract results list from search result dict."""
        return search_result.get("results", [])

    def generate(self, query: str, project_name: str = None) -> dict:
        """Generate complete design system recommendation."""
        # Step 1: First search product to get category
        product_result = search(query, "product", 1)
        product_results = product_result.get("results", [])
        category = "General"
        if product_results:
            category = product_results[0].get("Product Type", "General")

        # Step 2: Get reasoning rules for this category
        reasoning = self._apply_reasoning(category, {})
        style_priority = reasoning.get("style_priority", [])

        # Step 3: Multi-domain search with style priority hints
        search_results = self._multi_domain_search(query, style_priority)
        search_results["product"] = product_result  # Reuse product search

        # Step 4: Select best matches from each domain using priority
        style_results = self._extract_results(search_results.get("style", {}))
        color_results = self._extract_results(search_results.get("color", {}))
        typography_results = self._extract_results(search_results.get("typography", {}))
        landing_results = self._extract_results(search_results.get("landing", {}))

        best_style = self._select_best_match(style_results, reasoning.get("style_priority", []))
        best_color = self._select_best_color(color_results, reasoning.get("color_mood", ""), query)
        best_typography = typography_results[0] if typography_results else {}
        best_landing = landing_results[0] if landing_results else {}

        # Step 5: Build final recommendation
        # Combine effects from both reasoning and style search
        style_effects = best_style.get("Effects & Animation", "")
        reasoning_effects = reasoning.get("key_effects", "")
        combined_effects = style_effects if style_effects else reasoning_effects

        # Step 6: Extract style-specific design variables
        design_vars_raw = best_style.get("Design System Variables", "")
        design_vars = self._parse_design_variables(design_vars_raw)
        css_keywords = best_style.get("CSS/Technical Keywords", "")
        implementation_checklist = best_style.get("Implementation Checklist", "")

        return {
            "project_name": project_name or query.upper(),
            "category": category,
            "pattern": {
                "name": best_landing.get("Pattern Name", reasoning.get("pattern", "Hero + Features + CTA")),
                "sections": best_landing.get("Section Order", "Hero > Features > CTA"),
                "cta_placement": best_landing.get("Primary CTA Placement", "Above fold"),
                "color_strategy": best_landing.get("Color Strategy", ""),
                "conversion": best_landing.get("Conversion Optimization", "")
            },
            "style": {
                "name": best_style.get("Style Category", "Minimalism"),
                "type": best_style.get("Type", "General"),
                "effects": style_effects,
                "keywords": best_style.get("Keywords", ""),
                "best_for": best_style.get("Best For", ""),
                "performance": best_style.get("Performance", ""),
                "accessibility": best_style.get("Accessibility", ""),
                "design_variables": design_vars,
                "design_variables_raw": design_vars_raw,
                "css_keywords": css_keywords,
                "implementation_checklist": implementation_checklist
            },
            "colors": {
                "primary": best_color.get("Primary (Hex)", "#2563EB"),
                "secondary": best_color.get("Secondary (Hex)", "#3B82F6"),
                "cta": best_color.get("CTA (Hex)", "#F97316"),
                "background": best_color.get("Background (Hex)", "#F8FAFC"),
                "text": best_color.get("Text (Hex)", "#1E293B"),
                "notes": best_color.get("Notes", "")
            },
            "typography": {
                "heading": best_typography.get("Heading Font", "Inter"),
                "body": best_typography.get("Body Font", "Inter"),
                "mood": best_typography.get("Mood/Style Keywords", reasoning.get("typography_mood", "")),
                "best_for": best_typography.get("Best For", ""),
                "google_fonts_url": best_typography.get("Google Fonts URL", ""),
                "css_import": best_typography.get("CSS Import", "")
            },
            "key_effects": combined_effects,
            "anti_patterns": reasoning.get("anti_patterns", ""),
            "decision_rules": reasoning.get("decision_rules", {}),
            "severity": reasoning.get("severity", "MEDIUM")
        }


# ============ OUTPUT FORMATTERS ============
BOX_WIDTH = 90  # Wider box for more content

def format_ascii_box(design_system: dict) -> str:
    """Format design system as ASCII box with emojis (MCP-style)."""
    project = design_system.get("project_name", "PROJECT")
    pattern = design_system.get("pattern", {})
    style = design_system.get("style", {})
    colors = design_system.get("colors", {})
    typography = design_system.get("typography", {})
    effects = design_system.get("key_effects", "")
    anti_patterns = design_system.get("anti_patterns", "")

    def wrap_text(text: str, prefix: str, width: int) -> list:
        """Wrap long text into multiple lines."""
        if not text:
            return []
        words = text.split()
        lines = []
        current_line = prefix
        for word in words:
            if len(current_line) + len(word) + 1 <= width - 2:
                current_line += (" " if current_line != prefix else "") + word
            else:
                if current_line != prefix:
                    lines.append(current_line)
                current_line = prefix + word
        if current_line != prefix:
            lines.append(current_line)
        return lines

    # Build sections from pattern
    sections = pattern.get("sections", "").split(">")
    sections = [s.strip() for s in sections if s.strip()]

    # Build output lines
    lines = []
    w = BOX_WIDTH - 1

    lines.append("+" + "-" * w + "+")
    lines.append(f"|  TARGET: {project} - RECOMMENDED DESIGN SYSTEM".ljust(BOX_WIDTH) + "|")
    lines.append("+" + "-" * w + "+")
    lines.append("|" + " " * BOX_WIDTH + "|")

    # Pattern section
    lines.append(f"|  PATTERN: {pattern.get('name', '')}".ljust(BOX_WIDTH) + "|")
    if pattern.get('conversion'):
        lines.append(f"|     Conversion: {pattern.get('conversion', '')}".ljust(BOX_WIDTH) + "|")
    if pattern.get('cta_placement'):
        lines.append(f"|     CTA: {pattern.get('cta_placement', '')}".ljust(BOX_WIDTH) + "|")
    lines.append("|     Sections:".ljust(BOX_WIDTH) + "|")
    for i, section in enumerate(sections, 1):
        lines.append(f"|       {i}. {section}".ljust(BOX_WIDTH) + "|")
    lines.append("|" + " " * BOX_WIDTH + "|")

    # Style section
    lines.append(f"|  STYLE: {style.get('name', '')}".ljust(BOX_WIDTH) + "|")
    if style.get("keywords"):
        for line in wrap_text(f"Keywords: {style.get('keywords', '')}", "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
    if style.get("best_for"):
        for line in wrap_text(f"Best For: {style.get('best_for', '')}", "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
    if style.get("performance") or style.get("accessibility"):
        perf_a11y = f"Performance: {style.get('performance', '')} | Accessibility: {style.get('accessibility', '')}"
        lines.append(f"|     {perf_a11y}".ljust(BOX_WIDTH) + "|")
    lines.append("|" + " " * BOX_WIDTH + "|")

    # Colors section
    lines.append("|  COLORS:".ljust(BOX_WIDTH) + "|")
    lines.append(f"|     Primary:    {colors.get('primary', '')}".ljust(BOX_WIDTH) + "|")
    lines.append(f"|     Secondary:  {colors.get('secondary', '')}".ljust(BOX_WIDTH) + "|")
    lines.append(f"|     CTA:        {colors.get('cta', '')}".ljust(BOX_WIDTH) + "|")
    lines.append(f"|     Background: {colors.get('background', '')}".ljust(BOX_WIDTH) + "|")
    lines.append(f"|     Text:       {colors.get('text', '')}".ljust(BOX_WIDTH) + "|")
    if colors.get("notes"):
        for line in wrap_text(f"Notes: {colors.get('notes', '')}", "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
    lines.append("|" + " " * BOX_WIDTH + "|")

    # Typography section
    lines.append(f"|  TYPOGRAPHY: {typography.get('heading', '')} / {typography.get('body', '')}".ljust(BOX_WIDTH) + "|")
    if typography.get("mood"):
        for line in wrap_text(f"Mood: {typography.get('mood', '')}", "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
    if typography.get("best_for"):
        for line in wrap_text(f"Best For: {typography.get('best_for', '')}", "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
    if typography.get("google_fonts_url"):
        lines.append(f"|     Google Fonts: {typography.get('google_fonts_url', '')}".ljust(BOX_WIDTH) + "|")
    if typography.get("css_import"):
        lines.append(f"|     CSS Import: {typography.get('css_import', '')[:70]}...".ljust(BOX_WIDTH) + "|")
    lines.append("|" + " " * BOX_WIDTH + "|")

    # Key Effects section
    if effects:
        lines.append("|  KEY EFFECTS:".ljust(BOX_WIDTH) + "|")
        for line in wrap_text(effects, "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
        lines.append("|" + " " * BOX_WIDTH + "|")

    # Design Variables section (ASCII box)
    design_vars_raw = style.get("design_variables_raw", "")
    css_keywords = style.get("css_keywords", "")
    if design_vars_raw:
        lines.append("|  DESIGN TOKENS:".ljust(BOX_WIDTH) + "|")
        for pair in design_vars_raw.split(", "):
            pair = pair.strip()
            if pair:
                for line in wrap_text(pair, "|     ", BOX_WIDTH):
                    lines.append(line.ljust(BOX_WIDTH) + "|")
        lines.append("|" + " " * BOX_WIDTH + "|")
    if css_keywords:
        lines.append("|  CSS KEYWORDS:".ljust(BOX_WIDTH) + "|")
        for line in wrap_text(css_keywords, "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
        lines.append("|" + " " * BOX_WIDTH + "|")

    # Anti-patterns section
    if anti_patterns:
        lines.append("|  AVOID (Anti-patterns):".ljust(BOX_WIDTH) + "|")
        for line in wrap_text(anti_patterns, "|     ", BOX_WIDTH):
            lines.append(line.ljust(BOX_WIDTH) + "|")
        lines.append("|" + " " * BOX_WIDTH + "|")

    # Pre-Delivery Checklist section
    lines.append("|  PRE-DELIVERY CHECKLIST:".ljust(BOX_WIDTH) + "|")
    checklist_items = [
        "[ ] No emojis as icons (use SVG: Heroicons/Lucide)",
        "[ ] cursor-pointer on all clickable elements",
        "[ ] Hover states with smooth transitions (150-300ms)",
        "[ ] Light mode: text contrast 4.5:1 minimum",
        "[ ] Focus states visible for keyboard nav",
        "[ ] prefers-reduced-motion respected",
        "[ ] Responsive: 375px, 768px, 1024px, 1440px"
    ]
    for item in checklist_items:
        lines.append(f"|     {item}".ljust(BOX_WIDTH) + "|")
    lines.append("|" + " " * BOX_WIDTH + "|")

    lines.append("+" + "-" * w + "+")

    return "\n".join(lines)


def format_markdown(design_system: dict) -> str:
    """Format design system as markdown."""
    project = design_system.get("project_name", "PROJECT")
    pattern = design_system.get("pattern", {})
    style = design_system.get("style", {})
    colors = design_system.get("colors", {})
    typography = design_system.get("typography", {})
    effects = design_system.get("key_effects", "")
    anti_patterns = design_system.get("anti_patterns", "")

    lines = []
    lines.append(f"## Design System: {project}")
    lines.append("")

    # Pattern section
    lines.append("### Pattern")
    lines.append(f"- **Name:** {pattern.get('name', '')}")
    if pattern.get('conversion'):
        lines.append(f"- **Conversion Focus:** {pattern.get('conversion', '')}")
    if pattern.get('cta_placement'):
        lines.append(f"- **CTA Placement:** {pattern.get('cta_placement', '')}")
    if pattern.get('color_strategy'):
        lines.append(f"- **Color Strategy:** {pattern.get('color_strategy', '')}")
    lines.append(f"- **Sections:** {pattern.get('sections', '')}")
    lines.append("")

    # Style section
    lines.append("### Style")
    lines.append(f"- **Name:** {style.get('name', '')}")
    if style.get('keywords'):
        lines.append(f"- **Keywords:** {style.get('keywords', '')}")
    if style.get('best_for'):
        lines.append(f"- **Best For:** {style.get('best_for', '')}")
    if style.get('performance') or style.get('accessibility'):
        lines.append(f"- **Performance:** {style.get('performance', '')} | **Accessibility:** {style.get('accessibility', '')}")
    lines.append("")

    # Style Design Tokens (markdown)
    design_vars_raw = style.get("design_variables_raw", "")
    css_keywords = style.get("css_keywords", "")
    if design_vars_raw:
        lines.append("### Design Tokens")
        lines.append("")
        lines.append("```css")
        lines.append(":root {")
        for pair in design_vars_raw.split(", "):
            pair = pair.strip()
            if ": " in pair:
                lines.append(f"  {pair};")
        lines.append("}")
        lines.append("```")
        lines.append("")
    if css_keywords:
        lines.append("### CSS Implementation Keywords")
        lines.append("")
        lines.append(f"`{css_keywords}`")
        lines.append("")

    # Colors section
    lines.append("### Colors")
    lines.append(f"| Role | Hex | oklch |")
    lines.append(f"|------|-----|-------|")
    for role in ['primary', 'secondary', 'cta', 'background', 'text']:
        hex_val = colors.get(role, '')
        oklch_val = hex_to_oklch(hex_val) if hex_val else ''
        lines.append(f"| {role.title()} | {hex_val} | {oklch_val} |")
    if colors.get("notes"):
        lines.append(f"\n*Notes: {colors.get('notes', '')}*")
    lines.append("")

    # Typography section
    lines.append("### Typography")
    lines.append(f"- **Heading:** {typography.get('heading', '')}")
    lines.append(f"- **Body:** {typography.get('body', '')}")
    if typography.get("mood"):
        lines.append(f"- **Mood:** {typography.get('mood', '')}")
    if typography.get("best_for"):
        lines.append(f"- **Best For:** {typography.get('best_for', '')}")
    if typography.get("google_fonts_url"):
        lines.append(f"- **Google Fonts:** {typography.get('google_fonts_url', '')}")
    if typography.get("css_import"):
        lines.append(f"- **CSS Import:**")
        lines.append(f"```css")
        lines.append(f"{typography.get('css_import', '')}")
        lines.append(f"```")
    lines.append("")

    # Key Effects section
    if effects:
        lines.append("### Key Effects")
        lines.append(f"{effects}")
        lines.append("")

    # Anti-patterns section
    if anti_patterns:
        lines.append("### Avoid (Anti-patterns)")
        newline_bullet = '\n- '
        lines.append(f"- {anti_patterns.replace(' + ', newline_bullet)}")
        lines.append("")

    # Pre-Delivery Checklist section
    lines.append("### Pre-Delivery Checklist")
    lines.append("- [ ] No emojis as icons (use SVG: Heroicons/Lucide)")
    lines.append("- [ ] cursor-pointer on all clickable elements")
    lines.append("- [ ] Hover states with smooth transitions (150-300ms)")
    lines.append("- [ ] Light mode: text contrast 4.5:1 minimum")
    lines.append("- [ ] Focus states visible for keyboard nav")
    lines.append("- [ ] prefers-reduced-motion respected")
    lines.append("- [ ] Responsive: 375px, 768px, 1024px, 1440px")
    lines.append("")

    return "\n".join(lines)


# ============ MAIN ENTRY POINT ============
def generate_design_system(query: str, project_name: str = None, output_format: str = "ascii", 
                           persist: bool = False, page: str = None, output_dir: str = None) -> str:
    """
    Main entry point for design system generation.

    Args:
        query: Search query (e.g., "SaaS dashboard", "e-commerce luxury")
        project_name: Optional project name for output header
        output_format: "ascii" (default) or "markdown"
        persist: If True, save design system to design-system/ folder
        page: Optional page name for page-specific override file
        output_dir: Optional output directory (defaults to current working directory)

    Returns:
        Formatted design system string
    """
    generator = DesignSystemGenerator()
    design_system = generator.generate(query, project_name)
    
    # Persist to files if requested
    if persist:
        persist_design_system(design_system, page, output_dir, query)

    if output_format == "markdown":
        return format_markdown(design_system)
    return format_ascii_box(design_system)


# ============ PERSISTENCE FUNCTIONS ============
def persist_design_system(design_system: dict, page: str = None, output_dir: str = None, page_query: str = None) -> dict:
    """
    Persist design system to design-system/<project>/ folder using Master + Overrides pattern.
    
    Args:
        design_system: The generated design system dictionary
        page: Optional page name for page-specific override file
        output_dir: Optional output directory (defaults to current working directory)
        page_query: Optional query string for intelligent page override generation
    
    Returns:
        dict with created file paths and status
    """
    base_dir = Path(output_dir) if output_dir else Path.cwd()
    
    # Use project name for project-specific folder
    project_name = design_system.get("project_name", "default")
    project_slug = project_name.lower().replace(' ', '-')
    
    design_system_dir = base_dir / "design-system" / project_slug
    pages_dir = design_system_dir / "pages"
    
    created_files = []
    
    # Create directories
    design_system_dir.mkdir(parents=True, exist_ok=True)
    pages_dir.mkdir(parents=True, exist_ok=True)
    
    master_file = design_system_dir / "MASTER.md"
    
    # Generate and write MASTER.md
    master_content = format_master_md(design_system)
    with open(master_file, 'w', encoding='utf-8') as f:
        f.write(master_content)
    created_files.append(str(master_file))
    
    # If page is specified, create page override file with intelligent content
    if page:
        page_file = pages_dir / f"{page.lower().replace(' ', '-')}.md"
        page_content = format_page_override_md(design_system, page, page_query)
        with open(page_file, 'w', encoding='utf-8') as f:
            f.write(page_content)
        created_files.append(str(page_file))
    
    return {
        "status": "success",
        "design_system_dir": str(design_system_dir),
        "created_files": created_files
    }


def _build_shadow_system(design_vars: dict, style_name: str) -> dict:
    """Build a 4-level shadow system from design variables and style name.
    
    Returns dict with keys: sm, md, lg, xl, each containing 'value' and 'usage'.
    """
    style_lower = style_name.lower()
    
    # Check for explicit shadow variables
    shadow_none = design_vars.get("shadow", "")
    shadow_soft_1 = design_vars.get("shadow-soft-1", "")
    shadow_soft_2 = design_vars.get("shadow-soft-2", "")
    shadow_inner = design_vars.get("shadow-inner", "")
    shadow_outer = design_vars.get("shadow-outer", "")
    shadow_offset = design_vars.get("shadow-offset", "")
    shadow_color = design_vars.get("shadow-color", "")
    elevation_1 = design_vars.get("elevation-1", "")
    elevation_2 = design_vars.get("elevation-2", "")
    elevation_3 = design_vars.get("elevation-3", "")
    elevation_4 = design_vars.get("elevation-4", "")
    depth_shadow = design_vars.get("depth-shadow", "")
    
    # If elevation system is defined, use it directly
    if elevation_1 and elevation_2:
        return {
            "sm": {"value": elevation_1, "usage": "Subtle lift"},
            "md": {"value": elevation_2, "usage": "Cards, buttons"},
            "lg": {"value": elevation_3 or "0 10px 20px rgba(0,0,0,0.1)", "usage": "Modals, dropdowns"},
            "xl": {"value": elevation_4 or "0 20px 40px rgba(0,0,0,0.15)", "usage": "Hero, featured cards"}
        }
    
    # No shadow styles (Brutalism, Flat Design, E-Ink) — but NOT Neubrutalism
    if (shadow_none == "none" or "flat" in style_lower or 
        ("brutali" in style_lower and "neubrutali" not in style_lower)):
        return {
            "sm": {"value": "none", "usage": "No shadows in this style"},
            "md": {"value": "none", "usage": "No shadows in this style"},
            "lg": {"value": "none", "usage": "No shadows in this style"},
            "xl": {"value": "none", "usage": "No shadows in this style"}
        }
    
    # Neubrutalism — hard offset shadows
    if "neubrutali" in style_lower:
        offset = shadow_offset or "4px"
        color = shadow_color or "#000"
        return {
            "sm": {"value": f"{offset} {offset} 0px {color}", "usage": "Subtle hard shadow"},
            "md": {"value": f"5px 5px 0px {color}", "usage": "Cards, buttons"},
            "lg": {"value": f"7px 7px 0px {color}", "usage": "Modals, emphasis"},
            "xl": {"value": f"10px 10px 0px {color}", "usage": "Hero, featured"}
        }
    
    # Neumorphism — soft dual shadows
    if "neumorphi" in style_lower or shadow_soft_1:
        s1 = shadow_soft_1 or "-5px -5px 15px rgba(255,255,255,0.8)"
        s2 = shadow_soft_2 or "5px 5px 15px rgba(0,0,0,0.1)"
        return {
            "sm": {"value": f"-2px -2px 6px rgba(255,255,255,0.7), 2px 2px 6px rgba(0,0,0,0.08)", "usage": "Subtle emboss"},
            "md": {"value": f"{s1}, {s2}", "usage": "Cards, buttons"},
            "lg": {"value": f"-8px -8px 20px rgba(255,255,255,0.9), 8px 8px 20px rgba(0,0,0,0.12)", "usage": "Modals, panels"},
            "xl": {"value": f"-12px -12px 30px rgba(255,255,255,0.9), 12px 12px 30px rgba(0,0,0,0.15)", "usage": "Hero, featured"}
        }
    
    # Claymorphism — inner + outer shadows
    if "claymorphi" in style_lower or shadow_inner:
        inner = shadow_inner or "inset -2px -2px 8px rgba(0,0,0,0.1)"
        outer = shadow_outer or "4px 4px 8px rgba(0,0,0,0.1)"
        return {
            "sm": {"value": f"inset -1px -1px 4px rgba(0,0,0,0.05), 2px 2px 4px rgba(0,0,0,0.05)", "usage": "Subtle clay"},
            "md": {"value": f"{inner}, {outer}", "usage": "Cards, buttons"},
            "lg": {"value": f"inset -3px -3px 12px rgba(0,0,0,0.12), 6px 6px 12px rgba(0,0,0,0.12)", "usage": "Modals, panels"},
            "xl": {"value": f"inset -4px -4px 16px rgba(0,0,0,0.15), 8px 8px 16px rgba(0,0,0,0.15)", "usage": "Hero, featured"}
        }
    
    # Glassmorphism — subtle blur-based
    if "glassmorphi" in style_lower or "glass" in style_lower or "spatial" in style_lower:
        return {
            "sm": {"value": "0 2px 8px rgba(0,0,0,0.05)", "usage": "Subtle glass edge"},
            "md": {"value": "0 4px 16px rgba(0,0,0,0.08)", "usage": "Glass cards"},
            "lg": {"value": "0 8px 32px rgba(0,0,0,0.1)", "usage": "Glass modals"},
            "xl": {"value": "0 16px 48px rgba(0,0,0,0.12)", "usage": "Hero glass panels"}
        }
    
    # Depth shadow (from style data)
    if depth_shadow:
        return {
            "sm": {"value": "0 1px 3px rgba(0,0,0,0.05)", "usage": "Subtle lift"},
            "md": {"value": depth_shadow, "usage": "Cards, buttons"},
            "lg": {"value": "0 10px 20px rgba(0,0,0,0.12)", "usage": "Modals, dropdowns"},
            "xl": {"value": "0 20px 40px rgba(0,0,0,0.15)", "usage": "Hero, featured cards"}
        }
    
    # Default shadow system
    return {
        "sm": {"value": "0 1px 2px rgba(0,0,0,0.05)", "usage": "Subtle lift"},
        "md": {"value": "0 4px 6px rgba(0,0,0,0.1)", "usage": "Cards, buttons"},
        "lg": {"value": "0 10px 15px rgba(0,0,0,0.1)", "usage": "Modals, dropdowns"},
        "xl": {"value": "0 20px 25px rgba(0,0,0,0.15)", "usage": "Hero images, featured cards"}
    }


def _scale_radius(radius_str: str, factor: float) -> str:
    """Scale a border-radius value by a factor.
    
    '8px' * 1.5 -> '12px'
    '0px' * anything -> '0px'
    '16-24px' -> take first value and scale
    """
    if not radius_str:
        return f"{int(8 * factor)}px"
    
    # Handle range format like "16-24px"
    radius_str = radius_str.strip()
    if "-" in radius_str and "px" in radius_str:
        parts = radius_str.replace("px", "").split("-")
        try:
            base = int(parts[0])
            return f"{int(base * factor)}px"
        except ValueError:
            return f"{int(8 * factor)}px"
    
    # Handle simple format like "8px"
    try:
        num = int(radius_str.replace("px", "").strip())
        return f"{int(num * factor)}px"
    except ValueError:
        return f"{int(8 * factor)}px"


def _build_page_transition_system(design_vars: dict, style_name: str, effects_text: str) -> dict:
    """Build page transition / animation specs based on style.
    
    Returns dict with:
      - transition_type: str (e.g. 'fade', 'slide', 'none', 'morph', 'glitch')
      - duration: str (e.g. '300ms')
      - easing: str (e.g. 'ease', 'cubic-bezier(...)')
      - css: list[str] — CSS code lines for the page transition
      - notes: list[str] — implementation notes
    """
    style_lower = style_name.lower()
    effects_lower = effects_text.lower()
    
    # Extract relevant variables
    page_transition = design_vars.get("page-transition", "")
    animation_duration = design_vars.get("animation-duration", "")
    transition_duration = design_vars.get("transition-duration", "")
    morph_duration = design_vars.get("morph-duration", "")
    reveal_duration = design_vars.get("reveal-duration", "")
    entrance_animation = design_vars.get("entrance-animation", "")
    scroll_behavior = design_vars.get("scroll-behavior", "")
    breathing_duration = design_vars.get("breathing-duration", "")
    bounce_duration = design_vars.get("bounce-duration", "")
    glitch_duration = design_vars.get("glitch-duration", "")
    parallax_speed_bg = design_vars.get("parallax-speed-bg", "")
    
    # Determine base duration
    dur = (transition_duration or animation_duration or morph_duration 
           or reveal_duration or "300ms")
    # Clean range format like "300-400ms" → "350ms"
    if "-" in dur and "ms" in dur:
        parts = dur.replace("ms", "").split("-")
        try:
            avg = (int(parts[0]) + int(parts[1])) // 2
            dur = f"{avg}ms"
        except (ValueError, IndexError):
            dur = "300ms"
    
    # ==========================================
    # E-Ink / Paper — sharp page turn (check BEFORE generic "none")
    # ==========================================
    if "e-ink" in style_lower or "paper" in style_lower:
        return {
            "transition_type": "page-turn (sharp)",
            "duration": "0ms",
            "easing": "step-end",
            "css": [
                "/* Page Transition: Sharp page turn — no motion blur */",
                ".page-enter {",
                "  animation: page-turn-in 0.01s step-end forwards;",
                "}",
                ".page-exit {",
                "  animation: page-turn-out 0.01s step-end forwards;",
                "}",
                "@keyframes page-turn-in {",
                "  from { clip-path: inset(0 100% 0 0); }",
                "  to { clip-path: inset(0 0 0 0); }",
                "}",
                "@keyframes page-turn-out {",
                "  from { clip-path: inset(0 0 0 0); }",
                "  to { clip-path: inset(0 0 0 100%); }",
                "}",
            ],
            "notes": [
                "No motion blur — transitions should feel like turning a physical page",
                "Grain/noise texture should persist through transition",
            ]
        }
    
    # ==========================================
    # NO TRANSITIONS — Brutalism, Flat
    # ==========================================
    if (transition_duration == "0s" or transition_duration == "none" or 
        "no smooth transition" in effects_lower or "no motion" in effects_lower or
        ("brutali" in style_lower and "neubrutali" not in style_lower)):
        return {
            "transition_type": "none (instant)",
            "duration": "0s",
            "easing": "step-end",
            "css": [
                "/* Page Transition: NONE — Instant switch (style-intentional) */",
                ".page-enter { opacity: 1; }",
                ".page-exit { display: none; }",
            ],
            "notes": [
                "This style intentionally avoids smooth transitions",
                "Page changes should be instant — no fade, no slide",
                "Use `display: none` / `display: block` for page switching",
            ]
        }
    
    # ==========================================
    # Glitch-based — Cyberpunk, Retro-Futurism, RGB Split
    # ==========================================
    if ("cyberpunk" in style_lower or "retro-futuri" in style_lower or 
        "glitch" in effects_lower or "chromatic" in style_lower or
        "rgb split" in style_lower):
        g_dur = glitch_duration or "0.3s"
        return {
            "transition_type": "glitch",
            "duration": g_dur,
            "easing": "steps(8, end)",
            "css": [
                f"/* Page Transition: Glitch — {g_dur} distortion */",
                ".page-enter {",
                f"  animation: glitch-in {g_dur} steps(8, end) forwards;",
                "}",
                ".page-exit {",
                f"  animation: glitch-out {g_dur} steps(8, end) forwards;",
                "}",
                "@keyframes glitch-in {",
                "  0% { clip-path: inset(50% 0 50% 0); filter: hue-rotate(90deg); }",
                "  25% { clip-path: inset(20% 0 60% 0); filter: hue-rotate(180deg); }",
                "  50% { clip-path: inset(70% 0 10% 0); filter: hue-rotate(270deg); }",
                "  75% { clip-path: inset(10% 0 30% 0); filter: hue-rotate(45deg); }",
                "  100% { clip-path: inset(0 0 0 0); filter: none; }",
                "}",
                "@keyframes glitch-out {",
                "  0% { clip-path: inset(0 0 0 0); filter: none; }",
                "  50% { clip-path: inset(30% 0 40% 0); filter: hue-rotate(180deg); }",
                "  100% { opacity: 0; filter: hue-rotate(360deg); }",
                "}",
            ],
            "notes": [
                "Use `steps()` timing for authentic glitch feel",
                "Add RGB offset on transition for chromatic aberration effect",
                "Combine with scanline overlay for full cyberpunk aesthetic",
                "Respect `prefers-reduced-motion` — fallback to instant swap",
            ]
        }
    
    # ==========================================
    # Biomimetic / Organic — breathing reveal (before morph to avoid overlap)
    # ==========================================
    if ("biomimetic" in style_lower or "organic" in style_lower or
        "nature" in style_lower or breathing_duration):
        b_dur = breathing_duration or "400ms"
        return {
            "transition_type": "breathing-reveal",
            "duration": b_dur,
            "easing": "ease-in-out",
            "css": [
                f"/* Page Transition: Breathing reveal — organic {b_dur} */",
                ".page-enter {",
                f"  animation: breathe-in {b_dur} ease-in-out forwards;",
                "}",
                ".page-exit {",
                f"  animation: breathe-out {b_dur} ease-in-out forwards;",
                "}",
                "@keyframes breathe-in {",
                "  0% { opacity: 0; transform: scale(0.98); filter: blur(4px); }",
                "  60% { opacity: 0.8; transform: scale(1.01); filter: blur(1px); }",
                "  100% { opacity: 1; transform: scale(1); filter: blur(0px); }",
                "}",
                "@keyframes breathe-out {",
                "  0% { opacity: 1; transform: scale(1); }",
                "  40% { opacity: 0.6; transform: scale(1.01); }",
                "  100% { opacity: 0; transform: scale(0.98); filter: blur(4px); }",
                "}",
            ],
            "notes": [
                "Mimics natural breathing rhythm — inhale/exhale feel",
                "Slight scale overshoot creates organic, living motion",
                "Use `ease-in-out` to match natural acceleration curves",
            ]
        }
    
    # ==========================================
    # Morph / Liquid Glass — fluid morphing
    # ==========================================
    if ("liquid" in style_lower or "morph" in effects_lower or 
        morph_duration):
        m_dur = morph_duration or dur
        # Clean range format
        if "-" in m_dur and "ms" in m_dur:
            parts = m_dur.replace("ms", "").split("-")
            try:
                avg = (int(parts[0]) + int(parts[1])) // 2
                m_dur = f"{avg}ms"
            except (ValueError, IndexError):
                m_dur = "500ms"
        return {
            "transition_type": "morph (fluid)",
            "duration": m_dur,
            "easing": "cubic-bezier(0.4, 0, 0.2, 1)",
            "css": [
                f"/* Page Transition: Fluid morph — {m_dur} */",
                ".page-enter {",
                f"  animation: morph-in {m_dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                ".page-exit {",
                f"  animation: morph-out {m_dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                "@keyframes morph-in {",
                "  from { opacity: 0; filter: blur(20px); transform: scale(0.95); }",
                "  to { opacity: 1; filter: blur(0px); transform: scale(1); }",
                "}",
                "@keyframes morph-out {",
                "  from { opacity: 1; filter: blur(0px); transform: scale(1); }",
                "  to { opacity: 0; filter: blur(20px); transform: scale(1.05); }",
                "}",
            ],
            "notes": [
                "Elements should feel like they morph/flow between pages",
                "Use `filter: blur()` for fluid transition between states",
                "Consider View Transitions API for shared element morphing",
            ]
        }
    
    # ==========================================
    # Glassmorphism / Spatial UI — blur-based fade
    # ==========================================
    if ("glass" in style_lower or "spatial" in style_lower):
        blur = design_vars.get("blur-amount", "15px")
        return {
            "transition_type": "blur-fade",
            "duration": dur,
            "easing": "cubic-bezier(0.4, 0, 0.2, 1)",
            "css": [
                f"/* Page Transition: Blur-fade — glass-style {dur} */",
                ".page-enter {",
                f"  animation: blur-fade-in {dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                ".page-exit {",
                f"  animation: blur-fade-out {dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                "@keyframes blur-fade-in {",
                f"  from {{ opacity: 0; filter: blur({blur}); }}",
                "  to { opacity: 1; filter: blur(0px); }",
                "}",
                "@keyframes blur-fade-out {",
                f"  from {{ opacity: 1; filter: blur(0px); }}",
                f"  to {{ opacity: 0; filter: blur({blur}); }}",
                "}",
            ],
            "notes": [
                "Frosted glass effect during transition — blur in/out",
                f"Blur amount matches style's backdrop-filter: blur({blur})",
                "Layer transitions to maintain glass depth effect",
            ]
        }
    
    # ==========================================
    # Parallax / Storytelling — scroll-driven
    # ==========================================
    if ("parallax" in style_lower or "storytelling" in style_lower or
        "parallax" in effects_lower):
        r_dur = reveal_duration or dur
        return {
            "transition_type": "scroll-reveal",
            "duration": r_dur,
            "easing": "cubic-bezier(0.25, 0.46, 0.45, 0.94)",
            "css": [
                f"/* Page Transition: Scroll-reveal — parallax-style {r_dur} */",
                ".page-enter {",
                f"  animation: scroll-reveal-in {r_dur} cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;",
                "}",
                ".page-exit {",
                f"  animation: scroll-reveal-out {r_dur} cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;",
                "}",
                "@keyframes scroll-reveal-in {",
                "  from { opacity: 0; transform: translateY(60px); }",
                "  to { opacity: 1; transform: translateY(0); }",
                "}",
                "@keyframes scroll-reveal-out {",
                "  from { opacity: 1; transform: translateY(0); }",
                "  to { opacity: 0; transform: translateY(-30px); }",
                "}",
            ],
            "notes": [
                "Pages reveal from below like scrolling through a narrative",
                "Use Intersection Observer for section-level parallax reveals",
                "Stagger child elements for layered entrance effect",
            ]
        }
    
    # ==========================================
    # Motion-Driven — smooth slide + fade
    # ==========================================
    if ("motion" in style_lower or "page transition" in effects_lower or
        page_transition or entrance_animation):
        return {
            "transition_type": "slide-fade",
            "duration": dur,
            "easing": "cubic-bezier(0.4, 0, 0.2, 1)",
            "css": [
                f"/* Page Transition: Slide-fade — motion-style {dur} */",
                ".page-enter {",
                f"  animation: slide-fade-in {dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                ".page-exit {",
                f"  animation: slide-fade-out {dur} cubic-bezier(0.4, 0, 0.2, 1) forwards;",
                "}",
                "@keyframes slide-fade-in {",
                "  from { opacity: 0; transform: translateX(30px); }",
                "  to { opacity: 1; transform: translateX(0); }",
                "}",
                "@keyframes slide-fade-out {",
                "  from { opacity: 1; transform: translateX(0); }",
                "  to { opacity: 0; transform: translateX(-30px); }",
                "}",
            ],
            "notes": [
                "Forward navigation slides right-to-left, back slides left-to-right",
                "Use View Transitions API where supported for smoother effect",
                "Stagger entrance animations for child elements (50ms delay each)",
            ]
        }
    
    # ==========================================
    # Neumorphism / Claymorphism — soft scale
    # ==========================================
    if ("neumorphi" in style_lower or "claymorphi" in style_lower or
        "soft ui" in style_lower):
        return {
            "transition_type": "soft-scale",
            "duration": dur,
            "easing": "cubic-bezier(0.34, 1.56, 0.64, 1)",
            "css": [
                f"/* Page Transition: Soft scale — {dur} with bounce */",
                ".page-enter {",
                f"  animation: soft-scale-in {dur} cubic-bezier(0.34, 1.56, 0.64, 1) forwards;",
                "}",
                ".page-exit {",
                f"  animation: soft-scale-out {dur} ease-in forwards;",
                "}",
                "@keyframes soft-scale-in {",
                "  from { opacity: 0; transform: scale(0.96); }",
                "  to { opacity: 1; transform: scale(1); }",
                "}",
                "@keyframes soft-scale-out {",
                "  from { opacity: 1; transform: scale(1); }",
                "  to { opacity: 0; transform: scale(1.04); }",
                "}",
            ],
            "notes": [
                "Soft, bouncy feel matching the embossed/debossed aesthetic",
                "Scale origin should be center for balanced soft animation",
                "Keep transitions subtle — neumorphic style avoids dramatic motion",
            ]
        }
    
    # ==========================================
    # Neubrutalism — hard cut with offset
    # ==========================================
    if "neubrutali" in style_lower:
        return {
            "transition_type": "hard-cut",
            "duration": "150ms",
            "easing": "steps(1, end)",
            "css": [
                "/* Page Transition: Hard cut with offset — neubrutalist */",
                ".page-enter {",
                "  animation: hard-cut-in 150ms steps(1, end) forwards;",
                "}",
                ".page-exit {",
                "  animation: hard-cut-out 100ms steps(1, end) forwards;",
                "}",
                "@keyframes hard-cut-in {",
                "  from { transform: translate(4px, 4px); opacity: 0; }",
                "  to { transform: translate(0, 0); opacity: 1; }",
                "}",
                "@keyframes hard-cut-out {",
                "  from { opacity: 1; }",
                "  to { opacity: 0; }",
                "}",
            ],
            "notes": [
                "Quick hard cut — not instant like Brutalism but not smooth either",
                "4px offset matches the hard shadow aesthetic",
                "Single-step animation for intentionally 'chunky' feel",
            ]
        }
    
    # ==========================================
    # Tactile / Deformable — bounce
    # ==========================================
    if ("tactile" in style_lower or "deformable" in style_lower or bounce_duration):
        b_dur = bounce_duration or "400ms"
        return {
            "transition_type": "bounce",
            "duration": b_dur,
            "easing": "cubic-bezier(0.34, 1.56, 0.64, 1)",
            "css": [
                f"/* Page Transition: Bounce — tactile {b_dur} */",
                ".page-enter {",
                f"  animation: bounce-in {b_dur} cubic-bezier(0.34, 1.56, 0.64, 1) forwards;",
                "}",
                ".page-exit {",
                f"  animation: bounce-out 200ms ease-in forwards;",
                "}",
                "@keyframes bounce-in {",
                "  from { opacity: 0; transform: scale(0.9) translateY(10px); }",
                "  to { opacity: 1; transform: scale(1) translateY(0); }",
                "}",
                "@keyframes bounce-out {",
                "  from { opacity: 1; transform: scale(1); }",
                "  to { opacity: 0; transform: scale(0.95); }",
                "}",
            ],
            "notes": [
                "Overshoot creates tactile 'squish' feel on page entry",
                "Exit is quick — focus the delight on entrance",
                "Combine with press-deformation on interactive elements",
            ]
        }
    
    # ==========================================
    # Kinetic Typography — text-driven reveal
    # ==========================================
    if "kinetic" in style_lower:
        return {
            "transition_type": "text-reveal",
            "duration": dur,
            "easing": "cubic-bezier(0.25, 0.46, 0.45, 0.94)",
            "css": [
                f"/* Page Transition: Text reveal — kinetic typography {dur} */",
                ".page-enter {",
                f"  animation: text-reveal-in {dur} cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;",
                "}",
                ".page-exit {",
                "  animation: text-reveal-out 200ms ease-in forwards;",
                "}",
                "@keyframes text-reveal-in {",
                "  from { opacity: 0; clip-path: inset(100% 0 0 0); }",
                "  to { opacity: 1; clip-path: inset(0 0 0 0); }",
                "}",
                "@keyframes text-reveal-out {",
                "  from { opacity: 1; clip-path: inset(0 0 0 0); }",
                "  to { opacity: 0; clip-path: inset(0 0 100% 0); }",
                "}",
            ],
            "notes": [
                "Content reveals upward like a curtain — emphasizes text entry",
                "Stagger heading vs body text (heading first, body delayed 100ms)",
                "Use GSAP ScrollTrigger for scroll-based text animations within page",
            ]
        }
    
    # ==========================================
    # Gen Z Chaos / Maximalism — chaotic
    # ==========================================
    if "chaos" in style_lower or "maximali" in style_lower:
        return {
            "transition_type": "chaos-pop",
            "duration": "250ms",
            "easing": "cubic-bezier(0.68, -0.55, 0.265, 1.55)",
            "css": [
                "/* Page Transition: Chaos pop — maximalist */",
                ".page-enter {",
                "  animation: chaos-in 250ms cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;",
                "}",
                ".page-exit {",
                "  animation: chaos-out 150ms ease-in forwards;",
                "}",
                "@keyframes chaos-in {",
                "  0% { opacity: 0; transform: rotate(-3deg) scale(1.1); }",
                "  60% { transform: rotate(1deg) scale(0.98); }",
                "  100% { opacity: 1; transform: rotate(0) scale(1); }",
                "}",
                "@keyframes chaos-out {",
                "  from { opacity: 1; transform: rotate(0) scale(1); }",
                "  to { opacity: 0; transform: rotate(3deg) scale(0.9); }",
                "}",
            ],
            "notes": [
                "Intentionally 'wrong' — slight rotation for chaotic energy",
                "Overshoot easing creates playful, unpredictable feel",
                "Randomize rotation direction per page change for variety",
            ]
        }
    
    # ==========================================
    # Pixel Art — instant with retro flash
    # ==========================================
    if "pixel" in style_lower:
        return {
            "transition_type": "pixel-flash",
            "duration": "200ms",
            "easing": "steps(4, end)",
            "css": [
                "/* Page Transition: Pixel flash — retro */",
                ".page-enter {",
                "  animation: pixel-in 200ms steps(4, end) forwards;",
                "}",
                ".page-exit {",
                "  animation: pixel-out 100ms steps(2, end) forwards;",
                "}",
                "@keyframes pixel-in {",
                "  0% { opacity: 0; filter: contrast(2) brightness(2); }",
                "  50% { opacity: 1; filter: contrast(1.5) brightness(1.5); }",
                "  100% { opacity: 1; filter: none; }",
                "}",
                "@keyframes pixel-out {",
                "  from { opacity: 1; }",
                "  to { opacity: 0; filter: contrast(2) brightness(0); }",
                "}",
            ],
            "notes": [
                "Stepped animation for authentic pixel art feel",
                "Brightness flash mimics retro screen transitions",
                "No smooth interpolation — use `steps()` timing function",
            ]
        }
    
    # ==========================================
    # DEFAULT — simple crossfade
    # ==========================================
    return {
        "transition_type": "crossfade",
        "duration": dur,
        "easing": "ease",
        "css": [
            f"/* Page Transition: Crossfade — {dur} */",
            ".page-enter {",
            f"  animation: fade-in {dur} ease forwards;",
            "}",
            ".page-exit {",
            f"  animation: fade-out {dur} ease forwards;",
            "}",
            "@keyframes fade-in {",
            "  from { opacity: 0; }",
            "  to { opacity: 1; }",
            "}",
            "@keyframes fade-out {",
            "  from { opacity: 1; }",
            "  to { opacity: 0; }",
            "}",
        ],
        "notes": [
            "Simple crossfade — works for most styles",
            "Adjust duration to match style's transition-duration token",
            "Consider View Transitions API for browser-native support",
        ]
    }


def _map_transition_mode(transition_type: str) -> str:
    """Map the detailed transition_type from _build_page_transition_system
    to one of the 4 framer-motion PageTransition component modes.
    
    Available modes: fade, slide-up, slide-fade, scale
    """
    t = transition_type.lower()
    
    # No animation / instant — use fade (minimal)
    if "none" in t or "instant" in t or "page-turn" in t:
        return "fade"
    
    # Blur-based / morph — use scale (closest feel)
    if "blur" in t or "morph" in t or "fluid" in t:
        return "scale"
    
    # Vertical slide
    if "scroll-reveal" in t or "breathing" in t or "text-reveal" in t:
        return "slide-up"
    
    # Horizontal slide
    if "slide-fade" in t or "hard-cut" in t or "glitch" in t:
        return "slide-fade"
    
    # Scale / bounce / chaos
    if "bounce" in t or "chaos" in t or "scale" in t or "soft-scale" in t:
        return "scale"
    
    # Pixel / retro — use scale for stepped feel
    if "pixel" in t:
        return "scale"
    
    # Default
    return "fade"


def format_master_md(design_system: dict) -> str:
    """Format design system as MASTER.md with hierarchical override logic."""
    project = design_system.get("project_name", "PROJECT")
    pattern = design_system.get("pattern", {})
    style = design_system.get("style", {})
    colors = design_system.get("colors", {})
    typography = design_system.get("typography", {})
    effects = design_system.get("key_effects", "")
    anti_patterns = design_system.get("anti_patterns", "")
    
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    
    lines = []
    
    # Logic header
    lines.append("# Design System Master File")
    lines.append("")
    lines.append("> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.")
    lines.append("> If that file exists, its rules **override** this Master file.")
    lines.append("> If not, strictly follow the rules below.")
    lines.append("")
    lines.append("---")
    lines.append("")
    lines.append(f"**Project:** {project}")
    lines.append(f"**Generated:** {timestamp}")
    lines.append(f"**Category:** {design_system.get('category', 'General')}")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Global Rules section
    lines.append("## Global Rules")
    lines.append("")
    
    # Color Palette
    lines.append("### Color Palette")
    lines.append("")
    lines.append("> **IMPORTANT:** When writing CSS variables to `index.css`, use the `oklch()` values below, NOT Hex.")
    lines.append("")
    
    primary_hex = colors.get('primary', '#2563EB')
    secondary_hex = colors.get('secondary', '#3B82F6')
    cta_hex = colors.get('cta', '#F97316')
    bg_hex = colors.get('background', '#F8FAFC')
    text_hex = colors.get('text', '#1E293B')
    
    lines.append("| Role | Hex | oklch | CSS Variable |")
    lines.append("|------|-----|-------|--------------|")
    lines.append(f"| Primary | `{primary_hex}` | `{hex_to_oklch(primary_hex)}` | `--primary` |")
    lines.append(f"| Secondary | `{secondary_hex}` | `{hex_to_oklch(secondary_hex)}` | `--secondary` |")
    lines.append(f"| CTA/Accent | `{cta_hex}` | `{hex_to_oklch(cta_hex)}` | `--accent` |")
    lines.append(f"| Background | `{bg_hex}` | `{hex_to_oklch(bg_hex)}` | `--background` |")
    lines.append(f"| Text | `{text_hex}` | `{hex_to_oklch(text_hex)}` | `--foreground` |")
    lines.append("")
    if colors.get("notes"):
        lines.append(f"**Color Notes:** {colors.get('notes', '')}")
        lines.append("")
    
    # Typography
    lines.append("### Typography")
    lines.append("")
    lines.append(f"- **Heading Font:** {typography.get('heading', 'Inter')}")
    lines.append(f"- **Body Font:** {typography.get('body', 'Inter')}")
    if typography.get("mood"):
        lines.append(f"- **Mood:** {typography.get('mood', '')}")
    if typography.get("google_fonts_url"):
        lines.append(f"- **Google Fonts:** [{typography.get('heading', '')} + {typography.get('body', '')}]({typography.get('google_fonts_url', '')})")
    lines.append("")
    if typography.get("css_import"):
        lines.append("**CSS Import:**")
        lines.append("```css")
        lines.append(typography.get("css_import", ""))
        lines.append("```")
        lines.append("")
    
    # Style-Specific Design Variables
    design_vars = style.get("design_variables", {})
    design_vars_raw = style.get("design_variables_raw", "")
    css_keywords = style.get("css_keywords", "")
    implementation_checklist = style.get("implementation_checklist", "")
    
    # Extract key variables with smart defaults based on style
    dv_border_radius = design_vars.get("border-radius", "8px")
    dv_shadow = design_vars.get("shadow", "")
    dv_transition = design_vars.get("transition-duration", design_vars.get("animation-duration", "200ms"))
    dv_font_weight = design_vars.get("font-weight", "400-700")
    dv_border_width = design_vars.get("border-width", "")
    dv_border_style = design_vars.get("border-style", "")
    
    # Build shadow system from design variables
    shadow_specs = _build_shadow_system(design_vars, style.get("name", ""))
    
    # Spacing Variables
    spacing_base = design_vars.get("spacing", design_vars.get("base-unit", ""))
    block_gap = design_vars.get("block-gap", "")
    lines.append("### Spacing Variables")
    lines.append("")
    lines.append("| Token | Value | Usage |")
    lines.append("|-------|-------|-------|")
    if block_gap:
        lines.append(f"| `--space-xs` | `4px` / `0.25rem` | Tight gaps |")
        lines.append(f"| `--space-sm` | `8px` / `0.5rem` | Icon gaps, inline spacing |")
        lines.append(f"| `--space-md` | `16px` / `1rem` | Standard padding |")
        lines.append(f"| `--space-lg` | `{block_gap}` | Block/section gaps (style-specific) |")
        lines.append(f"| `--space-xl` | `48px` / `3rem` | Section margins |")
        lines.append(f"| `--space-2xl` | `64px` / `4rem` | Hero padding |")
    elif spacing_base:
        lines.append(f"| `--space-xs` | `4px` / `0.25rem` | Tight gaps |")
        lines.append(f"| `--space-sm` | `8px` / `0.5rem` | Icon gaps, inline spacing |")
        lines.append(f"| `--space-md` | `{spacing_base}` | Standard padding (style base) |")
        lines.append(f"| `--space-lg` | `24px` / `1.5rem` | Section padding |")
        lines.append(f"| `--space-xl` | `32px` / `2rem` | Large gaps |")
        lines.append(f"| `--space-2xl` | `48px` / `3rem` | Section margins |")
        lines.append(f"| `--space-3xl` | `64px` / `4rem` | Hero padding |")
    else:
        lines.append("| `--space-xs` | `4px` / `0.25rem` | Tight gaps |")
        lines.append("| `--space-sm` | `8px` / `0.5rem` | Icon gaps, inline spacing |")
        lines.append("| `--space-md` | `16px` / `1rem` | Standard padding |")
        lines.append("| `--space-lg` | `24px` / `1.5rem` | Section padding |")
        lines.append("| `--space-xl` | `32px` / `2rem` | Large gaps |")
        lines.append("| `--space-2xl` | `48px` / `3rem` | Section margins |")
        lines.append("| `--space-3xl` | `64px` / `4rem` | Hero padding |")
    lines.append("")
    
    # Shadow Depths (dynamic based on style)
    lines.append("### Shadow Depths")
    lines.append("")
    lines.append("| Level | Value | Usage |")
    lines.append("|-------|-------|-------|")
    for level, spec in shadow_specs.items():
        lines.append(f"| `--shadow-{level}` | `{spec['value']}` | {spec['usage']} |")
    lines.append("")
    
    # Style-Specific Design Tokens
    if design_vars_raw:
        lines.append("### Style-Specific Design Tokens")
        lines.append("")
        lines.append(f"> **Style:** {style.get('name', '')} — these tokens define the unique visual identity of this style.")
        lines.append("")
        lines.append("```css")
        lines.append(":root {")
        for pair in design_vars_raw.split(", "):
            pair = pair.strip()
            if ": " in pair:
                lines.append(f"  {pair};")
        lines.append("}")
        lines.append("```")
        lines.append("")
    
    # CSS Technical Keywords
    if css_keywords:
        lines.append("### CSS Implementation Keywords")
        lines.append("")
        lines.append(f"> Key CSS properties and techniques for this style:")
        lines.append("")
        lines.append(f"`{css_keywords}`")
        lines.append("")
    
    # Component Specs section (dynamic)
    lines.append("---")
    lines.append("")
    lines.append("## Component Specs")
    lines.append("")
    
    # Derive component-specific values from design variables
    btn_radius = dv_border_radius.split("-")[0] if "-" in dv_border_radius else dv_border_radius
    card_radius = _scale_radius(dv_border_radius, 1.5)
    modal_radius = _scale_radius(dv_border_radius, 2.0)
    transition_val = dv_transition.split("-")[0] if "-" in dv_transition else dv_transition
    # Clean transition value
    if not any(c.isdigit() for c in transition_val):
        transition_val = "200ms"
    transition_css = f"all {transition_val} ease"
    border_css = ""
    if dv_border_width and dv_border_width != "":
        border_css = f"  border: {dv_border_width} solid currentColor;\n"
    
    # Derive font-weight
    font_weight_parts = dv_font_weight.split("-")
    btn_font_weight = font_weight_parts[-1] if len(font_weight_parts) > 1 else "600"
    try:
        int(btn_font_weight)
    except ValueError:
        btn_font_weight = "600"
    
    # Buttons
    lines.append("### Buttons")
    lines.append("")
    lines.append("```css")
    lines.append("/* Primary Button */")
    lines.append(".btn-primary {")
    lines.append(f"  background: {hex_to_oklch(cta_hex)};")
    lines.append("  color: white;")
    lines.append("  padding: 12px 24px;")
    lines.append(f"  border-radius: {btn_radius};")
    lines.append(f"  font-weight: {btn_font_weight};")
    if border_css:
        lines.append(f"{border_css.rstrip()}")
    lines.append(f"  transition: {transition_css};")
    lines.append("  cursor: pointer;")
    lines.append("}")
    lines.append("")
    lines.append(".btn-primary:hover {")
    # Style-aware hover
    if "0s" in transition_val or "none" in transition_val.lower():
        lines.append("  filter: invert(1);")
    else:
        lines.append("  opacity: 0.9;")
        lines.append("  transform: translateY(-1px);")
    lines.append("}")
    lines.append("")
    lines.append("/* Secondary Button */")
    lines.append(".btn-secondary {")
    lines.append(f"  background: transparent;")
    lines.append(f"  color: {hex_to_oklch(primary_hex)};")
    lines.append(f"  border: {dv_border_width or '2px'} solid {hex_to_oklch(primary_hex)};")
    lines.append("  padding: 12px 24px;")
    lines.append(f"  border-radius: {btn_radius};")
    lines.append(f"  font-weight: {btn_font_weight};")
    lines.append(f"  transition: {transition_css};")
    lines.append("  cursor: pointer;")
    lines.append("}")
    lines.append("```")
    lines.append("")
    
    # Cards
    card_shadow = shadow_specs.get("md", {}).get("value", "0 4px 6px rgba(0,0,0,0.1)")
    card_hover_shadow = shadow_specs.get("lg", {}).get("value", "0 10px 15px rgba(0,0,0,0.1)")
    # Special handling for glass/blur styles
    glass_bg = design_vars.get("glass-bg", "")
    glass_opacity = design_vars.get("glass-opacity", "")
    blur_amount = design_vars.get("blur-amount", design_vars.get("glass-blur", ""))
    border_color = design_vars.get("border-color", "")
    # Construct proper glass background if we have opacity but no full bg value
    if not glass_bg and glass_opacity:
        glass_bg = f"rgba(255, 255, 255, {glass_opacity})"
    
    lines.append("### Cards")
    lines.append("")
    lines.append("```css")
    lines.append(".card {")
    if glass_bg:
        lines.append(f"  background: {glass_bg};")
        if blur_amount:
            lines.append(f"  backdrop-filter: blur({blur_amount});")
            lines.append(f"  -webkit-backdrop-filter: blur({blur_amount});")
        if border_color:
            lines.append(f"  border: 1px solid {border_color};")
    else:
        lines.append(f"  background: {hex_to_oklch(bg_hex)};")
    lines.append(f"  border-radius: {card_radius};")
    if border_css and not glass_bg:
        lines.append(f"{border_css.rstrip()}")
    lines.append("  padding: 24px;")
    lines.append(f"  box-shadow: {card_shadow};")
    lines.append(f"  transition: {transition_css};")
    lines.append("  cursor: pointer;")
    lines.append("}")
    lines.append("")
    lines.append(".card:hover {")
    if "0s" in transition_val or "none" in transition_val.lower():
        lines.append(f"  box-shadow: {card_hover_shadow};")
    else:
        lines.append(f"  box-shadow: {card_hover_shadow};")
        lines.append("  transform: translateY(-2px);")
    lines.append("}")
    lines.append("```")
    lines.append("")
    
    # Inputs
    lines.append("### Inputs")
    lines.append("")
    lines.append("```css")
    lines.append(".input {")
    lines.append("  padding: 12px 16px;")
    if glass_bg:
        lines.append(f"  background: {glass_bg};")
        if blur_amount:
            lines.append(f"  backdrop-filter: blur({blur_amount});")
        lines.append(f"  border: 1px solid {border_color or 'rgba(255,255,255,0.2)'};")
    else:
        lines.append(f"  border: {dv_border_width or '1px'} solid #E2E8F0;")
    lines.append(f"  border-radius: {btn_radius};")
    lines.append("  font-size: 16px;")
    lines.append(f"  transition: border-color {transition_val} ease;")
    lines.append("}")
    lines.append("")
    lines.append(".input:focus {")
    lines.append(f"  border-color: {hex_to_oklch(primary_hex)};")
    lines.append("  outline: none;")
    lines.append(f"  box-shadow: 0 0 0 3px {hex_to_oklch(primary_hex)};")
    lines.append("}")
    lines.append("```")
    lines.append("")
    
    # Modals
    lines.append("### Modals")
    lines.append("")
    lines.append("```css")
    lines.append(".modal-overlay {")
    lines.append("  background: rgba(0, 0, 0, 0.5);")
    if blur_amount:
        lines.append(f"  backdrop-filter: blur({blur_amount});")
    else:
        lines.append("  backdrop-filter: blur(4px);")
    lines.append("}")
    lines.append("")
    lines.append(".modal {")
    if glass_bg:
        lines.append(f"  background: {glass_bg};")
        if blur_amount:
            lines.append(f"  backdrop-filter: blur({blur_amount});")
        if border_color:
            lines.append(f"  border: 1px solid {border_color};")
    else:
        lines.append("  background: white;")
    lines.append(f"  border-radius: {modal_radius};")
    if border_css and not glass_bg:
        lines.append(f"{border_css.rstrip()}")
    lines.append("  padding: 32px;")
    lines.append(f"  box-shadow: {shadow_specs.get('xl', {}).get('value', '0 20px 25px rgba(0,0,0,0.15)')};")
    lines.append("  max-width: 500px;")
    lines.append("  width: 90%;")
    lines.append("}")
    lines.append("```")
    lines.append("")
    
    # Page Transition / Animation Specs (dynamic per style)
    page_transition = _build_page_transition_system(design_vars, style.get("name", ""), effects)
    
    # Map the detailed transition_type to a PageTransition component mode
    transition_mode = _map_transition_mode(page_transition["transition_type"])
    
    lines.append("### Page Transitions")
    lines.append("")
    lines.append(f"> **Type:** `{page_transition['transition_type']}` — **Mode:** `{transition_mode}` — **Duration:** `{page_transition['duration']}` — **Easing:** `{page_transition['easing']}`")
    lines.append("")
    lines.append(f"Page transitions use **framer-motion** (`AnimatePresence` + `motion.div`). Use the `transition` prop on `<PageTransition>` to set the mode, and update `:root` token variables to match this style:")
    lines.append("")
    lines.append("```css")
    lines.append(":root {")
    lines.append(f"  --duration-normal: {page_transition['duration']};")
    lines.append(f"  --ease-default: {page_transition['easing']};")
    lines.append("}")
    lines.append("```")
    lines.append("")
    lines.append(f"**Use in App.tsx:**")
    lines.append("```tsx")
    lines.append("<AnimatedRoutes>")
    lines.append(f'  <Route path="/" element={{<PageTransition transition="{transition_mode}"><Index /></PageTransition>}} />')
    lines.append(f'  <Route path="*" element={{<PageTransition transition="fade"><NotFound /></PageTransition>}} />')
    lines.append("</AnimatedRoutes>")
    lines.append("```")
    lines.append("")
    lines.append("> **Available modes:** `fade` | `slide-up` | `slide-fade` | `scale`")
    lines.append("")
    if page_transition["notes"]:
        lines.append("> **Implementation Notes:**")
        for note in page_transition["notes"]:
            lines.append(f"> - {note}")
        lines.append("")
    
    # Implementation Checklist (from style data)
    if implementation_checklist:
        lines.append("### Implementation Checklist")
        lines.append("")
        for item in implementation_checklist.split(", "):
            item = item.strip()
            if item:
                lines.append(f"- {item}")
        lines.append("")
    
    # Style section
    lines.append("---")
    lines.append("")
    lines.append("## Style Guidelines")
    lines.append("")
    lines.append(f"**Style:** {style.get('name', 'Minimalism')}")
    lines.append("")
    if style.get("keywords"):
        lines.append(f"**Keywords:** {style.get('keywords', '')}")
        lines.append("")
    if style.get("best_for"):
        lines.append(f"**Best For:** {style.get('best_for', '')}")
        lines.append("")
    if effects:
        lines.append(f"**Key Effects:** {effects}")
        lines.append("")
    
    # Layout Pattern
    lines.append("### Page Pattern")
    lines.append("")
    lines.append(f"**Pattern Name:** {pattern.get('name', '')}")
    lines.append("")
    if pattern.get('conversion'):
        lines.append(f"- **Conversion Strategy:** {pattern.get('conversion', '')}")
    if pattern.get('cta_placement'):
        lines.append(f"- **CTA Placement:** {pattern.get('cta_placement', '')}")
    lines.append(f"- **Section Order:** {pattern.get('sections', '')}")
    lines.append("")
    
    # Anti-Patterns section
    lines.append("---")
    lines.append("")
    lines.append("## Anti-Patterns (Do NOT Use)")
    lines.append("")
    if anti_patterns:
        anti_list = [a.strip() for a in anti_patterns.split("+")]
        for anti in anti_list:
            if anti:
                lines.append(f"- ❌ {anti}")
    lines.append("")
    lines.append("### Additional Forbidden Patterns")
    lines.append("")
    lines.append("- ❌ **Emojis as icons** — Use SVG icons (Heroicons, Lucide, Simple Icons)")
    lines.append("- ❌ **Missing cursor:pointer** — All clickable elements must have cursor:pointer")
    lines.append("- ❌ **Layout-shifting hovers** — Avoid scale transforms that shift layout")
    lines.append("- ❌ **Low contrast text** — Maintain 4.5:1 minimum contrast ratio")
    lines.append("- ❌ **Instant state changes** — Always use transitions (150-300ms)")
    lines.append("- ❌ **Invisible focus states** — Focus states must be visible for a11y")
    lines.append("")
    
    # Pre-Delivery Checklist
    lines.append("---")
    lines.append("")
    lines.append("## Pre-Delivery Checklist")
    lines.append("")
    lines.append("Before delivering any UI code, verify:")
    lines.append("")
    lines.append("- [ ] No emojis used as icons (use SVG instead)")
    lines.append("- [ ] All icons from consistent icon set (Heroicons/Lucide)")
    lines.append("- [ ] `cursor-pointer` on all clickable elements")
    lines.append("- [ ] Hover states with smooth transitions (150-300ms)")
    lines.append("- [ ] Light mode: text contrast 4.5:1 minimum")
    lines.append("- [ ] Focus states visible for keyboard navigation")
    lines.append("- [ ] `prefers-reduced-motion` respected")
    lines.append("- [ ] Responsive: 375px, 768px, 1024px, 1440px")
    lines.append("- [ ] No content hidden behind fixed navbars")
    lines.append("- [ ] No horizontal scroll on mobile")
    lines.append("")
    
    return "\n".join(lines)


def format_page_override_md(design_system: dict, page_name: str, page_query: str = None) -> str:
    """Format a page-specific override file with intelligent AI-generated content."""
    project = design_system.get("project_name", "PROJECT")
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    page_title = page_name.replace("-", " ").replace("_", " ").title()
    
    # Detect page type and generate intelligent overrides
    page_overrides = _generate_intelligent_overrides(page_name, page_query, design_system)
    
    lines = []
    
    lines.append(f"# {page_title} Page Overrides")
    lines.append("")
    lines.append(f"> **PROJECT:** {project}")
    lines.append(f"> **Generated:** {timestamp}")
    lines.append(f"> **Page Type:** {page_overrides.get('page_type', 'General')}")
    lines.append("")
    lines.append("> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/MASTER.md`).")
    lines.append("> Only deviations from the Master are documented here. For all other rules, refer to the Master.")
    lines.append("")
    lines.append("---")
    lines.append("")
    
    # Page-specific rules with actual content
    lines.append("## Page-Specific Rules")
    lines.append("")
    
    # Layout Overrides
    lines.append("### Layout Overrides")
    lines.append("")
    layout = page_overrides.get("layout", {})
    if layout:
        for key, value in layout.items():
            lines.append(f"- **{key}:** {value}")
    else:
        lines.append("- No overrides — use Master layout")
    lines.append("")
    
    # Spacing Overrides
    lines.append("### Spacing Overrides")
    lines.append("")
    spacing = page_overrides.get("spacing", {})
    if spacing:
        for key, value in spacing.items():
            lines.append(f"- **{key}:** {value}")
    else:
        lines.append("- No overrides — use Master spacing")
    lines.append("")
    
    # Typography Overrides
    lines.append("### Typography Overrides")
    lines.append("")
    typography = page_overrides.get("typography", {})
    if typography:
        for key, value in typography.items():
            lines.append(f"- **{key}:** {value}")
    else:
        lines.append("- No overrides — use Master typography")
    lines.append("")
    
    # Color Overrides
    lines.append("### Color Overrides")
    lines.append("")
    colors = page_overrides.get("colors", {})
    if colors:
        for key, value in colors.items():
            lines.append(f"- **{key}:** {value}")
    else:
        lines.append("- No overrides — use Master colors")
    lines.append("")
    
    # Component Overrides
    lines.append("### Component Overrides")
    lines.append("")
    components = page_overrides.get("components", [])
    if components:
        for comp in components:
            lines.append(f"- {comp}")
    else:
        lines.append("- No overrides — use Master component specs")
    lines.append("")
    
    # Page-Specific Components
    lines.append("---")
    lines.append("")
    lines.append("## Page-Specific Components")
    lines.append("")
    unique_components = page_overrides.get("unique_components", [])
    if unique_components:
        for comp in unique_components:
            lines.append(f"- {comp}")
    else:
        lines.append("- No unique components for this page")
    lines.append("")
    
    # Recommendations
    lines.append("---")
    lines.append("")
    lines.append("## Recommendations")
    lines.append("")
    recommendations = page_overrides.get("recommendations", [])
    if recommendations:
        for rec in recommendations:
            lines.append(f"- {rec}")
    lines.append("")
    
    return "\n".join(lines)


def _generate_intelligent_overrides(page_name: str, page_query: str, design_system: dict) -> dict:
    """
    Generate intelligent overrides based on page type using layered search.
    
    Uses the existing search infrastructure to find relevant style, UX, and layout
    data instead of hardcoded page types.
    """
    from core import search
    
    page_lower = page_name.lower()
    query_lower = (page_query or "").lower()
    combined_context = f"{page_lower} {query_lower}"
    
    # Search across multiple domains for page-specific guidance
    style_search = search(combined_context, "style", max_results=1)
    ux_search = search(combined_context, "ux", max_results=3)
    landing_search = search(combined_context, "landing", max_results=1)
    
    # Extract results from search response
    style_results = style_search.get("results", [])
    ux_results = ux_search.get("results", [])
    landing_results = landing_search.get("results", [])
    
    # Detect page type from search results or context
    page_type = _detect_page_type(combined_context, style_results)
    
    # Build overrides from search results
    layout = {}
    spacing = {}
    typography = {}
    colors = {}
    components = []
    unique_components = []
    recommendations = []
    
    # Extract style-based overrides
    if style_results:
        style = style_results[0]
        style_name = style.get("Style Category", "")
        keywords = style.get("Keywords", "")
        best_for = style.get("Best For", "")
        effects = style.get("Effects & Animation", "")
        
        # Infer layout from style keywords
        if any(kw in keywords.lower() for kw in ["data", "dense", "dashboard", "grid"]):
            layout["Max Width"] = "1400px or full-width"
            layout["Grid"] = "12-column grid for data flexibility"
            spacing["Content Density"] = "High — optimize for information display"
        elif any(kw in keywords.lower() for kw in ["minimal", "simple", "clean", "single"]):
            layout["Max Width"] = "800px (narrow, focused)"
            layout["Layout"] = "Single column, centered"
            spacing["Content Density"] = "Low — focus on clarity"
        else:
            layout["Max Width"] = "1200px (standard)"
            layout["Layout"] = "Full-width sections, centered content"
        
        if effects:
            recommendations.append(f"Effects: {effects}")
    
    # Extract UX guidelines as recommendations
    for ux in ux_results:
        category = ux.get("Category", "")
        do_text = ux.get("Do", "")
        dont_text = ux.get("Don't", "")
        if do_text:
            recommendations.append(f"{category}: {do_text}")
        if dont_text:
            components.append(f"Avoid: {dont_text}")
    
    # Extract landing pattern info for section structure
    if landing_results:
        landing = landing_results[0]
        sections = landing.get("Section Order", "")
        cta_placement = landing.get("Primary CTA Placement", "")
        color_strategy = landing.get("Color Strategy", "")
        
        if sections:
            layout["Sections"] = sections
        if cta_placement:
            recommendations.append(f"CTA Placement: {cta_placement}")
        if color_strategy:
            colors["Strategy"] = color_strategy
    
    # Add page-type specific defaults if no search results
    if not layout:
        layout["Max Width"] = "1200px"
        layout["Layout"] = "Responsive grid"
    
    if not recommendations:
        recommendations = [
            "Refer to MASTER.md for all design rules",
            "Add specific overrides as needed for this page"
        ]
    
    return {
        "page_type": page_type,
        "layout": layout,
        "spacing": spacing,
        "typography": typography,
        "colors": colors,
        "components": components,
        "unique_components": unique_components,
        "recommendations": recommendations
    }


def _detect_page_type(context: str, style_results: list) -> str:
    """Detect page type from context and search results."""
    context_lower = context.lower()
    
    # Check for common page type patterns
    page_patterns = [
        (["dashboard", "admin", "analytics", "data", "metrics", "stats", "monitor", "overview"], "Dashboard / Data View"),
        (["checkout", "payment", "cart", "purchase", "order", "billing"], "Checkout / Payment"),
        (["settings", "profile", "account", "preferences", "config"], "Settings / Profile"),
        (["landing", "marketing", "homepage", "hero", "home", "promo"], "Landing / Marketing"),
        (["login", "signin", "signup", "register", "auth", "password"], "Authentication"),
        (["pricing", "plans", "subscription", "tiers", "packages"], "Pricing / Plans"),
        (["blog", "article", "post", "news", "content", "story"], "Blog / Article"),
        (["product", "item", "detail", "pdp", "shop", "store"], "Product Detail"),
        (["search", "results", "browse", "filter", "catalog", "list"], "Search Results"),
        (["empty", "404", "error", "not found", "zero"], "Empty State"),
    ]
    
    for keywords, page_type in page_patterns:
        if any(kw in context_lower for kw in keywords):
            return page_type
    
    # Fallback: try to infer from style results
    if style_results:
        style_name = style_results[0].get("Style Category", "").lower()
        best_for = style_results[0].get("Best For", "").lower()
        
        if "dashboard" in best_for or "data" in best_for:
            return "Dashboard / Data View"
        elif "landing" in best_for or "marketing" in best_for:
            return "Landing / Marketing"
    
    return "General"


# ============ CLI SUPPORT ============
if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="Generate Design System")
    parser.add_argument("query", help="Search query (e.g., 'SaaS dashboard')")
    parser.add_argument("--project-name", "-p", type=str, default=None, help="Project name")
    parser.add_argument("--format", "-f", choices=["ascii", "markdown"], default="ascii", help="Output format")

    args = parser.parse_args()

    result = generate_design_system(args.query, args.project_name, args.format)
    print(result)
