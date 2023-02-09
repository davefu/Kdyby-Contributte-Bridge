<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
abstract class CompilerExtensionProxy extends CompilerExtension {

	public function setCompiler(Compiler $compiler, $name): self {
		$this->getOriginalExtension()->setCompiler($compiler, $name);
		return parent::setCompiler($compiler, $name);
	}

	public function setConfig(array $config): self {
		//$config['configuration'] = $config['configuration'] ?? [];
		$this->getOriginalExtension()->setConfig($config);
		return parent::setConfig($config);
	}

	public function validateConfig(array $expected, array $config = null, $name = null): array {
		if (func_num_args() === 1) {
			$this->getOriginalExtension()->validateConfig($expected);
			return parent::validateConfig($expected);
		}
		$this->getOriginalExtension()->validateConfig($expected, $config, $name);
		return parent::validateConfig($expected, $config, $name);
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
