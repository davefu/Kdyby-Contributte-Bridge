<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmCacheExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmCacheExtensionProxy extends OrmCacheExtension {

	/** @var mixed[] */
	private $defaults = [
		'defaultDriver' => 'filesystem',
		'queryCache' => null,
		'hydrationCache' => null,
		'metadataCache' => null,
		'resultCache' => null,
		'secondLevelCache' => null,
	];

	public function loadConfiguration(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();

		$this->validateConfig($this->defaults);
		$this->loadQueryCacheConfiguration();
		$this->loadHydrationCacheConfiguration();
		$this->loadResultCacheConfiguration();
		$this->loadMetadataCacheConfiguration();
		$this->loadSecondLevelCacheConfiguration();
	}
}
