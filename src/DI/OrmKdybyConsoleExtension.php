<?php

namespace Davefu\KdybyContributteBridge\DI;

use Davefu\KdybyContributteBridge\Exception\NotSupportedException;
use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Extensions\InjectExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmKdybyConsoleExtension extends CompilerExtension {

	public function loadConfiguration(): void {
		$builder = $this->getContainerBuilder();

		foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
			$cli = $builder->addDefinition($this->prefix('cli.' . $i))
				->addTag(ConsoleExtension::TAG_COMMAND)
				->addTag(InjectExtension::TAG_INJECT, FALSE); // lazy injects

			if (is_string($command)) {
				$cli->setClass($command);
			} else {
				throw new NotSupportedException();
			}
		}
	}
}
