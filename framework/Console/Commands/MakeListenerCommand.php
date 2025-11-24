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

class MakeListenerCommand extends Command
{
    /**
     * 命令名称 - 必须定义.
     */
    protected static $defaultName = 'make:listener';

    /**
     * 命令描述.
     */
    protected static $defaultDescription = '创建一个新的事件监听器类';

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
                '监听器类名称（例如：SendWelcomeEmail）'
            )
            ->addArgument(
                'event',
                InputArgument::OPTIONAL,
                '关联的事件类名称（例如：UserRegistered），将生成 handle(UserRegisteredEvent \$event) 方法'
            );
    }

    /**
     * 执行命令.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $listenerBaseName = $input->getArgument('name');
        $listenerName     = ucfirst($listenerBaseName);
        $eventArg         = $input->getArgument('event');
        $directory        = __DIR__ . '/../../../app/Listeners';
        $filePath         = $directory . '/' . $listenerName . '.php';

        if (file_exists($filePath)) {
            $output->writeln("<error>错误：监听器 {$listenerName} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        $filesystem = new Filesystem();

        try {
            $filesystem->mkdir($directory);

            $content = $this->generateListenerContent($listenerName, $eventArg);

            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成监听器类：</info>');
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
     * 生成监听器类内容.
     */
    private function generateListenerContent(string $listenerName, ?string $eventArg): string
    {
        $eventClass = $eventArg ? ucfirst($eventArg) . 'Event' : 'EventInterface';
        $eventParam = $eventArg
            ? "\\App\\Events\\{$eventClass} \$event"
            : '\Framework\Event\EventInterface $event';

        $useStatement = $eventArg
            ? "use App\\Events\\{$eventClass};"
            : '';

        $handleMethodDoc = $eventArg
            ? "     * @param \\App\\Events\\{$eventClass} \$event"
            : '     * @param \Framework\Event\EventInterface $event';

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\Listeners;

use Framework\\Event\\ListenerInterface;
{$useStatement}

class {$listenerName} implements ListenerInterface
{
    /**
     * 处理事件
     *
{$handleMethodDoc}
     */
    public function handle({$eventParam}): void
    {
        // 在此处实现监听逻辑
    }
}
PHP;
    }
}
