<?php

namespace Davefu\KdybyContributteBridge\DI;

use Davefu\KdybyContributteBridge\DI\Helper\Exception;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nettrine\ORM\DI\OrmExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmExtensionProxy extends CompilerExtension {
	use Helper\CompilerExtensionTrait;

	public function loadConfiguration(): void {
		$dbalExtension = $this->getExtension(DbalExtensionProxy::class);
		if ($dbalExtension === null) {
			Exception::throwMissingExtensionException(DbalExtensionProxy::class, static::class);
		}

		$ormExtension = $this->getExtension(OrmExtension::class);
		if ($ormExtension === null) {
			Exception::throwMissingExtensionException(OrmExtension::class, static::class);
		}
	}

	public function beforeCompile(): void {
		$builder = $this->getContainerBuilder();
		$dbalExtension = $this->getExtension(DbalExtensionProxy::class);
		$ormExtension = $this->getExtension(OrmExtension::class);


		// Entity Manager
		$original = new Statement(DoctrineEntityManager::class . '::create', [
			$builder->getDefinitionByType(Connection::class), // Nettrine/DBAL
			$ormExtension->prefix('@configuration'),
			$dbalExtension->prefix('@' . DbalExtensionProxy::SERVICE_EVENT_MANAGER)
		]);

		/** @var ServiceDefinition $emDecorator */
		$emDecorator = $builder->getDefinition($ormExtension->prefix('entityManagerDecorator'));
		$emDecorator->setArguments([$original]);
	}
}
