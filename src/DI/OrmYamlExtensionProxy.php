<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmYamlExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmYamlExtensionProxy extends OrmYamlExtension {

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
