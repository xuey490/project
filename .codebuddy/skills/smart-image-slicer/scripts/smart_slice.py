#!/usr/bin/env python3
"""
智能精灵图切分工具
- 自动检测左上角底色
- 支持不规则底色和重叠区域
- 基于颜色相似度进行智能切分
"""

import cv2
import numpy as np
from PIL import Image
import os
import argparse
from collections import Counter
import math

def get_background_color(image, sample_size=20):
    """
    从图像左上角区域采样获取背景色
    返回 RGB 平均值和标准差
    """
    # 取左上角区域作为背景色采样
    sample_region = image[:sample_size, :sample_size]
    
    # 将采样区域转换为一维数组
    pixels = sample_region.reshape(-1, 3)
    
    # 计算平均颜色和标准差
    bg_color = np.mean(pixels, axis=0)
    bg_std = np.std(pixels, axis=0)
    
    # 如果标准差太小，设置最小值避免过于严格的匹配
    bg_std = np.maximum(bg_std, 10)
    
    return bg_color, bg_std

def color_distance(color1, color2):
    """计算两个颜色之间的欧几里得距离"""
    return np.sqrt(np.sum((color1 - color2) ** 2))

def is_background_color(pixel, bg_color, bg_std, tolerance_factor=2.5):
    """
    判断像素是否为背景色
    使用标准差的倍数作为容差
    """
    # 计算每个通道的差异
    diff = np.abs(pixel - bg_color)
    # 使用标准差的倍数作为阈值
    threshold = bg_std * tolerance_factor
    
    # 所有通道都在容差范围内才认为是背景色
    return np.all(diff <= threshold)

def create_mask(image, bg_color, bg_std, tolerance_factor=2.5):
    """
    创建背景掩码
    返回二值图像：背景为0，前景为255
    """
    h, w, c = image.shape
    mask = np.zeros((h, w), dtype=np.uint8)
    
    # 向量化操作：计算所有像素与背景色的差异
    diff = np.abs(image - bg_color)
    threshold = bg_std * tolerance_factor
    
    # 创建背景掩码：所有通道都在阈值内的像素为背景
    bg_mask = np.all(diff <= threshold, axis=2)
    
    # 前景像素设为255
    mask[~bg_mask] = 255
    
    return mask

