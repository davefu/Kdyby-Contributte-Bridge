<?php

namespace Davefu\KdybyContributteBridge\DI\Helper;

use Nettrine\ORM\Exception\Logical\InvalidStateException;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
class Exception {

	/**
	 * @throw InvalidStateException
	 */
	public static function throwMissingExtensionException(string $missingExtension, string $currentExtension): void {
		throw new InvalidStateException(
			sprintf('You should register %s before %s.', $missingExtension, $currentExtension)
		);
	}
}
