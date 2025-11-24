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

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MakeMiddlewareCommand extends Command
{
    /**
     * 命令名称 - 必须定义.
     */
    protected static $defaultName = 'make:middleware';

    /**
     * 命令描述.
     */
    protected static $defaultDescription = '创建一个新的中间件类';

    /**
     * 配置命令.
     */
    protected function configure(): void
    {
        $this
            // 确保命令名称被正确设置
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '中间件名称（例如：Auth 会生成 Auth 中间件）'
            );
    }

    /**
     * 执行命令.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $middlewareName = $input->getArgument('name');
        $className      = ucfirst($middlewareName);
        $directory      = __DIR__ . '/../../../app/Middlewares';
        $filePath       = $directory . '/' . $className . '.php';

        // 检查文件是否已存在
        if (file_exists($filePath)) {
            $output->writeln("<error>错误：中间件 {$className} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        // 创建文件系统实例
        $filesystem = new Filesystem();

        try {
            // 确保目录存在
            $filesystem->mkdir($directory);

            // 生成中间件内容
            $content = $this->generateMiddlewareContent($className);

            // 写入文件
            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成中间件：</info>');
            $output->writeln("路径：{$filePath}");
            return Command::SUCCESS;
        } catch (IOExceptionInterface $e) {
            $output->writeln('<error>文件操作错误：' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>发生错误：' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * 生成中间件类内容.
     */
    private function generateMiddlewareContent(string $className): string
    {
        return <<<PHP
<?php

namespace App\\Middlewares;

use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class {$className}
{
    /**
     * 处理请求
     *
     * @param  Request  \$request
     * @param  \\Closure  \$next
     * @return Response
     */
    public function handle(Request \$request, \\Closure \$next): Response
    {
        // 在请求处理前执行的逻辑
        // 例如：身份验证、权限检查等
        
        \$response = \$next(\$request);
        
        // 在请求处理后执行的逻辑
        // 例如：添加响应头、日志记录等
        
        return \$response;
    }
}
PHP;
    }
}