def find_connected_components(mask, min_area=100):
    """
    查找连通组件
    返回每个组件的边界框
    """
    # 形态学操作去噪
    kernel = np.ones((3, 3), np.uint8)
    mask = cv2.morphologyEx(mask, cv2.MORPH_OPEN, kernel, iterations=1)
    mask = cv2.morphologyEx(mask, cv2.MORPH_CLOSE, kernel, iterations=2)
    
    # 查找轮廓
    contours, _ = cv2.findContours(mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    
    bboxes = []
    for contour in contours:
        # 计算轮廓面积
        area = cv2.contourArea(contour)
        if area < min_area:
            continue
            
        # 获取边界框
        x, y, w, h = cv2.boundingRect(contour)
        bboxes.append((x, y, x + w, y + h))
    
    return bboxes

def merge_overlapping_boxes(bboxes, overlap_threshold=0.3):
    """
    合并重叠的边界框
    """
    if not bboxes:
        return []
    
    # 按面积排序，大的优先
    bboxes = sorted(bboxes, key=lambda box: (box[2]-box[0])*(box[3]-box[1]), reverse=True)
    
    merged = []
    used = set()
    
    for i, box1 in enumerate(bboxes):
        if i in used:
            continue
            
        x1, y1, x2, y2 = box1
        current_box = [x1, y1, x2, y2]
        
        for j, box2 in enumerate(bboxes[i+1:], i+1):
            if j in used:
                continue
                
            x3, y3, x4, y4 = box2
            
            # 计算重叠面积
            overlap_x1 = max(current_box[0], x3)
            overlap_y1 = max(current_box[1], y3)
            overlap_x2 = min(current_box[2], x4)
            overlap_y2 = min(current_box[3], y4)
            
            if overlap_x1 < overlap_x2 and overlap_y1 < overlap_y2:
                overlap_area = (overlap_x2 - overlap_x1) * (overlap_y2 - overlap_y1)
                box2_area = (x4 - x3) * (y4 - y3)
                
                if overlap_area / box2_area > overlap_threshold:
                    # 合并边界框
                    current_box[0] = min(current_box[0], x3)
                    current_box[1] = min(current_box[1], y3)
                    current_box[2] = max(current_box[2], x4)
                    current_box[3] = max(current_box[3], y4)
                    used.add(j)
        
        merged.append(tuple(current_box))
        used.add(i)
    
    return merged

def extract_sprites(image_path, output_folder, sample_size=20, tolerance_factor=2.5, 
                   min_area=100, overlap_threshold=0.3, padding=2):
    """
    主要的精灵图切分函数
    """
    if not os.path.exists(output_folder):
        os.makedirs(output_folder)
    
    print(f"🔹 正在处理: {image_path}")
    
    # 读取图像
    image = cv2.imread(image_path)
    if image is None:
        print("❌ 无法读取图片")
        return 0
    
    # 转换为RGB
    image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # 获取背景色
    bg_color, bg_std = get_background_color(image_rgb, sample_size)
    print(f"🎨 检测到背景色: RGB{tuple(bg_color.astype(int))}, 标准差: {tuple(bg_std.astype(int))}")
    
    # 创建前景掩码
    mask = create_mask(image_rgb, bg_color, bg_std, tolerance_factor)
    
    # 查找连通组件
    bboxes = find_connected_components(mask, min_area)
    print(f"🔍 检测到 {len(bboxes)} 个初始区域")
    
    # 合并重叠区域
    merged_bboxes = merge_overlapping_boxes(bboxes, overlap_threshold)
    print(f"🔗 合并后剩余 {len(merged_bboxes)} 个区域")
    
    # 提取并保存精灵
    pil_image = Image.open(image_path).convert("RGBA")
    pil_array = np.array(pil_image)
    
    count = 0
    for i, (x1, y1, x2, y2) in enumerate(merged_bboxes):
        # 添加padding
        x1 = max(0, x1 - padding)
        y1 = max(0, y1 - padding)
        x2 = min(pil_array.shape[1], x2 + padding)
        y2 = min(pil_array.shape[0], y2 + padding)
        
        # 提取区域
        sprite_data = pil_array[y1:y2, x1:x2].copy()
        
        # 背景透明化
        for y in range(sprite_data.shape[0]):
            for x in range(sprite_data.shape[1]):
                pixel_rgb = sprite_data[y, x, :3]
                if is_background_color(pixel_rgb, bg_color, bg_std, tolerance_factor):
                    sprite_data[y, x, 3] = 0  # 设置为透明
        
        # 保存精灵
        sprite_img = Image.fromarray(sprite_data)
        
        # 裁剪透明边框
        bbox = sprite_img.getbbox()
        if bbox:
            sprite_img = sprite_img.crop(bbox)
            
            filename = f"sprite_{count:03d}.png"
            sprite_img.save(os.path.join(output_folder, filename))
            count += 1
            print(f"💾 保存: {filename} (大小: {sprite_img.size})")
    
    return count

def main():
    parser = argparse.ArgumentParser(description="智能精灵图切分工具")
    parser.add_argument("input_image", help="输入图像路径")
    parser.add_argument("-o", "--output", default="sprites_output", help="输出文件夹")
    parser.add_argument("-s", "--sample-size", type=int, default=20, 
                       help="背景色采样区域大小 (默认: 20)")
    parser.add_argument("-t", "--tolerance", type=float, default=2.5,
                       help="颜色容差倍数 (默认: 2.5)")
    parser.add_argument("-m", "--min-area", type=int, default=100,
                       help="最小精灵面积 (默认: 100)")
    parser.add_argument("-r", "--overlap", type=float, default=0.3,
                       help="重叠合并阈值 (默认: 0.3)")
    parser.add_argument("-p", "--padding", type=int, default=2,
                       help="精灵边距 (默认: 2)")
    
    args = parser.parse_args()
    
    count = extract_sprites(
        args.input_image, 
        args.output,
        args.sample_size,
        args.tolerance,
        args.min_area,
        args.overlap,
        args.padding
    )
    
    if count > 0:
        print(f"\n✅ 成功切分出 {count} 个精灵!")
        print(f"📁 输出目录: {args.output}")
    else:
        print("\n⚠️ 未检测到有效的精灵区域")

if __name__ == "__main__":
    main()