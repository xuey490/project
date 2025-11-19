<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
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

class MakeModelCommand extends Command
{
    /**
     * 命令名称 - 必须定义
     * 这是修复错误的关键.
     */
    protected static $defaultName = 'make:model';

    /**
     * 命令描述.
     */
    protected static $defaultDescription = '创建一个新的模型类';

    /**
     * 配置命令.
     */
    protected function configure(): void
    {
        $this
            // 命令名称（冗余定义，确保兼容性）
            ->setName(self::$defaultName)
            // 命令描述
            ->setDescription(self::$defaultDescription)
            // 添加模型名称参数
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '模型名称（例如：User 会生成 User 模型）'
            );
    }

    /**
     * 执行命令.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modelName = $input->getArgument('name');
        $className = ucfirst($modelName);
        $directory = __DIR__ . '/../../../app/Models';
        $filePath  = $directory . '/' . $className . '.php';

        // 检查文件是否已存在
        if (file_exists($filePath)) {
            $output->writeln("<error>错误：模型 {$className} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        // 创建文件系统实例
        $filesystem = new Filesystem();

        try {
            // 确保目录存在
            $filesystem->mkdir($directory);

            // 生成模型内容
            $content = $this->generateModelContent($className);

            // 写入文件
            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成模型：</info>');
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
     * 生成模型类内容.
     */
    private function generateModelContent(string $className): string
    {
        // 自动生成表名（类名转小写复数形式）
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';

        return <<<PHP
<?php

namespace App\\Models;

use think\\Model;

class {$className} extends Model
{
    /**
     * 对应的数据表名
     * @var string
     */
    protected \$table = '{$tableName}';
    
    /**
     * 主键名称
     * @var string
     */
    protected \$pk = 'id';
    
    /**
     * 是否自动写入时间戳
     * @var bool
     */
    protected \$autoWriteTimestamp = true;
    
    /**
     * 时间戳字段名
     * @var array
     */
    protected \$createTime = 'created_at';
    protected \$updateTime = 'updated_at';
    
    /**
     * 时间戳格式
     * @var string
     */
    protected \$dateFormat = 'Y-m-d H:i:s';
    
    /**
     * 可以被批量赋值的属性
     * @var array
     */
    protected \$fillable = [];
    
    /**
     * 隐藏的属性
     * @var array
     */
    protected \$hidden = [];
}
PHP;
    }
}
