<?php

namespace Davefu\KdybyContributteBridge\DI\Helper;

use Nette\DI\CompilerExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
trait CompilerExtensionTrait {

	protected function getExtension(string $className): ?CompilerExtension {
		$extensions = $this->compiler->getExtensions($className);
		if ($extensions === []) {
			return null;
		}

		return current($extensions);
	}
}
