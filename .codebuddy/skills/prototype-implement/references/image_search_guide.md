# Image Search and Adaptation Guide

This guide explains how to intelligently search for and adapt images in generated code, replacing placeholders with real, contextually appropriate images.

## Overview

The `search_images.py` script automatically:
1. Analyzes alt text descriptions in the generated TSX/JSX files
2. Generates intelligent search queries
3. Fetches high-quality images from **picsum.photos**
4. Replaces all placehold.co placeholders while preserving dimensions

## Usage

### Single File Mode

```bash
python3 scripts/search_images.py frontend/src/pages/Index.tsx
```

### Directory Mode (Recommended)

```bash
# Replace all placehold.co URLs in all TSX files under a directory
python3 scripts/search_images.py --dir frontend/src/pages
```

### Custom Background Query

```bash
python3 scripts/search_images.py --dir frontend/src/pages --bg-query "modern gradient abstract"
```

## Supported File Types

- `.tsx` - TypeScript React components
- `.jsx` - JavaScript React components

## URL Patterns Detected

The script detects and replaces these patterns:

```tsx
// Double quotes
<img src="https://placehold.co/600x400" alt="..." />

// Single quotes
<img src='https://placehold.co/600x400' alt='...' />

// Template literals
<img src={`https://placehold.co/600x400`} alt="..." />

// Background images in url()
style={{ backgroundImage: 'url(https://placehold.co/1920x1080)' }}
```

## How It Works

### 1. Alt Text Analysis

The script extracts alt text from image tags:

```tsx
<img src="https://placehold.co/600x400" alt="Professional business team collaborating in modern office" />
```

### 2. Query Generation

Converts alt text to concise search queries:
- Removes stop words (a, the, in, on, etc.)
- Extracts key descriptive terms
- Limits to 3-4 most meaningful words

Example:
- Alt: "Professional business team collaborating in modern office"
- Query: "professional business team modern office"

### 3. Image Replacement

Replaces placeholder with picsum.photos URL:

```tsx
<img src="https://picsum.photos/seed/abc123/600/400" alt="Professional business team collaborating in modern office" />
```

## Writing Effective Alt Text

For best image search results, write alt text that:

### ✅ DO:
- Be specific and descriptive
- Include key visual elements
- Mention style/mood when relevant
- Use common search terms

**Examples:**
```tsx
{/* Good */}
<img alt="Minimalist workspace with MacBook and coffee cup" />
<img alt="Sunset over mountain landscape with lake reflection" />
<img alt="Fresh organic vegetables on wooden cutting board" />
<img alt="Modern architecture glass building blue sky" />
```

### ❌ DON'T:
- Use generic descriptions
- Include technical details
- Use obscure or uncommon terms
- Be too verbose

**Examples:**
```tsx
{/* Bad */}
<img alt="Image 1" />
<img alt="A very detailed scene showing..." />
<img alt="Picture of a thing" />
```

## Alt Text Patterns by Category

### Business/Corporate
```
"professional business meeting conference room"
"corporate office workspace modern design"
"business people handshake partnership"
"startup team collaboration brainstorming"
```

### Technology
```
"laptop code screen programming workspace"
"smartphone app interface modern design"
"cloud computing network technology"
"data visualization dashboard analytics"
```

### E-commerce/Products
```
"product photography white background"
"luxury watch close-up detail"
"fashion clothing minimal display"
"electronics gadget modern design"
```

### Lifestyle
```
"healthy breakfast bowl fresh ingredients"
"yoga meditation peaceful morning"
"travel adventure mountain hiking"
"cozy home interior scandinavian design"
```

### Abstract/Backgrounds
```
"abstract gradient blue purple"
"geometric pattern modern minimal"
"texture paper white subtle"
"bokeh lights blur background"
```

## Image Dimensions Best Practices

### Common Aspect Ratios

| Use Case | Aspect Ratio | Example Dimensions |
|----------|--------------|-------------------|
| Hero images | 16:9 | 1920x1080, 1600x900 |
| Product cards | 4:3 | 800x600, 1200x900 |
| Profile/Avatar | 1:1 | 400x400, 600x600 |
| Blog thumbnails | 3:2 | 900x600, 1200x800 |
| Mobile hero | 9:16 | 1080x1920 |

### Recommended Sizes

```python
# Hero/Banner
hero_large = "1920x1080"     # Desktop
hero_medium = "1600x900"     # Tablet
hero_small = "800x600"       # Mobile

# Content Images
content_large = "1200x800"   # Full width
content_medium = "800x600"   # Half width
content_small = "400x300"    # Thumbnail

# UI Elements
avatar = "400x400"           # Profile pictures
icon = "200x200"             # Small icons
card_image = "600x400"       # Card thumbnails
```

## Advanced Usage

### Command Line Options

```bash
# Single file with custom background query
python3 scripts/search_images.py file.tsx --bg-query "abstract technology"

# Directory mode
python3 scripts/search_images.py --dir frontend/src/pages

# Directory mode with custom background query
python3 scripts/search_images.py --dir frontend/src/pages --bg-query "modern gradient"
```

## Picsum.photos API

### URL Format
```
https://picsum.photos/seed/{UNIQUE_SEED}/{WIDTH}/{HEIGHT}
```

### Examples
```
# 600x400 image with seed for consistent results
https://picsum.photos/seed/hero123/600/400

# 1920x1080 banner
https://picsum.photos/seed/banner456/1920/1080
```

### Benefits
- No API key required
- Fast and reliable
- Seed-based for consistent images
- High quality photos

## Background Image Handling

The script also handles CSS background images in JSX:

### Inline Styles
```tsx
<div style={{ backgroundImage: 'url(https://placehold.co/1920x1080)' }}>
```

### Processed Result
```tsx
<div style={{ backgroundImage: 'url(https://picsum.photos/seed/bg123/1920/1080)' }}>
```

## Troubleshooting

### Issue: Images not loading
**Solution:** Check image URLs are accessible

### Issue: Dimensions incorrect
**Solution:** Verify placehold.co URLs have correct WIDTHxHEIGHT format

### Issue: Same image repeated
**Solution:** Each occurrence gets a unique seed based on position

## Production Considerations

### Performance
```tsx
// Optimize images
// - Use responsive images with srcset
// - Lazy load below-the-fold images
// - Use CDN for faster delivery
// - Compress images appropriately
```

## Examples

### E-commerce Site
```bash
# Replace all product images
python3 scripts/search_images.py --dir frontend/src/pages

# Alt text examples:
# "luxury leather handbag minimal white background"
# "modern furniture chair scandinavian design"
```

### Portfolio Site
```bash
python3 scripts/search_images.py --dir frontend/src/pages --bg-query "creative design"

# Alt text examples:
# "web design mockup modern interface"
# "brand identity logo design showcase"
```

### Landing Page
```bash
python3 scripts/search_images.py --dir frontend/src/pages --bg-query "technology innovation"

# Alt text examples:
# "startup team collaboration creative workspace"
# "laptop code development programming"
```

## Integration Workflow

1. **Generate React Code** with detailed alt text descriptions
2. **Run Image Search** to replace placeholders: `python3 scripts/search_images.py --dir frontend/src/pages`
3. **Verify** no placehold.co URLs remain: `grep -r "placehold.co" frontend/src/`
4. **Build & Preview** to check images display correctly

This creates a seamless workflow from design → React code → production-ready images!
