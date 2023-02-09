<?php

namespace Davefu\KdybyContributteBridge\DI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Nette\DI\Helpers;
use Nette\Utils\Validators;
use Nettrine\ORM\DI\OrmAnnotationsExtension;
use Nettrine\ORM\Exception\Logical\InvalidStateException;
use Nettrine\ORM\Mapping\AnnotationDriver;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmAnnotationsExtensionProxy extends OrmAnnotationsExtension {
	use Helper\MappingDriverTrait;

	public const DRIVER_TAG = 'nettrine.orm.annotation.driver';

	public function loadConfiguration(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();

		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$reader = $builder->addDefinition($this->prefix('annotationReader'))
			->setType(AnnotationReader::class)
			->setAutowired(false);

		Validators::assertField($config, 'ignore', 'array');
		foreach ($config['ignore'] as $annotationName) {
			$reader->addSetup('addGlobalIgnoredName', [$annotationName]);
			AnnotationReader::addGlobalIgnoredName($annotationName);
		}

		if ($config['cache'] === null && $config['defaultCache'] !== null) {
			$this->getDefaultCache()
				->setAutowired(false);
		} elseif ($config['cache'] !== null) {
			$builder->addDefinition($this->prefix('annotationsCache'))
				->setFactory($config['cache'])
				->setAutowired(false);
		} else {
			throw new InvalidStateException('Cache or defaultCache must be provided');
		}

		$builder->addDefinition($this->prefix('reader'))
			->setType(Reader::class)
			->setFactory(CachedReader::class, [
				$this->prefix('@annotationReader'),
				$this->prefix('@annotationsCache'),
				$config['debug'],
			]);

		$pathsConfig = Helpers::expand($config['paths'], $builder->parameters);
		$builder->addDefinition($this->prefix('annotationDriver'))
			->setFactory(AnnotationDriver::class, [$this->prefix('@reader'), $pathsConfig])
			->addSetup('addExcludePaths', [Helpers::expand($config['excludePaths'], $builder->parameters)])
			->addTag(self::DRIVER_TAG);

		$mappingDriver = $this->getMappingDriverDef();
		foreach ($pathsConfig as $namespace => $path) {
			$mappingDriver->addSetup('addDriver', [$this->prefix('@annotationDriver'), $namespace]);
		}

		AnnotationRegistry::registerUniqueLoader('class_exists');
	}
}
