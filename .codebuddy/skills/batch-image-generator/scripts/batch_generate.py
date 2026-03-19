#!/usr/bin/env python3
"""
Batch Image Generator

Generates game assets from a JSON manifest file by orchestrating:
- text-to-image (mother images)
- generate-image-by-image (variants)
- background-remover (transparent PNGs)

Usage:
    python3 batch_generate.py manifest.json [options]

Options:
    --output-dir, -o    Base output directory (default: current dir)
    --dry-run, -d       Show what would be generated without executing
    --skip-remove-bg    Skip background removal for all assets
    --resume-from, -r   Resume from specified asset ID
    --verbose, -v       Show detailed progress
    --parallel, -p      Number of parallel workers (default: 1)
"""

import argparse
import json
import os
import subprocess
import sys
import time
import glob
from pathlib import Path
from typing import Optional, Dict, List, Any
from dataclasses import dataclass
from enum import Enum


class AssetType(Enum):
    MOTHER = "mother"
    VARIANT = "variant"


@dataclass
class Asset:
    id: str
    type: AssetType
    output: str
    prompt: Optional[str] = None
    mother_id: Optional[str] = None
    edit_prompt: Optional[str] = None
    size: Optional[str] = None
    quality: Optional[str] = None
    strength: float = 0.5
    remove_bg: bool = True
    background: Optional[str] = None


@dataclass
class Config:
    style: str
    default_size: str = "1024x1024"
    default_quality: str = "high"
    temp_dir: str = "./temp"
    skills_dir: str = ".codebuddy/skills"


