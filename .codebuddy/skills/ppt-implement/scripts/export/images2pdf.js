/**
 * 图片转 PDF 工具
 * 将 page-{页码}.png 格式的截图文件合成为一个 PDF 文件
 * 
 * 使用方法:
 *   node images2pdf.js <imagesDir> <outputPdfPath>
 *
 * 参数说明:
 *   imagesDir     - 包含 page-{页码}.png 文件的目录
 *   outputPdfPath - 输出 PDF 文件路径
 */

const fs = require('fs');
const path = require('path');
const { PDFDocument } = require('pdf-lib');
const sharp = require('sharp');

/**
 * 将目录下的 page-*.png 图片合成为 PDF
 * @param {string} imagesDir - 图片目录
 * @param {string} outputPdfPath - 输出 PDF 文件路径
 * @returns {Promise<{success: boolean, outputPath: string, pageCount: number}>}
 */
async function imagesToPdf(imagesDir, outputPdfPath) {
    if (!fs.existsSync(imagesDir)) {
        throw new Error(`图片目录不存在: ${imagesDir}`);
    }

    // 获取所有 page-*.png 文件并按页码排序
    const files = fs.readdirSync(imagesDir)
        .filter(f => /^page-\d+\.png$/.test(f))
        .sort((a, b) => {
            const numA = parseInt(a.match(/^page-(\d+)\.png$/)[1], 10);
            const numB = parseInt(b.match(/^page-(\d+)\.png$/)[1], 10);
            return numA - numB;
        });

    if (files.length === 0) {
        throw new Error(`目录中没有找到 page-*.png 文件: ${imagesDir}`);
    }

    const pdfDoc = await PDFDocument.create();

    for (const file of files) {
        const imgPath = path.join(imagesDir, file);
        const imgBuffer = fs.readFileSync(imgPath);

        // 使用 sharp 获取图片尺寸并转为 JPEG（pdf-lib 不原生支持 PNG alpha 通道，转 JPEG 更稳定）
        const metadata = await sharp(imgBuffer).metadata();
        const jpegBuffer = await sharp(imgBuffer)
            .flatten({ background: { r: 255, g: 255, b: 255 } })
            .jpeg({ quality: 95 })
            .toBuffer();

        const jpegImage = await pdfDoc.embedJpg(jpegBuffer);

        // 页面尺寸与图片一致（单位：points, 1px ≈ 0.75pt @96dpi）
        const width = metadata.width * 0.75;
        const height = metadata.height * 0.75;

        const page = pdfDoc.addPage([width, height]);
        page.drawImage(jpegImage, {
            x: 0,
            y: 0,
            width,
            height,
        });
    }

    // 确保输出目录存在
    fs.mkdirSync(path.dirname(outputPdfPath), { recursive: true });

    const pdfBytes = await pdfDoc.save();
    fs.writeFileSync(outputPdfPath, pdfBytes);

    return {
        success: true,
        outputPath: outputPdfPath,
        pageCount: files.length,
    };
}

module.exports = { imagesToPdf };

// 命令行入口
if (require.main === module) {
    const args = process.argv.slice(2);
    const imagesDir = args[0];
    const outputPdfPath = args[1];

    if (!imagesDir || !outputPdfPath) {
        console.error('使用方法: node images2pdf.js <imagesDir> <outputPdfPath>');
        process.exit(1);
    }

    imagesToPdf(imagesDir, outputPdfPath)
        .then(result => {
            console.log(`PDF 生成成功: ${result.outputPath}, 页数: ${result.pageCount}`);
            process.exit(0);
        })
        .catch(error => {
            console.error(`PDF 生成失败: ${error.message}`);
            process.exit(1);
        });
}
