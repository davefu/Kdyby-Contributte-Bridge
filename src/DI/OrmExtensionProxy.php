<?php

namespace Davefu\KdybyContributteBridge\DI;

use Davefu\KdybyContributteBridge\DI\Helper\Exception;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nettrine\ORM\DI\OrmExtension;
use Nettrine\ORM\EntityManagerDecorator;
use Nettrine\ORM\Exception\Logical\InvalidStateException;
use Nettrine\ORM\ManagerRegistry;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmExtensionProxy extends CompilerExtensionProxy {

	public const MAPPING_DRIVER_TAG = 'nettrine.orm.mapping.driver';

	/** @var mixed[] */
	private $defaults = [
		'entityManagerDecoratorClass' => EntityManagerDecorator::class,
		'configurationClass' => Configuration::class,
		'configuration' => [
			'proxyDir' => '%tempDir%/proxies',
			'autoGenerateProxyClasses' => null,
			'proxyNamespace' => 'Nettrine\Proxy',
			'metadataDriverImpl' => null,
			'entityNamespaces' => [],
			//TODO named query
			//TODO named native query
			'customStringFunctions' => [],
			'customNumericFunctions' => [],
			'customDatetimeFunctions' => [],
			'customHydrationModes' => [],
			'classMetadataFactoryName' => null,
			//TODO filters
			'defaultRepositoryClassName' => null,
			'namingStrategy' => UnderscoreNamingStrategy::class,
			'quoteStrategy' => null,
			'entityListenerResolver' => null,
			'repositoryFactory' => null,
			'defaultQueryHints' => [],
		],
	];

	/** @var OrmExtension */
	private $originalExtension;

	public function __construct() {
		$this->originalExtension = new OrmExtension();
	}

	public function loadConfiguration(): void {
		$this->validateConfig($this->defaults);
		$this->loadDoctrineConfiguration();
		$this->loadEntityManagerConfiguration();
		$this->loadMappingConfiguration();
	}

	public function loadEntityManagerConfiguration(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$entityManagerDecoratorClass = $config['entityManagerDecoratorClass'];
		if (!class_exists($entityManagerDecoratorClass)) {
			throw new InvalidStateException(sprintf('EntityManagerDecorator class "%s" not found', $entityManagerDecoratorClass));
		}

		$dbalExtension = $this->getExtension(DbalExtensionProxy::class);
		if ($dbalExtension === null) {
			Exception::throwMissingExtensionException(DbalExtensionProxy::class, static::class);
		}

		// Entity Manager
		$original = $builder->addDefinition($this->prefix('entityManager'))
			->setType(DoctrineEntityManager::class)
			->setFactory(DoctrineEntityManager::class . '::create', [
				$builder->getDefinitionByType(Connection::class), // Nettrine/DBAL
				$this->prefix('@configuration'),
				$dbalExtension->prefix('@' . DbalExtensionProxy::SERVICE_EVENT_MANAGER)
			])
			->setAutowired(false);

		// Entity Manager Decorator
		$builder->addDefinition($this->prefix('entityManagerDecorator'))
			->setFactory($entityManagerDecoratorClass, [$original]);

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

		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults['configuration'], $this->config['configuration']);
		$config = Helpers::expand($config, $builder->parameters);
		if ($config['metadataDriverImpl'] === null) {
			$builder->getDefinitionByType($this->config['configurationClass'])
				->addSetup('setMetadataDriverImpl', [$this->prefix('@mappingDriver')]);
		}
	}

	public function loadMappingConfiguration(): void {
		$builder = $this->getContainerBuilder();

		// Driver Chain
		$builder->addDefinition($this->prefix('mappingDriver'))
			->setFactory(MappingDriverChain::class)
			->addTag(self::MAPPING_DRIVER_TAG);
	}

	protected function getOriginalExtension(): CompilerExtension {
		return $this->originalExtension;
	}
}
