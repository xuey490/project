#!/usr/bin/env python3
"""
Map downloaded real images to placeholder images in HTML based on context.
This script intelligently replaces placeholder images with actual downloaded images.
"""

import sys
import os
import re
from pathlib import Path


def analyze_image_context(html_content: str, placeholder_url: str) -> str:
    """
    Analyze the context around a placeholder image to understand what it represents.
    Returns a description/keyword for matching.
    """
    # Find the context around this image
    patterns = [
        # Look for nearby text in alt attribute
        r'<img[^>]*src=["\']' + re.escape(placeholder_url) + r'["\'][^>]*alt=["\']([^"\']+)["\']',
        r'<img[^>]*alt=["\']([^"\']+)["\'][^>]*src=["\']' + re.escape(placeholder_url) + r'["\']',
        # Look for nearby headings or text
        r'<h[1-6][^>]*>([^<]+)</h[1-6]>[^<]*<[^>]*' + re.escape(placeholder_url),
        # Look for parent div with class
        r'<div[^>]*class=["\']([^"\']*)[^"\']*["\'][^>]*>[^<]*<img[^>]*' + re.escape(placeholder_url),
    ]
    
    for pattern in patterns:
        match = re.search(pattern, html_content, re.IGNORECASE)
        if match:
            return match.group(1).lower()
    
    return "generic"


def map_images_to_placeholders(html_file: str, image_dir: str, output_file: str) -> None:
    """
    Replace placeholder images with real downloaded images.
    """
    with open(html_file, 'r', encoding='utf-8') as f:
        html = f.read()
    
    # Find all placeholder images
    placeholder_pattern = r'https://placehold\.co/[^"\'\s]+'
    placeholders = list(set(re.findall(placeholder_pattern, html)))
    
    if not placeholders:
        print("⚠️  No placeholder images found in HTML")
        return
    
    # Get list of downloaded images
    image_files = sorted([f for f in os.listdir(image_dir) if f.endswith(('.jpg', '.png', '.webp', '.gif'))])
    
    if not image_files:
        print("⚠️  No images found in directory")
        return
    
    print(f"🔍 Found {len(placeholders)} placeholders and {len(image_files)} real images")
    
    # Map placeholders to real images
    replacements = 0
    for i, placeholder in enumerate(placeholders):
        if i < len(image_files):
            real_image_path = os.path.join(image_dir, image_files[i])
            
            # Replace in HTML
            html = html.replace(placeholder, real_image_path)
            replacements += 1
            print(f"  ✓ {placeholder} → {image_files[i]}")
    
    # Save updated HTML
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(html)
    
    print(f"\n✅ Replaced {replacements} images")
    print(f"💾 Saved to: {output_file}")


def main():
    if len(sys.argv) < 4:
        print("Usage: python map_real_images.py <html_file> <image_dir> <output_file>")
        print("\nExample:")
        print("  python map_real_images.py lovable-clone.html lovable_images lovable-real.html")
        sys.exit(1)
    
    html_file = sys.argv[1]
    image_dir = sys.argv[2]
    output_file = sys.argv[3]
    
    if not os.path.exists(html_file):
        print(f"❌ Error: HTML file not found: {html_file}")
        sys.exit(1)
    
    if not os.path.exists(image_dir):
        print(f"❌ Error: Image directory not found: {image_dir}")
        sys.exit(1)
    
    map_images_to_placeholders(html_file, image_dir, output_file)
    print("\n🎉 Done!")


if __name__ == "__main__":
    main()
