<?php

namespace Davefu\KdybyContributteBridge\DI\Helper;

use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

/**
 * @author David Fiedor <davefu@seznam.cz>
 */
trait MappingDriverTrait {

	protected function getMappingDriverDef(): ServiceDefinition {
		return $this->getContainerBuilder()->getDefinitionByType(MappingDriverChain::class);
	}

	/**
	 * @return ContainerBuilder
	 */
	abstract public function getContainerBuilder();
}
