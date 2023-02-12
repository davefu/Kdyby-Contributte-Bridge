<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use stdClass;

/**
 * @author David Fiedor <davefu@seznam.cz>
 *
 * @property-read stdClass $config
 */
abstract class CompilerExtensionProxy extends CompilerExtension {

	public function setCompiler(Compiler $compiler, $name): self {
		$this->getOriginalExtension()->setCompiler($compiler, $name);
		return parent::setCompiler($compiler, $name);
	}

	/**
	 * @param array|object $config
	 */
	public function setConfig($config): self {
		$this->getOriginalExtension()->setConfig($config);
		return parent::setConfig($config);
	}

	protected function getExtension(string $className): ?CompilerExtension {
		$extensions = $this->compiler->getExtensions($className);
		if ($extensions === []) {
			return null;
		}

		return current($extensions);
	}

	abstract protected function getOriginalExtension(): CompilerExtension;
}
