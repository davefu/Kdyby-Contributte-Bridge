<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmConsoleExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmConsoleExtensionProxy extends OrmConsoleExtension {

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
