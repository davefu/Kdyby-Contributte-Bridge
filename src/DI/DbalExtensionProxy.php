<?php

namespace Davefu\KdybyContributteBridge\DI;

use Doctrine\DBAL\Connection;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Schema;
use Nettrine\DBAL\ConnectionAccessor;
use Nettrine\DBAL\ConnectionFactory;
use Nettrine\DBAL\DI\DbalExtension;
use Nettrine\DBAL\Events\ContainerAwareEventManager;
use Nettrine\DBAL\Events\DebugEventManager;
use Nettrine\DBAL\Tracy\BlueScreen\DbalBlueScreen;
use Nettrine\DBAL\Tracy\QueryPanel\QueryPanel;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class DbalExtensionProxy extends CompilerExtensionProxy {

	public const SERVICE_EVENT_MANAGER = 'eventManager';

	/** @var DbalExtension */
	private $originalExtension;

	public function __construct() {
		$this->originalExtension = new DbalExtension();
	}

	public function loadConfiguration(): void {
		$this->loadDoctrineConfiguration();
		$this->loadConnectionConfiguration();
	}

	public function loadConnectionConfiguration(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->config->connection;

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

		if ($this->config->debug->panel) {
			$builder->getDefinition($evmName)
				->setAutowired(false);
			$builder->addDefinition($this->prefix('eventManager.debug'))
				->setFactory(DebugEventManager::class, [$this->prefix('@' . self::SERVICE_EVENT_MANAGER)]);
		}

		$builder->addDefinition($this->prefix('connectionFactory'))
			->setFactory(ConnectionFactory::class, [$config['types'], $config['typesMapping']]);

		$connectionDef = $builder->addDefinition($this->prefix('connection'))
			->setType(Connection::class)
			->setFactory('@' . $this->prefix('connectionFactory') . '::createConnection', [
				$config,
				'@' . $this->prefix('configuration'),
				$this->prefix('@' . self::SERVICE_EVENT_MANAGER),
			]);

		if ($this->config->debug->panel) {
			$connectionDef
				->addSetup('@Tracy\Bar::addPanel', [
					new Statement(QueryPanel::class, [
						$this->prefix('@profiler'),
					]),
				])
				->addSetup('@Tracy\BlueScreen::addPanel', [
					[DbalBlueScreen::class, 'renderException'],
				]);
		}

		$builder->addAccessorDefinition($this->prefix('connectionAccessor'))
			->setImplement(ConnectionAccessor::class);
	}

	public function getConfigSchema(): Schema {
		return $this->originalExtension->getConfigSchema();
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
