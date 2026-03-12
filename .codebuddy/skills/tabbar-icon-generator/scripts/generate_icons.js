#!/usr/bin/env node
/**
 * TabBar Icon Generator for MiniProgram/Taro
 * 
 * Generate PNG icons for WeChat MiniProgram TabBar.
 * Icons are 81x81px with normal and active color variants.
 * 
 * Usage:
 *   node generate_icons.js -o src/assets/tabbar
 *   node generate_icons.js -o output -i home,user,settings
 *   node generate_icons.js -o output --normal-color "#666666" --active-color "#07C160"
 */

const fs = require('fs');
const path = require('path');

// Parse command line arguments
function parseArgs() {
  const args = process.argv.slice(2);
  const options = {
    output: 'src/assets/tabbar',
    icons: null,
    normalColor: '#999999',
    activeColor: '#07C160',
    size: 81
  };
  
  for (let i = 0; i < args.length; i++) {
    switch (args[i]) {
      case '-o':
      case '--output':
        options.output = args[++i];
        break;
      case '-i':
      case '--icons':
        options.icons = args[++i].split(',').map(s => s.trim());
        break;
      case '--normal-color':
        options.normalColor = args[++i];
        break;
      case '--active-color':
        options.activeColor = args[++i];
        break;
      case '--size':
        options.size = parseInt(args[++i], 10);
        break;
      case '-h':
      case '--help':
        console.log(`
TabBar Icon Generator

Usage: node generate_icons.js [options]

Options:
  -o, --output <dir>      Output directory (default: src/assets/tabbar)
  -i, --icons <list>      Comma-separated icon names (default: all)
  --normal-color <color>  Normal state color (default: #999999)
  --active-color <color>  Active state color (default: #07C160)
  --size <px>             Icon size in pixels (default: 81)
  -h, --help              Show this help

Available icons: home, list, user, settings, search, message, cart, favorite
        `);
        process.exit(0);
    }
  }
  
  return options;
}

// Get SVG content for an icon
function getIconSvg(name, color, size = 81) {
  const icons = {
    'home': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <path d="M40.5 12 L12 36 L12 68 L32 68 L32 48 L49 48 L49 68 L69 68 L69 36 Z" 
          fill="none" stroke="${color}" stroke-width="4" stroke-linejoin="round"/>
  </svg>`,
    
    'home-filled': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <path d="M40.5 12 L12 36 L12 68 L32 68 L32 48 L49 48 L49 68 L69 68 L69 36 Z" 
          fill="${color}" stroke="${color}" stroke-width="4" stroke-linejoin="round"/>
  </svg>`,

    'list': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <rect x="16" y="20" width="49" height="8" rx="2" fill="${color}"/>
    <rect x="16" y="36" width="49" height="8" rx="2" fill="${color}"/>
    <rect x="16" y="52" width="35" height="8" rx="2" fill="${color}"/>
  </svg>`,

    'user': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <circle cx="40.5" cy="28" r="14" fill="none" stroke="${color}" stroke-width="4"/>
    <path d="M16 68 C16 50 28 42 40.5 42 C53 42 65 50 65 68" 
          fill="none" stroke="${color}" stroke-width="4" stroke-linecap="round"/>
  </svg>`,
    
    'user-filled': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <circle cx="40.5" cy="28" r="14" fill="${color}" stroke="${color}" stroke-width="4"/>
    <path d="M16 68 C16 50 28 42 40.5 42 C53 42 65 50 65 68" 
          fill="none" stroke="${color}" stroke-width="4" stroke-linecap="round"/>
  </svg>`,

    'settings': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <circle cx="40.5" cy="40.5" r="12" fill="none" stroke="${color}" stroke-width="4"/>
    <circle cx="40.5" cy="40.5" r="26" fill="none" stroke="${color}" stroke-width="4" stroke-dasharray="8 14"/>
  </svg>`,
    
    'settings-filled': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <circle cx="40.5" cy="40.5" r="12" fill="${color}" stroke="${color}" stroke-width="4"/>
    <circle cx="40.5" cy="40.5" r="26" fill="none" stroke="${color}" stroke-width="4" stroke-dasharray="8 14"/>
  </svg>`,

    'search': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <circle cx="35" cy="35" r="18" fill="none" stroke="${color}" stroke-width="4"/>
    <line x1="48" y1="48" x2="65" y2="65" stroke="${color}" stroke-width="4" stroke-linecap="round"/>
  </svg>`,

    'message': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <rect x="12" y="18" width="57" height="40" rx="4" fill="none" stroke="${color}" stroke-width="4"/>
    <path d="M12 28 L40.5 45 L69 28" fill="none" stroke="${color}" stroke-width="4"/>
  </svg>`,
    
    'message-filled': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <rect x="12" y="18" width="57" height="40" rx="4" fill="${color}" stroke="${color}" stroke-width="4"/>
    <path d="M12 28 L40.5 45 L69 28" fill="none" stroke="#ffffff" stroke-width="4"/>
  </svg>`,

    'cart': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <path d="M16 20 L24 20 L32 52 L60 52 L68 28 L28 28" fill="none" stroke="${color}" stroke-width="4" stroke-linejoin="round"/>
    <circle cx="36" cy="64" r="5" fill="${color}"/>
    <circle cx="56" cy="64" r="5" fill="${color}"/>
  </svg>`,

    'favorite': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <path d="M40.5 65 C25 52 12 42 12 28 C12 18 20 12 30 12 C36 12 40.5 16 40.5 16 C40.5 16 45 12 51 12 C61 12 69 18 69 28 C69 42 56 52 40.5 65 Z" 
          fill="none" stroke="${color}" stroke-width="4"/>
  </svg>`,
    
    'favorite-filled': `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 81 81">
    <path d="M40.5 65 C25 52 12 42 12 28 C12 18 20 12 30 12 C36 12 40.5 16 40.5 16 C40.5 16 45 12 51 12 C61 12 69 18 69 28 C69 42 56 52 40.5 65 Z" 
          fill="${color}" stroke="${color}" stroke-width="4"/>
  </svg>`
  };
  
  return icons[name] || '';
}

