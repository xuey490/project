#!/usr/bin/env python3
"""
Image search utility for replacing placeholder images in HTML/TSX/JSX files.
Supports both HTML and React/TSX syntax.
Uses picsum.photos as the default image service.
"""

import re
import json
import sys
from typing import List, Dict, Tuple
from urllib.parse import quote


def generate_picsum_url(query: str, width: int = 800, height: int = 600) -> str:
    """
    Generate Picsum Photos URL with random parameter based on query.
    
    Args:
        query: Search query/topic for the image (used to generate random number)
        width: Image width in pixels
        height: Image height in pixels
        
    Returns:
        Picsum Photos URL
    """
    # Use hash of query as random number for consistent images per query
    random_num = abs(hash(query)) % 10000
    return f"https://picsum.photos/{width}/{height}?random={random_num}"


def extract_dimensions_from_url(url: str) -> Tuple[int, int]:
    """
    Extract width and height from placehold.co URL.
    
    Args:
        url: placehold.co URL like https://placehold.co/600x400
        
    Returns:
        Tuple of (width, height)
    """
    size_match = re.search(r'(\d+)x(\d+)', url)
    if size_match:
        return int(size_match.group(1)), int(size_match.group(2))
    return 800, 600


def generate_search_query_from_alt(alt_text: str) -> str:
    """
    Generate a concise search query from alt text.
    
    Args:
        alt_text: Alt text description
        
    Returns:
        Optimized search query
    """
    if not alt_text or alt_text.lower() in ['image', 'photo', 'picture', 'background', 'placeholder']:
        return 'nature landscape'
    
    # Remove common articles and prepositions
    stop_words = {'a', 'an', 'the', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are'}
    
    # Split and filter
    words = alt_text.lower().split()
    keywords = [w for w in words if w not in stop_words and len(w) > 2]
    
    # Take first 3-4 most meaningful words
    query = ' '.join(keywords[:4])
    
    return query if query else alt_text


def find_nearby_alt_text(content: str, url_start: int, url_end: int) -> str:
    """
    Find alt text near a URL in the content.
    Searches within 200 characters before and after the URL.
    
    Args:
        content: Full file content
        url_start: Start position of URL
        url_end: End position of URL
        
    Returns:
        Alt text if found, empty string otherwise
    """
    # Search range: 200 chars before and after
    search_start = max(0, url_start - 200)
    search_end = min(len(content), url_end + 200)
    context = content[search_start:search_end]
    
    # Patterns to find alt text (in order of preference)
    patterns = [
        # HTML/JSX: alt="..." or alt='...'
        r'alt=["\']([^"\']+)["\']',
        # JSX: alt={"..."} or alt={'...'}
        r'alt=\{["\']([^"\']+)["\']\}',
        # aria-label
        r'aria-label=["\']([^"\']+)["\']',
        # title
        r'title=["\']([^"\']+)["\']',
    ]
    
    for pattern in patterns:
        match = re.search(pattern, context, re.IGNORECASE)
        if match:
            return match.group(1)
    
    return ''


def find_all_placeholder_urls(content: str) -> List[Dict]:
    """
    Find all placehold.co URLs in content (HTML, TSX, JSX).
    Each occurrence is tracked separately (even same URL with different alt).
    
    Args:
        content: File content
        
    Returns:
        List of placeholder info dicts
    """
    placeholders = []
    
    # Pattern 1: URLs in quotes - "https://...", 'https://...', `https://...`
    url_pattern_quoted = r'(["\'\`])(https://placehold\.co/[^\s"\'`<>)]+)\1'
    
    # Pattern 2: URLs in url() without quotes - url(https://...)
    url_pattern_url_func = r'url\((https://placehold\.co/[^\s"\'`<>)]+)\)'
    
    # Find quoted URLs
    for match in re.finditer(url_pattern_quoted, content):
        quote_char = match.group(1)
        url = match.group(2)
        
        # Extract dimensions
        width, height = extract_dimensions_from_url(url)
        
        # Find nearby alt text
        alt = find_nearby_alt_text(content, match.start(), match.end())
        
        placeholders.append({
            'url': url,
            'width': width,
            'height': height,
            'alt': alt,
            'position': match.start(),
            'full_match': match.group(0),
            'replacement_template': f'{quote_char}{{url}}{quote_char}'
        })
    
    # Find url() URLs (for background-image)
    for match in re.finditer(url_pattern_url_func, content):
        url = match.group(1)
        
        # Skip if already found as quoted URL
        if any(p['url'] == url and abs(p['position'] - match.start()) < 10 for p in placeholders):
            continue
        
        # Extract dimensions
        width, height = extract_dimensions_from_url(url)
        
        # Background images default to abstract background query
        alt = 'abstract background'
        
        placeholders.append({
            'url': url,
            'width': width,
            'height': height,
            'alt': alt,
            'position': match.start(),
            'full_match': match.group(0),
            'replacement_template': 'url({url})'
        })
    
    return placeholders


def replace_placeholder_images(
    content: str,
    bg_query: str = 'abstract background'
) -> Tuple[str, List[Dict]]:
    """
    Replace all placehold.co URLs with picsum.photos URLs.
    Each occurrence is replaced individually, even if same URL appears multiple times.
    
    Args:
        content: File content (HTML or TSX/JSX)
        bg_query: Default query for background images
        
    Returns:
        Tuple of (updated content, list of replacements)
    """
    replacements = []
    
    # Find all placeholder URLs (each occurrence tracked separately)
    placeholders = find_all_placeholder_urls(content)
    
    # Sort by position descending (replace from end to start to preserve positions)
    placeholders.sort(key=lambda x: x['position'], reverse=True)
    
    # Replace each placeholder
    updated_content = content
    for placeholder in placeholders:
        url = placeholder['url']
        width = placeholder['width']
        height = placeholder['height']
        alt = placeholder['alt']
        position = placeholder['position']
        full_match = placeholder['full_match']
        replacement_template = placeholder['replacement_template']
        
        # Generate search query
        if 'background' in alt.lower() or not alt:
            query = bg_query
        else:
            query = generate_search_query_from_alt(alt)
        
        # Generate new URL (use position + alt for unique hash)
        unique_seed = f"{query}_{position}"
        new_url = generate_picsum_url(unique_seed, width, height)
        
        # Build replacement string using template
        new_full_match = replacement_template.format(url=new_url)
        
        # Replace at specific position
        updated_content = (
            updated_content[:position] + 
            new_full_match + 
            updated_content[position + len(full_match):]
        )
        
        replacements.append({
            'original': url,
            'new': new_url,
            'query': query,
            'alt': alt,
            'dimensions': f'{width}x{height}'
        })
    
    # Reverse to show in original order
    replacements.reverse()
    
    return updated_content, replacements


def process_file(
    input_file: str,
    output_file: str = None,
    bg_query: str = 'abstract background'
) -> Dict:
    """
    Process a file to replace all placeholder images.
    
    Args:
        input_file: Path to input file (HTML, TSX, JSX)
        output_file: Path to output file (defaults to input_file)
        bg_query: Query for background images
        
    Returns:
        Dictionary with processing results
    """
    # Read input file
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if any placeholders exist
    if 'placehold.co' not in content:
        return {
            'input_file': input_file,
            'output_file': output_file or input_file,
            'replacements': 0,
            'details': [],
            'message': 'No placehold.co URLs found'
        }
    
    # Replace placeholders
    updated_content, replacements = replace_placeholder_images(content, bg_query)
    
    # Write output file
    output_path = output_file or input_file
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(updated_content)
    
    return {
        'input_file': input_file,
        'output_file': output_path,
        'replacements': len(replacements),
        'details': replacements
    }


def process_directory(
    directory: str,
    extensions: List[str] = None,
    bg_query: str = 'abstract background'
) -> Dict:
    """
    Process all matching files in a directory.
    
    Args:
        directory: Directory path
        extensions: File extensions to process (default: .html, .tsx, .jsx)
        bg_query: Query for background images
        
    Returns:
        Dictionary with processing results
    """
    import os
    
    if extensions is None:
        extensions = ['.html', '.tsx', '.jsx', '.js']
    
    results = {
        'directory': directory,
        'files_processed': 0,
        'total_replacements': 0,
        'files': []
    }
    
    for root, dirs, files in os.walk(directory):
        # Skip node_modules
        if 'node_modules' in root:
            continue
            
        for filename in files:
            if any(filename.endswith(ext) for ext in extensions):
                filepath = os.path.join(root, filename)
                result = process_file(filepath, filepath, bg_query)
                
                if result['replacements'] > 0:
                    results['files_processed'] += 1
                    results['total_replacements'] += result['replacements']
                    results['files'].append({
                        'file': filepath,
                        'replacements': result['replacements']
                    })
    
    return results


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage:")
        print("  python search_images.py <file>              # Process single file (in-place)")
        print("  python search_images.py <file> <output>     # Process file to output")
        print("  python search_images.py --dir <directory>   # Process all files in directory")
        print("")
        print("Examples:")
        print("  python search_images.py frontend/src/pages/Index.tsx")
        print("  python search_images.py --dir frontend/src/pages")
        print("")
        print("Supported file types: .html, .tsx, .jsx, .js")
        sys.exit(1)
    
    # Directory mode
    if sys.argv[1] == '--dir':
        if len(sys.argv) < 3:
            print("Error: directory path required")
            sys.exit(1)
        
        directory = sys.argv[2]
        bg_query = sys.argv[3] if len(sys.argv) > 3 else 'abstract background'
        
        result = process_directory(directory, bg_query=bg_query)
        
        print(f"\n✅ Directory processing complete!")
        print(f"   Directory: {result['directory']}")
        print(f"   Files processed: {result['files_processed']}")
        print(f"   Total replacements: {result['total_replacements']}")
        
        if result['files']:
            print(f"\nFiles modified:")
            for f in result['files']:
                print(f"   {f['file']}: {f['replacements']} replacements")
    
    # Single file mode
    else:
        input_file = sys.argv[1]
        output_file = sys.argv[2] if len(sys.argv) > 2 else None
        bg_query = sys.argv[3] if len(sys.argv) > 3 else 'abstract background'
        
        result = process_file(input_file, output_file, bg_query)
        
        print(f"\n✅ Image replacement complete!")
        print(f"   Input:  {result['input_file']}")
        print(f"   Output: {result['output_file']}")
        print(f"   Replacements: {result['replacements']}")
        
        if result['details']:
            print(f"\nDetails:")
            for r in result['details']:
                print(f"   {r['dimensions']} | alt=\"{r['alt'][:40]}...\" → query=\"{r['query']}\"")
