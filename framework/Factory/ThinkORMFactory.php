<?php
declare(strict_types=1);

namespace Framework\Factory;

use Framework\Factory\FactoryInterface;
use think\DbManager;
use Psr\Log\LoggerInterface;
use think\facade\Db;

class ThinkORMFactory //implements FactoryInterface
{
    private DbManager $db;

    public function __construct(
        private array $config,
        private LoggerInterface $logger,
        private LoggerInterface $slowLogger,
        private int $slowThresholdMs = 200
    ) {
        // 创建真正的 DbManager 实例（不是 facade）
        $this->db = new DbManager();

        // 设置配置
        $this->db->setConfig($this->config);

        // 注册事件监听
        $this->registerQueryListener($this->db);
    }

    public function create(): DbManager
    {
        return $this->db;
    }

	private function registerQueryListener(DbManager $db): void
	{
		$threshold = $this->slowThresholdMs;

		$db->listen(function ($sql, $time, $explain) use ($threshold) {

			$time = is_numeric($time) ? (float)$time : 0.0;
			$timeMs = round($time * 1000, 2);

			$record = [
				'sql'      => $sql,
				'time_ms'  => $timeMs,
				'explain'  => $explain ?? [],
			];

			// 慢查询报警
			if ($timeMs > $threshold) {
				// 慢查询（warning）
				$this->slowLogger->warning('[ThinkORM Slow SQL]', $record);
			} else {
				// 普通 SQL
				$this->logger->debug('[ThinkORM SQL]', $record);
			}
		});
	}

}