<?php

namespace Davefu\KdybyContributteBridge\Cache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\Common\Cache\XcacheCache;
use InvalidArgumentException;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\DI\Definitions\Reference;
use Nette\SmartObject;
use stdClass;

/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author David Fiedor <davefu@seznam.cz>
 */
class Helpers {
	use SmartObject;

	/** @var string[] */
	public const DRIVERS = [
		'default' => NetteCache::class,
		'apc' => ApcCache::class,
		'apcu' => ApcuCache::class,
		'array' => ArrayCache::class,
		'filesystem' => FilesystemCache::class,
		'memcache' => MemcacheCache::class,
		'memcached' => MemcachedCache::class,
		'phpfile' => PhpFileCache::class,
		'redis' => RedisCache::class,
		'void' => VoidCache::class,
		'xcache' => XcacheCache::class,
	];

	/**
	 * @param CompilerExtension $extension
	 * @param string|stdClass|Statement $cache
	 * @param string $suffix
	 * @param bool|null $debug
	 */
	public static function processCache(CompilerExtension $extension, $cache, string $suffix, ?bool $debug = null): string {
		$builder = $extension->getContainerBuilder();

		$impl = $cache instanceof stdClass ? $cache->value : ($cache instanceof Statement ? $cache->getEntity() : $cache);
		if (!is_string($impl)) {
			throw new InvalidArgumentException('Cache implementation cannot be resolved. Pass preferably string or \Nette\DI\Definitions\Statement as $cache argument.');
		}

		/** @var Statement $cache */
		[$cache] = self::filterArgs($cache);
		if (isset(self::DRIVERS[$impl])) {
			$cache = new Statement(self::DRIVERS[$impl], $cache->arguments);
		}

		if ($impl === 'default') {
			$cache->arguments[1] = 'Doctrine.' . ucfirst($suffix);
			$cache->arguments[2] = $debug ?? $builder->parameters['debugMode'];
		}

		if ($impl === 'filesystem') {
			$cache->arguments[] = $builder->parameters['tempDir'] . '/cache/Doctrine.' . ucfirst($suffix);
		}

		$def = $builder->addDefinition($serviceName = $extension->prefix('cache.' . $suffix))
			->setClass(Cache::class)
			->setFactory($cache->getEntity(), $cache->arguments)
			->setAutowired(false);

		if (class_exists($cache->getEntity()) && is_subclass_of($cache->getEntity(), CacheProvider::class)) {
			$ns = 'Kdyby_' . $serviceName;

			if (preg_match('~^(?P<projectRoot>.+)(?:\\\\|\\/)vendor(?:\\\\|\\/)kdyby(?:\\\\|\\/)doctrine-cache(?:\\\\|\\/).+\\z~i', __DIR__, $m)) {
				$ns .= '_' . substr(md5($m['projectRoot']), 0, 8);
			}

			$def->addSetup('setNamespace', [$ns]);
		}

		return '@' . $serviceName;
	}

	/**
	 * @param string|stdClass|Statement $statement
	 * @return Statement[]
	 */
	public static function filterArgs($statement): array {
		return self::doFilterArguments([is_string($statement) ? new Statement($statement) : $statement]);
	}

	/**
	 * Removes ... recursively.
	 *
	 * @param array|mixed[] $args
	 * @return Statement[]
	 */
	private static function doFilterArguments(array $args): array {
		foreach ($args as $k => $v) {
			if ($v === '...') {
				unset($args[$k]);
			} elseif (is_array($v)) {
				$args[$k] = self::doFilterArguments($v);
			} elseif ($v instanceof Statement) {
				/** @var string|array|Definition|Reference|null $tmp */
				$tmp = self::doFilterArguments([$v->getEntity()])[0];
				$args[$k] = new Statement($tmp, self::doFilterArguments($v->arguments));
			} elseif ($v instanceof stdClass && isset($v->value, $v->attributes)) {
				/** @var string|array|Definition|Reference|null $tmp */
				$tmp = self::doFilterArguments([$v->value])[0];
				$args[$k] = new Statement($tmp, self::doFilterArguments(is_array($v->attributes) ? $v->attributes : [$v->attributes]));
			}
		}

		return $args;
	}
}
