export const calculateGridMetrics = (scene, rows, cols, marginTop = 100) => {
    const safeWidth = scene.scale.width * 0.95; // 5% margin
    const safeHeight = scene.scale.height - marginTop - 20;
    
    // Calculate max possible size for a cell
    const cellW = safeWidth / cols;
    const cellH = safeHeight / rows;
    const cellSize = Math.min(cellW, cellH);
    
    // Calculate centering offsets
    const boardWidth = cols * cellSize;
    const boardHeight = rows * cellSize;
    const startX = (scene.scale.width - boardWidth) / 2 + (cellSize / 2);
    const startY = marginTop + (safeHeight - boardHeight) / 2 + (cellSize / 2);
    
    return { cellSize, startX, startY };
};

/**
 * 将游戏对象限制在指定的宽高范围内，同时保持纵横比
 * @param {Phaser.GameObjects.Image} target - 目标图片对象
 * @param {number} maxWidth - 最大宽度
 * @param {number} maxHeight - 最大高度 (可选)
 */
export const fitImage = (target, maxWidth, maxHeight) => {
    // 先重置缩放，获取原始尺寸
    target.setScale(1);
    
    // 计算宽度缩放比
    let scale = maxWidth / target.width;
    
    // 如果提供了高度，且按高度缩放会更小，则采用高度缩放比（确保完全塞入框内）
    if (maxHeight) {
        const heightScale = maxHeight / target.height;
        scale = Math.min(scale, heightScale);
    }
    
    // 应用缩放
    target.setScale(scale);
    
    return target;
}