<?php

namespace Davefu\KdybyContributteBridge\DI;

use Contributte\Console\DI\ConsoleExtension;
use Davefu\KdybyContributteBridge\Tool\CacheCleaner;
use Nette\DI\Definitions\Statement;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\Annotations\DI\AnnotationsExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class ContributteConsoleExtensionProxy extends ConsoleExtension {

	public function getConfigSchema(): Schema {
		return Expect::structure([
			'url' => Expect::anyOf(Expect::string(), Expect::null())->dynamic(),
			'name' => Expect::string()->dynamic(),
			'version' => Expect::anyOf(Expect::string(), Expect::int(), Expect::float()),
			'catchExceptions' => Expect::bool()->dynamic(),
			'autoExit' => Expect::bool(),
			'helperSet' => Expect::anyOf(Expect::string(), Expect::type(Statement::class)),
			'helpers' => Expect::arrayOf(
				Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class))
			),
			'commands' => Expect::arrayOf(
				Expect::anyOf(Expect::string(), Expect::type(Statement::class))
			),
		]);
	}

	public function loadConfiguration(): void {
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$cacheCleaner = $builder->addDefinition($this->prefix('cacheCleaner'))
			->setFactory(CacheCleaner::class)
			->setAutowired(true);

		foreach ($this->compiler->getExtensions(AnnotationsExtension::class) as $extension) {
			/** @var OrmAnnotationsExtensionProxy $extension */
			$cacheCleaner->addSetup('addCacheStorage', [$extension->prefix('@cache')]);
		}

		$commandTag = defined('self::COMMAND_TAG') ? self::COMMAND_TAG : 'console.command';
		foreach ($config->commands as $i => $command) {
			$def = $builder->addDefinition($this->prefix('command.' . $i));
			$def->setFactory(Helpers::filterArguments([
				is_string($command) ? new Statement($command) : $command,
			])[0]);

			if (class_exists($def->getEntity())) {
				$def->setType($def->getEntity());
			}

			$def->setAutowired(false);
			$def->addTag($commandTag);
		}
	}
}
