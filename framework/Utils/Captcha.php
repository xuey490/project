<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Ramsey\Uuid\Uuid;

class Captcha
{
    /**
     * 验证验证码是否正确.
     */
    public static function check(string $code, string $key): bool
    {
        $config = config('captcha.captcha');

        $redisKey = $config['prefix'] . ':' . $key;

        $redis = app('redis');
        $hash  = $redis->get($redisKey);
        if (! $hash) {
            return false;
        }

        $code = mb_strtolower($code, 'UTF-8');
        $ok   = password_verify($code, $hash);

        if ($ok) {
            $redis->del($redisKey);
        }

        return $ok;
    }

    /**
     * 输出验证码并把验证码的值保存的session中.
     * @return array
     * @throws \Exception
     */
    public static function base64(array $_config = [])
    {
        $config = app('config')->get('captcha.captcha');

        if (! empty($_config)) {
            $config = array_merge($config, $_config);
        }

        # return $config;
        $generator = self::generateValue($config);
        // 图片宽(px)
        $config['imageW'] || $config['imageW'] = $config['length'] * $config['fontSize'] * 1.5 + $config['length'] * $config['fontSize'] / 2;
        // 图片高(px)
        $config['imageH'] || $config['imageH'] = $config['fontSize'] * 2.5;
        // 建立一幅 $config['imageW'] x $config['imageH'] 的图像
        $im = imagecreate((int) $config['imageW'], (int) $config['imageH']);
        // 设置背景
        imagecolorallocate($im, $config['bg'][0], $config['bg'][1], $config['bg'][2]);

        // 验证码字体随机颜色
        $color = imagecolorallocate($im, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));

        // 验证码使用随机字体
        $ttfPath = BASE_PATH . '/config/assets/' . ($config['useZh'] ? 'zhttfs' : 'ttfs') . '/';

        if (empty($config['fontttf'])) {
            $dir  = dir($ttfPath);
            $ttfs = [];
            while (false !== ($file = $dir->read())) {
                if (substr($file, -4) == '.ttf' || substr($file, -4) == '.otf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $config['fontttf'] = $ttfs[array_rand($ttfs)];
        }

        $fontttf = $ttfPath . $config['fontttf'];

        if ($config['useImgBg']) {
            self::background($config, $im);
        }

        if ($config['useNoise']) {
            // 绘杂点
            self::writeNoise($config, $im);
        }
        if ($config['useCurve']) {
            // 绘干扰线
            self::writeCurve($config, $im, $color);
        }

        // 绘验证码
        $text = $config['useZh'] ? preg_split('/(?<!^)(?!$)/u', $generator['value']) : str_split($generator['value']); // 验证码

        foreach ($text as $index => $char) {
            $x     = $config['fontSize'] * ($index + 1) * mt_rand((int) 1.2, (int) 1.6) * ($config['math'] ? 1 : 1.5);
            $y     = $config['fontSize'] + mt_rand(10, 20);
            $angle = $config['math'] ? 0 : mt_rand(-40, 40);
            imagettftext($im, $config['fontSize'], $angle, (int) $x, (int) $y, $color, $fontttf, $char);
        }

        ob_start();
        imagepng($im);
        $content = ob_get_clean();
        imagedestroy($im);

        return [
            'key'    => $generator['key'],
            'base64' => 'data:image/png;base64,' . base64_encode($content),
        ];
    }

    /**
     * 生成验证码内容并保存到 Redis.
     */
    protected static function generateValue(array $config): array
    {
        if ($config['math']) {
            $x      = random_int(10, 30);
            $y      = random_int(1, 9);
            $value  = "{$x}+{$y}";
            $answer = (string) ($x + $y);
        } else {
            $value      = '';
            $characters = $config['useZh']
                ? preg_split('/(?<!^)(?!$)/u', $config['zhSet'])
                : str_split($config['codeSet']);

            for ($i = 0; $i < $config['length']; ++$i) {
                $value .= $characters[array_rand($characters)];
            }

            $answer = mb_strtolower($value, 'UTF-8');
        }

        // redis key
        $key      = Uuid::uuid4()->toString();
        $redisKey = $config['prefix'] . ':' . $key;

        app('redis')->setex(
            $redisKey,
            $config['expire'],
            password_hash($answer, PASSWORD_BCRYPT)
        );

        return [
            'value' => $value,
            'key'   => $key,
        ];
    }

    protected static function pickFont(string $path, array &$config): string
    {
        if (! empty($config['fontttf'])) {
            return $path . $config['fontttf'];
        }

        $fonts = [];
        foreach (scandir($path) as $f) {
            if (str_ends_with($f, '.ttf') || str_ends_with($f, '.otf')) {
                $fonts[] = $f;
            }
        }

        $config['fontttf'] = $fonts[array_rand($fonts)];
        return $path . $config['fontttf'];
    }

    // ====== 干扰项绘制函数 保留原样 =======
    /**
     * @desc: 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     * @param mixed $im
     * @param mixed $color
     */
    protected static function writeCurve(array $config, $im, $color): void
    {
        $py = 0;
        // 曲线前部分
        $A = mt_rand(1, (int) ($config['imageH'] / 2)); // 振幅
        $b = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // Y轴方向偏移量
        $f = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // X轴方向偏移量
        $T = mt_rand((int) $config['imageH'], (int) ($config['imageW'] * 2)); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand((int) ($config['imageW'] / 2), (int) $config['imageW']); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $config['imageH'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($im, (int) $px + $i, (int) $py + $i, (int) $color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    --$i;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, (int) ($config['imageH'] / 2)); // 振幅
        $f   = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // X轴方向偏移量
        $T   = mt_rand((int) $config['imageH'], (int) ($config['imageW'] * 2)); // 周期
        $w   = (2 * M_PI)                                        / $T;
        $b   = $py - $A * sin($w * $px + $f) - $config['imageH'] / 2;
        $px1 = $px2;
        $px2 = $config['imageW'];

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $config['imageH'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($im, (int) $px + $i, (int) $py + $i, (int) $color);
                    --$i;
                }
            }
        }
    }

    /**
     * @desc: 画杂点  往图片上写不同颜色的字母或数字
     * @param mixed $im
     */
    protected static function writeNoise(array $config, $im): void
    {
        $codeSet = 'FSSPHP20222345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; ++$i) {
            // 杂点颜色
            $noiseColor = imagecolorallocate($im, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; ++$j) {
                // 绘杂点
                imagestring($im, 5, mt_rand(-10, (int) $config['imageW']), mt_rand(-10, (int) $config['imageH']), $codeSet[mt_rand(0, 29)], (int) $noiseColor);
            }
        }
    }

    /**
     * @desc: 绘制背景图片 注：如果验证码输出图片比较大，将占用比较多的系统资源
     * @param mixed $im
     */
    protected static function background(array $config, $im): void
    {
        $path = BASE_PATH . '/config/assets/bgs/';
        $dir  = dir($path);

        $bgs = [];
        while (false !== ($file = $dir->read())) {
            if ($file[0] != '.' && substr($file, -4) == '.jpg') {
                $bgs[] = $path . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        [$width, $height] = @getimagesize($gb);
        $bgImage          = @imagecreatefromjpeg($gb);
        @imagecopyresampled($im, $bgImage, 0, 0, 0, 0, (int) $config['imageW'], (int) $config['imageH'], $width, $height);
        @imagedestroy($bgImage);
    }
}
