<?php

namespace Davefu\KdybyContributteBridge\Cache;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Nette\Caching\Cache as NCache;
use Nette\Caching\Storage;
use Nette\SmartObject;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Validator\Mapping\ClassMetadata as SymfonyClassMetadata;

/**
 * Nette cache driver for doctrine
 * 
 * @author Filip Procházka <filip@prochazka.su>
 */
class NetteCache extends CacheProvider {
	use SmartObject;

	public const CACHE_NS = 'Doctrine';

	/** @var NCache */
	private $cache;

	/** @var bool */
	private $debug;

	public function __construct(Storage $storage, string $namespace = self::CACHE_NS, bool $debugMode = false) {
		$this->cache = new NCache($storage, $namespace);
		$this->debug = $debugMode;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id) {
		$cached = $this->cache->load($id);
		return $cached ?? false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id): bool {
		return $this->cache->load($id) !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0): bool {
		if ($this->debug !== true) {
			return $this->doSaveDependingOnFiles($id, $data, [], $lifeTime);
		}

		$files = [];
		if ($data instanceof DoctrineClassMetadata) {
			$files[] = self::getClassFilename($data->name);
			foreach ($data->parentClasses as $class) {
				$files[] = self::getClassFilename($class);
			}
		}
		if ($data instanceof SymfonyClassMetadata) {
			$files[] = self::getClassFilename($data->name);
		}

		if (!empty($data)) {
			$m = Strings::match($id, '~(?P<class>[^@$[\].]+)(?:\$(?P<prop>[^@$[\].]+))?\@\[Annot\]~i');
			if ($m !== null && class_exists($m['class'])) {
				$files[] = self::getClassFilename($m['class']);
			}
		}

		return $this->doSaveDependingOnFiles($id, $data, $files, $lifeTime);
	}

	/**
	 * @param string $id
	 * @param mixed $data
	 * @param string[] $files
	 * @param int $lifeTime
	 */
	protected function doSaveDependingOnFiles(string $id, $data, array $files, ?int $lifeTime = null): bool {
		$lifeTime = (int) $lifeTime;
		$dp = [NCache::TAGS => ['doctrine'], NCache::FILES => $files];

		if ($lifeTime > 0) {
			$dp[NCache::EXPIRE] = time() + $lifeTime;
		}

		$this->cache->save($id, $data, $dp);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id): bool {
		$this->cache->save($id, null);

		return true;
	}

	protected function doFlush(): bool {
		$this->cache->clean([
			NCache::TAGS => ['doctrine'],
		]);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats(): array {
		return [
			self::STATS_HITS => null,
			self::STATS_MISSES => null,
			self::STATS_UPTIME => null,
			self::STATS_MEMORY_USAGE => null,
			self::STATS_MEMORY_AVAILABLE => null,
		];
	}

	/**
	 * @return string|bool
	 * @throws ReflectionException
	 */
	private static function getClassFilename(string $className) {
		$reflection = new ReflectionClass($className);
		return $reflection->getFileName();
	}
}
