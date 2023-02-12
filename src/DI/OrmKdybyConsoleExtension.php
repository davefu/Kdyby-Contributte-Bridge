<?php

namespace Davefu\KdybyContributteBridge\DI;

use Davefu\KdybyContributteBridge\Exception\NotSupportedException;
use Davefu\KdybyContributteBridge\Tool\CacheCleaner;
use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Extensions\InjectExtension;
use Nettrine\Annotations\DI\AnnotationsExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmKdybyConsoleExtension extends CompilerExtension {

	public function loadConfiguration(): void {
		$builder = $this->getContainerBuilder();

		$cacheCleaner = $builder->addDefinition($this->prefix('cacheCleaner'))
			->setFactory(CacheCleaner::class)
			->setAutowired(true);

		foreach ($this->compiler->getExtensions(AnnotationsExtension::class) as $extension) {
			/** @var OrmAnnotationsExtensionProxy $extension */
			$cacheCleaner->addSetup('addCacheStorage', [$extension->prefix('@cache')]);
		}

		foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
			$cli = $builder->addDefinition($this->prefix('cli.' . $i))
				->addTag(ConsoleExtension::TAG_COMMAND)
				->addTag(InjectExtension::TagInject, false); // lazy injects

			if (is_string($command)) {
				$cli->setType($command);
			} else {
				throw new NotSupportedException();
			}
		}
	}
}
