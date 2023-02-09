<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Davefu\KdybyContributteBridge\Console\Proxy;

use Davefu\KdybyContributteBridge\Console\OrmDelegateCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tomas Jacik <tomas.jacik@sunfox.cz>
 * @author David Fiedor <davefu@seznam.cz>
 */
class ConvertMappingCommand extends OrmDelegateCommand {

	/**
	 * @var \Kdyby\Doctrine\Tools\CacheCleaner
	 * @inject
	 */
	public $cacheCleaner;

	public function __construct() {
		parent::__construct();
	}

	protected function initialize(InputInterface $input, OutputInterface $output): void {
		parent::initialize($input, $output);

		$this->cacheCleaner->invalidate();
	}

	protected function createCommand(): Command {
		return new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand();
	}
}
