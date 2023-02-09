<?php

namespace Davefu\KdybyContributteBridge\DI;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Nette\DI\Helpers;
use Nettrine\ORM\DI\OrmXmlExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmXmlExtensionProxy extends OrmXmlExtension {
	use Helper\MappingDriverTrait;

	public const DRIVER_TAG = 'nettrine.orm.xml.driver';

	public function loadConfiguration(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();

		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$pathsConfig = Helpers::expand($config['paths'], $builder->parameters);
		$builder->addDefinition($this->prefix('xmlDriver'))
			->setFactory(XmlDriver::class, [
				$pathsConfig,
				$config['fileExtension'],
			])
			->addTag(self::DRIVER_TAG);

		$mappingDriver = $this->getMappingDriverDef();
		foreach ($pathsConfig as $namespace => $path) {
			$mappingDriver->addSetup('addDriver', [$this->prefix('@annotationDriver'), $namespace]);
		}
	}
}
