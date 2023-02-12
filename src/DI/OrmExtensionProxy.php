<?php

namespace Davefu\KdybyContributteBridge\DI;

use Davefu\KdybyContributteBridge\DI\Helper\Exception;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nettrine\ORM\DI\OrmExtension;
use Nettrine\ORM\Exception\Logical\InvalidStateException;
use Nettrine\ORM\ManagerRegistry;
use stdClass;

/**
 * @author David Fiedor <davefu@seznam.cz>
 *
 * @property-read stdClass $config
 */
class OrmExtensionProxy extends CompilerExtensionProxy {

	public const MAPPING_DRIVER_TAG = OrmExtension::MAPPING_DRIVER_TAG;

	/** @var OrmExtension */
	private $originalExtension;

	public function __construct() {
		$this->originalExtension = new OrmExtension();
	}

	public function loadConfiguration(): void {
		$this->loadDoctrineConfiguration();
		$this->loadEntityManagerConfiguration();
		$this->loadMappingConfiguration();
	}

	public function loadEntityManagerConfiguration(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		// @validate entity manager decorator has a real class
		$entityManagerDecoratorClass = $config->entityManagerDecoratorClass;
		if (!class_exists($entityManagerDecoratorClass)) {
			throw new InvalidStateException(sprintf('EntityManagerDecorator class "%s" not found', $entityManagerDecoratorClass));
		}

		$dbalExtension = $this->getExtension(DbalExtensionProxy::class);
		if ($dbalExtension === null) {
			Exception::throwMissingExtensionException(DbalExtensionProxy::class, static::class);
		}

		// Entity Manager
		$original = new Statement(DoctrineEntityManager::class . '::create', [
			$builder->getDefinitionByType(Connection::class), // Nettrine/DBAL
			$this->prefix('@configuration'),
			$dbalExtension->prefix('@' . DbalExtensionProxy::SERVICE_EVENT_MANAGER)
		]);

		// Entity Manager Decorator
		$decorator = $builder->addDefinition($this->prefix('entityManagerDecorator'))
			->setFactory($entityManagerDecoratorClass, [$original]);

		if ($config->configuration->filters !== []) {
			foreach ($config->configuration->filters as $filterName => $filter) {
				if ($filter->enabled) {
					$decorator->addSetup(new Statement('$service->getFilters()->enable(?)', [$filterName]));
				}
			}
		}

		// ManagerRegistry
		$builder->addDefinition($this->prefix('managerRegistry'))
			->setType(ManagerRegistry::class)
			->setArguments([
				$builder->getDefinitionByType(Connection::class),
				$this->prefix('@entityManagerDecorator'),
			]);
	}

	public function loadDoctrineConfiguration(): void {
		$this->originalExtension->loadDoctrineConfiguration();
	}

	public function loadMappingConfiguration(): void {
		$this->originalExtension->loadMappingConfiguration();
	}

	protected function getOriginalExtension(): CompilerExtension {
		return $this->originalExtension;
	}
}
