<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmCacheExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmCacheExtensionProxy extends OrmCacheExtension {

	public function loadConfiguration(): void {
		parent::loadConfiguration();

		//Allow turning off second level cache
		$config = $this->config;
		if (($config->secondLevelCache === null || $config->secondLevelCache === [])
			&& ($config->defaultDriver === null || $config->defaultDriver === [])) { // Nette converts explicit null to an empty array
			$this->getConfigurationDef()
				->addSetup('setSecondLevelCacheEnabled', [false]);
		}
	}

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
