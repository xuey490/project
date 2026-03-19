
import os
import argparse
import cv2
import numpy as np
from rembg import remove, new_session
from PIL import Image, ImageOps

def add_white_border(image, border_size=50):
    """给图片增加白边"""
    if image.mode not in ("RGB", "RGBA"):
        image = image.convert("RGB")
    return ImageOps.expand(image, border=border_size, fill='white')

def process_mask_in_original_size(rembg_output_padded, border_size, erode_size=2):
    """
    核心逻辑改变：
    1. 先把 AI 生成的图切回原图大小 (扔掉白边区域的任何伪影)。
    2. 再提取 Alpha 通道进行修补。
    """
    # 1. 转为 numpy
    img_np = np.array(rembg_output_padded)
    
    # 2. 【关键】直接切掉四周的 border_size
    # 数组切片: [y_start:y_end, x_start:x_end]
    h, w = img_np.shape[:2]
    # 确保不会切过头
    if h > 2 * border_size and w > 2 * border_size:
        img_cropped = img_np[border_size:h-border_size, border_size:w-border_size]
    else:
        img_cropped = img_np # 如果图片太小就不切了（极其罕见）

    # 3. 提取 Alpha 通道
    if img_cropped.shape[2] != 4:
        # 如果没有 alpha，返回全白
        return np.ones((img_cropped.shape[0], img_cropped.shape[1]), dtype=np.uint8) * 255
    
    alpha = img_cropped[:, :, 3]

    # 4. 二值化 & 查找轮廓
    _, binary = cv2.threshold(alpha, 10, 255, cv2.THRESH_BINARY)
    contours, _ = cv2.findContours(binary, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    if not contours:
        return alpha 

    # 5. 找到最大轮廓并填充 (防止花纹丢失)
    max_contour = max(contours, key=cv2.contourArea)
    mask_filled = np.zeros_like(alpha)
    cv2.drawContours(mask_filled, [max_contour], -1, 255, thickness=-1)

    # 6. 边缘收缩 (去除细微白边)
    if erode_size > 0:
        kernel = np.ones((3, 3), np.uint8)
        mask_filled = cv2.erode(mask_filled, kernel, iterations=erode_size)
        # 再次轻微模糊，平滑锯齿
        mask_filled = cv2.GaussianBlur(mask_filled, (3, 3), 0)

    return mask_filled

def remove_background_ai(input_path, output_path, resize=None):
    print(f"🤖 正在处理: {input_path} ...")
    
    model_name = "isnet-general-use"
    
    try:
        session = new_session(model_name)
        original_image = Image.open(input_path)
        
        # 1. 记录原始尺寸
        orig_w, orig_h = original_image.size
        
        # 2. 加白边 (依然加，为了 AI 识别准确)
        border_size = 100
        padded_original = add_white_border(original_image, border_size=border_size)

        # 3. AI 识别
        print("🔍 AI 正在识别...")
        rembg_output_padded = remove(
            padded_original, 
            session=session,
            alpha_matting=False 
        )
        
        # 4. 【核心】在原图尺寸下处理蒙版
        # 这会自动丢弃 border_size 区域内的所有像素，包括底部的白色伪影
        # print("✂️ 正在裁剪并修复边界...")
        final_mask = process_mask_in_original_size(rembg_output_padded, border_size, erode_size=2)

        # 5. 合成 (注意：这里用的是原始图片 original_image，不是 padded 版)
        # 确保 original_image 是 RGB
        if original_image.mode != "RGB":
            original_image = original_image.convert("RGB")
            
        original_rgb = np.array(original_image)
        r, g, b = cv2.split(original_rgb)
        
        # 确保尺寸匹配 (防止极少数情况下的 1px 误差)
        if final_mask.shape[:2] != original_rgb.shape[:2]:
            final_mask = cv2.resize(final_mask, (orig_w, orig_h))

        # 合并
        final_rgba = cv2.merge([r, g, b, final_mask])
        final_image = Image.fromarray(final_rgba)

        # 6. 最后只裁掉透明区域
        final_image = final_image.crop(final_image.getbbox())

        # 7. 调整尺寸 (如果指定了 resize)
        if resize:
            target_w, target_h = resize
            cur_w, cur_h = final_image.size
            if target_w and target_h:
                # 同时指定宽高，直接缩放
                final_image = final_image.resize((target_w, target_h), Image.LANCZOS)
                print(f"📐 已调整尺寸: {cur_w}x{cur_h} → {target_w}x{target_h}")
            elif target_w:
                # 只指定宽度，等比缩放
                ratio = target_w / cur_w
                new_h = int(cur_h * ratio)
                final_image = final_image.resize((target_w, new_h), Image.LANCZOS)
                print(f"📐 已调整尺寸: {cur_w}x{cur_h} → {target_w}x{new_h} (按宽度等比)")
            elif target_h:
                # 只指定高度，等比缩放
                ratio = target_h / cur_h
                new_w = int(cur_w * ratio)
                final_image = final_image.resize((new_w, target_h), Image.LANCZOS)
                print(f"📐 已调整尺寸: {cur_w}x{cur_h} → {new_w}x{target_h} (按高度等比)")
        
        final_image.save(output_path)
        print(f"✅ 成功！保存至: {output_path}")
        
    except Exception as e:
        print(f"❌ 出错: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="AI 去底 (边界伪影去除版)")
    parser.add_argument("input", help="输入图片路径")
    parser.add_argument("-o", "--output", help="输出路径")
    parser.add_argument("--resize", help="调整输出尺寸，格式: WxH / Wx / xH (例: 128x128, 256x, x512)")
    args = parser.parse_args()

    out = args.output if args.output else args.input.replace(".", "_clean.")

    resize = None
    if args.resize:
        parts = args.resize.lower().split('x')
        try:
            w = int(parts[0]) if parts[0] else None
            h = int(parts[1]) if len(parts) > 1 and parts[1] else None
            if w or h:
                resize = (w, h)
            else:
                print("⚠️ 无效的 resize 参数，忽略。格式: WxH / Wx / xH")
        except ValueError:
            print("⚠️ 无效的 resize 参数，忽略。格式: WxH / Wx / xH")

    remove_background_ai(args.input, out, resize=resize)