// Load or install sharp
async function getSharp() {
  try {
    return require('sharp');
  } catch (e) {
    console.log('Installing sharp...');
    const { execSync } = require('child_process');
    execSync('npm install sharp --no-save', { stdio: 'inherit' });
    return require('sharp');
  }
}

// Generate icons
async function generateIcons(options) {
  const allIcons = ['home', 'list', 'user', 'settings', 'search', 'message', 'cart', 'favorite'];
  const iconList = options.icons 
    ? options.icons.filter(i => allIcons.includes(i))
    : allIcons;
  
  if (iconList.length === 0) {
    console.log(`No valid icons specified. Available: ${allIcons.join(', ')}`);
    return;
  }
  
  // Ensure output directory exists
  if (!fs.existsSync(options.output)) {
    fs.mkdirSync(options.output, { recursive: true });
  }
  
  console.log(`Generating icons in: ${options.output}`);
  console.log(`Normal color: ${options.normalColor}, Active color: ${options.activeColor}`);
  
  const sharp = await getSharp();
  
  for (const iconName of iconList) {
    // Normal state
    const svgNormal = getIconSvg(iconName, options.normalColor, options.size);
    if (svgNormal) {
      const outputNormal = path.join(options.output, `${iconName}.png`);
      await sharp(Buffer.from(svgNormal)).png().toFile(outputNormal);
      console.log(`  Generated: ${iconName}.png`);
    }
    
    // Active state (use filled variant if available)
    const filledName = `${iconName}-filled`;
    const svgActive = getIconSvg(filledName, options.activeColor, options.size) 
      || getIconSvg(iconName, options.activeColor, options.size);
    if (svgActive) {
      const outputActive = path.join(options.output, `${iconName}-active.png`);
      await sharp(Buffer.from(svgActive)).png().toFile(outputActive);
      console.log(`  Generated: ${iconName}-active.png`);
    }
  }
  
  console.log(`\nDone! Generated ${iconList.length * 2} icons.`);
}

// Main
const options = parseArgs();
generateIcons(options).catch(console.error);
