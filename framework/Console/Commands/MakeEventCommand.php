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

class MakeEventCommand extends Command
{
    /**
     * 命令名称 - 必须定义.
     */
    protected static $defaultName = 'make:event';

    /**
     * 命令描述.
     */
    protected static $defaultDescription = '创建一个新的事件类';

    /**
     * 配置命令.
     */
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '事件类名称（例如：UserRegistered 会生成 UserRegisteredEvent）'
            );
    }

    /**
     * 执行命令.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventBaseName = $input->getArgument('name');
        $eventName     = ucfirst($eventBaseName) . 'Event';
        $directory     = __DIR__ . '/../../../app/Events';
        $filePath      = $directory . '/' . $eventName . '.php';

        if (file_exists($filePath)) {
            $output->writeln("<error>错误：事件 {$eventName} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        $filesystem = new Filesystem();

        try {
            $filesystem->mkdir($directory);

            $content = $this->generateEventContent($eventName);

            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成事件类：</info>');
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
     * 生成事件类内容.
     */
    private function generateEventContent(string $eventName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\Events;

use Framework\\Event\\EventInterface;

class {$eventName} implements EventInterface
{
    /**
     * 构造函数 - 可根据需要添加事件数据
     */
    public function __construct(
        // public readonly mixed \$data,
    ) {
    }
}
PHP;
    }
}
