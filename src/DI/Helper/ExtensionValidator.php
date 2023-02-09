<?php

namespace Davefu\KdybyContributteBridge\DI\Helper;

use Davefu\KdybyContributteBridge\DI\OrmExtensionProxy;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\Compiler;
use Nettrine\ORM\Exception\Logical\InvalidStateException;

/**
 *
 * @author David Fiedor <davefu@seznam.cz>
 */
class ExtensionValidator {

	/** @var Compiler */
	private $compiler;

	/** @var string */
	private $callerName;

	public function __construct(Compiler $compiler, string $callerName) {
		$this->compiler = $compiler;
		$this->callerName = $callerName;
	}

	public static function of(Compiler $compiler, string $callerName): self {
		return new static($compiler, $callerName);
	}

	/**
	 * @throws InvalidStateException
	 */
	public function validateKdybyEventsExtensionRegistered(): self {
		if ($this->compiler->getExtensions(EventsExtension::class) === []) {
			Exception::throwMissingExtensionException(EventsExtension::class, $this->callerName);
		}

		return $this;
	}

	/**
	 * @throws InvalidStateException
	 */
	public function validateOrmExtensionRegistered(): self {
		if ($this->compiler->getExtensions(OrmExtensionProxy::class) === []) {
			Exception::throwMissingExtensionException(OrmExtensionProxy::class, $this->callerName);
		}

		return $this;
	}
}
