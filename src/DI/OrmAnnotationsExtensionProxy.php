<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmAnnotationsExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmAnnotationsExtensionProxy extends OrmAnnotationsExtension {

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
