<?php

namespace Davefu\KdybyContributteBridge\DI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Portability\Connection as PortabilityConnection;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Nettrine\DBAL\ConnectionFactory;
use Nettrine\DBAL\DI\DbalExtension;
use Nettrine\DBAL\Events\DebugEventManager;
use Nettrine\DBAL\Tracy\QueryPanel\QueryPanel;
use PDO;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class DbalExtensionProxy extends CompilerExtensionProxy {

	public const TAG_NETTRINE_SUBSCRIBER = DbalExtension::TAG_NETTRINE_SUBSCRIBER;
	public const SERVICE_EVENT_MANAGER = 'eventManager';

	/** @var mixed[] */
	private $defaults = [
		'debug' => false,
		'configuration' => [
			'sqlLogger' => null,
			'resultCacheImpl' => null,
			'filterSchemaAssetsExpression' => null,
			'autoCommit' => true,
		],
		'connection' => [
			'url' => null,
			'pdo' => null,
			'memory' => null,
			'driver' => 'pdo_mysql',
			'driverClass' => null,
			'unix_socket' => null,
			'host' => null,
			'port' => null,
			'dbname' => null,
			'servicename' => null,
			'user' => null,
			'password' => null,
			'charset' => 'UTF8',
			'portability' => PortabilityConnection::PORTABILITY_ALL,
			'fetchCase' => PDO::CASE_LOWER,
			'persistent' => true,
			'types' => [],
			'typesMapping' => [],
			'wrapperClass' => null,
		],
	];

	/** @var DbalExtension */
	private $originalExtension;

	public function __construct() {
		$this->originalExtension = new DbalExtension();
	}

	public function loadConfiguration(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateKdybyEventsExtensionRegistered();
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$this->loadDoctrineConfiguration();
		$this->loadConnectionConfiguration();

		if ($config['debug'] === true) {
			$builder->addDefinition($this->prefix('queryPanel'))
				->setFactory(QueryPanel::class)
				->setAutowired(false);
		}
	}

	public function loadConnectionConfiguration(): void {
		$builder = $this->getContainerBuilder();
		$globalConfig = $this->validateConfig($this->defaults);
		$config = $this->validateConfig($this->defaults['connection'], $this->config['connection']);

		$evmName = $this->prefix(self::SERVICE_EVENT_MANAGER);
		if ($this->getExtension('Kdyby\Events\DI\EventsExtension') === null) {
			$builder->addDefinition($evmName)
				->setFactory(ContainerAwareEventManager::class);
		} else {
			$builder->addDefinition($evmName)
				->setFactory(\Kdyby\Events\NamespacedEventManager::class, [\Doctrine\ORM\Events::class . '::'])
				->addSetup('$dispatchGlobalEvents', [true]) // for BC
				->setAutowired(false);
		}

		if ($globalConfig['debug'] === true) {
			$builder->getDefinition($evmName)
				->setAutowired(false);
			$builder->addDefinition($this->prefix('eventManager.debug'))
				->setFactory(DebugEventManager::class, [$this->prefix('@eventManager')]);
		}

		$builder->addDefinition($this->prefix('connectionFactory'))
			->setFactory(ConnectionFactory::class, [$config['types'], $config['typesMapping']]);

		$builder->addDefinition($this->prefix('connection'))
			->setFactory(Connection::class)
			->setFactory('@' . $this->prefix('connectionFactory') . '::createConnection', [
				$config,
				'@' . $this->prefix('configuration'),
				$this->prefix('@' . self::SERVICE_EVENT_MANAGER)
			]);
	}

	public function loadDoctrineConfiguration(): void {
		$this->originalExtension->loadDoctrineConfiguration();
	}

	public function beforeCompile(): void {
		$this->originalExtension->beforeCompile();
	}

	public function afterCompile(ClassType $class): void {
		$this->originalExtension->afterCompile($class);
	}

	protected function getOriginalExtension(): CompilerExtension {
		return $this->originalExtension;
	}
}
