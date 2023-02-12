<?php

namespace Davefu\KdybyContributteBridge\Tool;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author David Fiedor <davefu@seznam.cz>
 */
class CacheCleaner {

	/** @var EntityManagerInterface */
	private $entityManager;

	/** @var (ClearableCache|Cache|null)[] */
	private $cacheStorages = [];

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function addCacheStorage(ClearableCache $storage): void {
		$this->cacheStorages[] = $storage;
	}

	public function invalidate(): void {
		$ormConfig = $this->entityManager->getConfiguration();
		$dbalConfig = $this->entityManager->getConnection()->getConfiguration();

		$cache = $this->cacheStorages;
		$cache[] = $ormConfig->getHydrationCacheImpl();
		$cache[] = $ormConfig->getMetadataCacheImpl();
		$cache[] = $ormConfig->getQueryCacheImpl();
		$cache[] = $ormConfig->getResultCacheImpl();
		$cache[] = $dbalConfig->getResultCacheImpl();

		foreach ($cache as $impl) {
			if (!$impl instanceof ClearableCache) {
				continue;
			}

			$impl->deleteAll();
		}
	}
}
