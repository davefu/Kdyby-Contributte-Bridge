<?php

namespace Davefu\KdybyContributteBridge\DI;

use Nettrine\ORM\DI\OrmXmlExtension;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class OrmXmlExtensionProxy extends OrmXmlExtension {

	public function validate(): void {
		Helper\ExtensionValidator::of($this->compiler, static::class)
			->validateOrmExtensionRegistered();
	}
}