class BatchGenerator:
    def __init__(
        self,
        manifest_path: str,
        output_dir: str = ".",
        dry_run: bool = False,
        skip_remove_bg: bool = False,
        resume_from: Optional[str] = None,
        verbose: bool = False,
    ):
        self.manifest_path = manifest_path
        self.output_dir = Path(output_dir).resolve()
        self.dry_run = dry_run
        self.skip_remove_bg = skip_remove_bg
        self.resume_from = resume_from
        self.verbose = verbose
        
        self.config: Optional[Config] = None
        self.assets: List[Asset] = []
        self.generated_files: Dict[str, str] = {}  # asset_id -> file_path
        self.results: Dict[str, bool] = {}  # asset_id -> success
        self.start_time = time.time()
        
        # Find project root (where .codebuddy is located)
        self.project_root = self._find_project_root()
        
    def _find_project_root(self) -> Path:
        """Find project root by looking for .codebuddy directory"""
        current = Path.cwd()
        while current != current.parent:
            if (current / ".codebuddy").exists():
                return current
            current = current.parent
        # Fallback to manifest directory
        return Path(self.manifest_path).parent.resolve()
    
    def load_manifest(self) -> bool:
        """Load and validate the manifest file"""
        try:
            with open(self.manifest_path, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            # Parse config
            config_data = data.get('config', {})
            self.config = Config(
                style=config_data.get('style', ''),
                default_size=config_data.get('default_size', '1024x1024'),
                default_quality=config_data.get('default_quality', 'high'),
                temp_dir=config_data.get('temp_dir', './temp'),
                skills_dir=config_data.get('skills_dir', '.codebuddy/skills'),
            )
            
            # Parse assets
            for asset_data in data.get('assets', []):
                asset_type = AssetType(asset_data['type'])
                asset = Asset(
                    id=asset_data['id'],
                    type=asset_type,
                    output=asset_data['output'],
                    prompt=asset_data.get('prompt'),
                    mother_id=asset_data.get('mother_id'),
                    edit_prompt=asset_data.get('edit_prompt'),
                    size=asset_data.get('size'),
                    quality=asset_data.get('quality'),
                    strength=asset_data.get('strength', 0.5),
                    remove_bg=asset_data.get('remove_bg', True),
                    background=asset_data.get('background'),
                )
                self.assets.append(asset)
            
            return True
            
        except Exception as e:
            self._error(f"Failed to load manifest: {e}")
            return False
    
    def validate_manifest(self) -> bool:
        """Validate manifest structure and dependencies"""
        if not self.config:
            self._error("Config section is missing")
            return False
        
        if not self.assets:
            self._error("No assets defined in manifest")
            return False
        
        # Check for duplicate IDs
        ids = [a.id for a in self.assets]
        if len(ids) != len(set(ids)):
            self._error("Duplicate asset IDs found")
            return False
        
        # Validate variant dependencies
        mother_ids = {a.id for a in self.assets if a.type == AssetType.MOTHER}
        for asset in self.assets:
            if asset.type == AssetType.VARIANT:
                if not asset.mother_id:
                    self._error(f"Variant '{asset.id}' missing mother_id")
                    return False
                if asset.mother_id not in mother_ids:
                    self._error(f"Variant '{asset.id}' references unknown mother '{asset.mother_id}'")
                    return False
                # Check mother comes before variant
                mother_idx = ids.index(asset.mother_id)
                variant_idx = ids.index(asset.id)
                if variant_idx < mother_idx:
                    self._error(f"Variant '{asset.id}' defined before its mother '{asset.mother_id}'")
                    return False
        
        return True
    
    def run(self) -> bool:
        """Execute the batch generation"""
        self._print_header()
        
        if not self.load_manifest():
            return False
        
        if not self.validate_manifest():
            return False
        
        # Setup directories
        self._setup_directories()
        
        # Filter assets if resuming
        assets_to_process = self._get_assets_to_process()
        
        if self.dry_run:
            self._print_dry_run(assets_to_process)
            return True
        
        # Process assets
        total = len(assets_to_process)
        for idx, asset in enumerate(assets_to_process, 1):
            self._print_progress(idx, total, asset)
            success = self._process_asset(asset)
            self.results[asset.id] = success
            
            if not success:
                self._warn(f"Failed to generate: {asset.id}")
        
        self._print_summary()
        return all(self.results.values())
    
    def _setup_directories(self):
        """Create necessary directories"""
        # Temp directory
        temp_path = self.output_dir / self.config.temp_dir
        temp_path.mkdir(parents=True, exist_ok=True)
        
        # Output directories for each asset
        for asset in self.assets:
            output_path = self.output_dir / asset.output
            output_path.parent.mkdir(parents=True, exist_ok=True)
    
    def _get_assets_to_process(self) -> List[Asset]:
        """Get list of assets to process (handles resume)"""
        if not self.resume_from:
            return self.assets
        
        # Find resume index
        try:
            idx = next(i for i, a in enumerate(self.assets) if a.id == self.resume_from)
            # Mark previous assets as generated (for variant dependencies)
            for asset in self.assets[:idx]:
                output_path = self.output_dir / asset.output
                if output_path.exists():
                    self.generated_files[asset.id] = str(output_path)
            return self.assets[idx:]
        except StopIteration:
            self._warn(f"Resume ID '{self.resume_from}' not found, processing all")
            return self.assets
    
    def _process_asset(self, asset: Asset) -> bool:
        """Process a single asset"""
        try:
            if asset.type == AssetType.MOTHER:
                return self._generate_mother(asset)
            else:
                return self._generate_variant(asset)
        except Exception as e:
            self._error(f"Error processing {asset.id}: {e}")
            return False
    
    def _generate_mother(self, asset: Asset) -> bool:
        """Generate a mother image using text-to-image"""
        # Build full prompt with style
        full_prompt = f"{self.config.style}，{asset.prompt}" if self.config.style else asset.prompt
        
        # Build command
        script_path = self.project_root / self.config.skills_dir / "text-to-image/scripts/generate_image.py"
        temp_dir = self.output_dir / self.config.temp_dir
        temp_prefix = f"{asset.id}_orig"
        
        cmd = [
            "python3", str(script_path),
            full_prompt,
            "--size", asset.size or self.config.default_size,
            "--quality", asset.quality or self.config.default_quality,
            "--n", "1",
            "--download-dir", str(temp_dir),
            "--download-prefix", temp_prefix,
        ]
        
        if asset.background == "transparent":
            cmd.extend(["--background", "transparent"])
        
        if self.verbose:
            self._log(f"Command: {' '.join(cmd)}")
        
        # Execute
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=str(self.project_root))
        
        if result.returncode != 0:
            self._error(f"text-to-image failed: {result.stderr}")
            return False
        
        # Find generated file
        temp_files = sorted(glob.glob(str(temp_dir / f"{temp_prefix}*.png")), key=os.path.getmtime, reverse=True)
        if not temp_files:
            self._error(f"No output file found for {asset.id}")
            return False
        
        temp_file = temp_files[0]
        output_path = self.output_dir / asset.output
        
        # Remove background if needed
        if asset.remove_bg and not self.skip_remove_bg:
            if not self._remove_background(temp_file, str(output_path)):
                return False
        else:
            # Just copy the file
            import shutil
            shutil.copy(temp_file, output_path)
        
        self.generated_files[asset.id] = str(output_path)
        self._success(f"Generated: {asset.output}")
        return True
    
    def _generate_variant(self, asset: Asset) -> bool:
        """Generate a variant image using generate-image-by-image"""
        # Get mother file path
        mother_path = self.generated_files.get(asset.mother_id)
        if not mother_path:
            # Try to find it from output path
            mother_asset = next((a for a in self.assets if a.id == asset.mother_id), None)
            if mother_asset:
                mother_path = str(self.output_dir / mother_asset.output)
                if not os.path.exists(mother_path):
                    self._error(f"Mother file not found: {mother_path}")
                    return False
            else:
                self._error(f"Mother asset not found: {asset.mother_id}")
                return False
        
        # Build command
        script_path = self.project_root / self.config.skills_dir / "generate-image-by-image/scripts/edit_image.py"
        temp_dir = self.output_dir / self.config.temp_dir
        temp_prefix = f"{asset.id}_orig"
        
        cmd = [
            "python3", str(script_path),
            asset.edit_prompt,
            "--image-url", mother_path,
            "--size", asset.size or self.config.default_size,
            "--quality", asset.quality or self.config.default_quality,
            "--strength", str(asset.strength),
            "--download-dir", str(temp_dir),
            "--download-prefix", temp_prefix,
        ]
        
        if self.verbose:
            self._log(f"Command: {' '.join(cmd)}")
        
        # Execute
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=str(self.project_root))
        
        if result.returncode != 0:
            self._error(f"generate-image-by-image failed: {result.stderr}")
            return False
        
        # Find generated file
        temp_files = sorted(glob.glob(str(temp_dir / f"{temp_prefix}*.png")), key=os.path.getmtime, reverse=True)
        if not temp_files:
            self._error(f"No output file found for {asset.id}")
            return False
        
        temp_file = temp_files[0]
        output_path = self.output_dir / asset.output
        
        # Remove background if needed
        if asset.remove_bg and not self.skip_remove_bg:
            if not self._remove_background(temp_file, str(output_path)):
                return False
        else:
            # Just copy the file
            import shutil
            shutil.copy(temp_file, output_path)
        
        self.generated_files[asset.id] = str(output_path)
        self._success(f"Generated: {asset.output}")
        return True
    
    def _remove_background(self, input_path: str, output_path: str) -> bool:
        """Remove background using background-remover skill"""
        script_path = self.project_root / self.config.skills_dir / "background-remover/scripts/remove_background.py"
        
        cmd = [
            "python3", str(script_path),
            input_path,
            "-o", output_path,
        ]
        
        if self.verbose:
            self._log(f"Removing background: {input_path}")
        
        result = subprocess.run(cmd, capture_output=True, text=True, cwd=str(self.project_root))
        
        if result.returncode != 0:
            self._error(f"background-remover failed: {result.stderr}")
            return False
        
        return True
    
    # === Output helpers ===
    
    def _print_header(self):
        print("\n🎨 Batch Image Generator")
        print("=" * 40)
        print(f"📋 Manifest: {self.manifest_path}")
        print(f"📁 Output: {self.output_dir}")
        if self.dry_run:
            print("🔍 Mode: DRY RUN")
        if self.resume_from:
            print(f"⏩ Resume from: {self.resume_from}")
        print()
    
    def _print_progress(self, current: int, total: int, asset: Asset):
        icon = "🖼️" if asset.type == AssetType.MOTHER else "🔄"
        type_name = "mother" if asset.type == AssetType.MOTHER else "variant"
        print(f"\n[{current}/{total}] {icon}  Generating {type_name}: {asset.id}")
    
    def _print_dry_run(self, assets: List[Asset]):
        print("📝 Assets to generate:\n")
        for i, asset in enumerate(assets, 1):
            icon = "🖼️" if asset.type == AssetType.MOTHER else "🔄"
            type_name = "mother" if asset.type == AssetType.MOTHER else "variant"
            bg_status = "✂️ remove bg" if asset.remove_bg else "⬛ keep bg"
            print(f"  {i:3}. {icon} [{type_name:7}] {asset.id}")
            print(f"       → {asset.output}")
            print(f"       {bg_status}")
            if asset.type == AssetType.VARIANT:
                print(f"       ↳ from: {asset.mother_id}")
        print(f"\n🎯 Total: {len(assets)} assets")
        print("\n✨ Run without --dry-run to generate assets")
    
    def _print_summary(self):
        elapsed = time.time() - self.start_time
        minutes = int(elapsed // 60)
        seconds = int(elapsed % 60)
        
        success_count = sum(1 for v in self.results.values() if v)
        fail_count = sum(1 for v in self.results.values() if not v)
        
        print("\n" + "=" * 40)
        print(f"✅ Completed: {success_count}/{len(self.results)}")
        if fail_count > 0:
            print(f"❌ Failed: {fail_count}")
            for asset_id, success in self.results.items():
                if not success:
                    print(f"   - {asset_id}")
        print(f"⏱️  Total time: {minutes}m {seconds}s")
        print(f"📁 Output: {self.output_dir}")
        print()
    
    def _log(self, msg: str):
        if self.verbose:
            print(f"       📋 {msg}")
    
    def _success(self, msg: str):
        print(f"       ✅ {msg}")
    
    def _warn(self, msg: str):
        print(f"       ⚠️  {msg}")
    
    def _error(self, msg: str):
        print(f"       ❌ {msg}", file=sys.stderr)


def main():
    parser = argparse.ArgumentParser(
        description="Batch generate game assets from a JSON manifest",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python3 batch_generate.py manifest.json
  python3 batch_generate.py manifest.json --dry-run
  python3 batch_generate.py manifest.json -o ./assets --verbose
  python3 batch_generate.py manifest.json --resume-from "asset_id"
        """
    )
    
    parser.add_argument("manifest", help="Path to JSON manifest file")
    parser.add_argument("-o", "--output-dir", default=".", help="Base output directory")
    parser.add_argument("-d", "--dry-run", action="store_true", help="Show what would be generated")
    parser.add_argument("--skip-remove-bg", action="store_true", help="Skip background removal")
    parser.add_argument("-r", "--resume-from", help="Resume from specified asset ID")
    parser.add_argument("-v", "--verbose", action="store_true", help="Verbose output")
    parser.add_argument("-p", "--parallel", type=int, default=1, help="Parallel workers (not supported yet)")
    
    args = parser.parse_args()
    
    if args.parallel > 1:
        print("⚠️  Warning: Parallel execution not supported (API limitation). Using sequential mode.")
    
    generator = BatchGenerator(
        manifest_path=args.manifest,
        output_dir=args.output_dir,
        dry_run=args.dry_run,
        skip_remove_bg=args.skip_remove_bg,
        resume_from=args.resume_from,
        verbose=args.verbose,
    )
    
    success = generator.run()
    sys.exit(0 if success else 1)


if __name__ == "__main__":
    main()
