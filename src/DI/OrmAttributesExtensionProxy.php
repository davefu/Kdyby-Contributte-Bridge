<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmAttributesExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmAttributesExtensionProxy extends OrmAttributesExtension {

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
