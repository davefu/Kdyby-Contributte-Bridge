<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Davefu\KdybyContributteBridge\Console\Proxy;

use Davefu\KdybyContributteBridge\Console\DbalDelegateCommand;
use Symfony\Component\Console\Command\Command;

/**
 * Loads an SQL file and executes it.
 */
class ImportCommand extends DbalDelegateCommand {

	protected function createCommand(): Command {
		return new \Doctrine\DBAL\Tools\Console\Command\ImportCommand();
	}
}
