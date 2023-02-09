<?php

namespace Davefu\KdybyContributteBridge\DI\Helper;

use Davefu\KdybyContributteBridge\DI\OrmAnnotationsExtensionProxy;
use Davefu\KdybyContributteBridge\DI\OrmExtensionProxy;
use Davefu\KdybyContributteBridge\DI\OrmXmlExtensionProxy;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use Nettrine\ORM\Exception\Logical\InvalidStateException;

/**
 * Short term helper based on original Nettrine MappingHelper, that will be available in newer Nettrine package version.
 * @link https://github.com/contributte/doctrine-orm/blob/v0.7.0/src/DI/Helpers/MappingHelper.php
 * @author David Fiedor <davefu@seznam.cz>
 */
class MappingHelper {

	/** @var CompilerExtension */
	private $extension;

	private function __construct(CompilerExtension $extension) {
		$this->extension = $extension;
	}

	public static function of(CompilerExtension $extension): self {
		return new self($extension);
	}

	public function addAnnotation(string $namespace, string $path): self {
		$this->validatePath($path);

		$annotationDriver = $this->getService(OrmAnnotationsExtensionProxy::DRIVER_TAG, 'AnnotationDriver');
		$annotationDriver->addSetup('addPaths', [[$path]]);

		$this->addNamespaceToMappingDriverChain($annotationDriver, $namespace);

		return $this;
	}

	public function addXml(string $namespace, string $path, bool $simple = false): self {
		$this->validatePath($path);

		$xmlDriver = $this->getService(OrmXmlExtensionProxy::DRIVER_TAG, 'XmlDriver');
		if ($simple) {
			$xmlDriver->addSetup(new Statement('$service->getLocator()->addNamespacePrefixes([? => ?])', [$path, $namespace]));
		} else {
			$xmlDriver->addSetup(new Statement('$service->getLocator()->addPaths([?])', [$path]));
		}

		$this->addNamespaceToMappingDriverChain($xmlDriver, $namespace);

		return $this;
	}

	/**
	 * @throws InvalidStateException
	 */
	private function getService(string $serviceClass, string $name): ServiceDefinition {
		$builder = $this->extension->getContainerBuilder();

		$services = $builder->findByTag($serviceClass);
		if ($services === []) {
			throw new InvalidStateException(sprintf('Service "%s" not found "%s"', $name, $serviceClass));
		}

		return $builder->getDefinition(current(array_keys($services)));
	}

	private function addNamespaceToMappingDriverChain(ServiceDefinition $driver, string $namespace): void {
		$chainDriver = $this->getService(OrmExtensionProxy::MAPPING_DRIVER_TAG, 'MappingDriverChain');
		$chainDriver->addSetup('addDriver', [$driver, $namespace]);
	}

	/**
	 * @throws InvalidStateException
	 */
	private function validatePath(string $path): void {
		if (!is_dir($path)) {
			throw new InvalidStateException(sprintf('Given mapping path "%s" does not exist', $path));
		}
	}
}
