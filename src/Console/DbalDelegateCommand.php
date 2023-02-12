<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Davefu\KdybyContributteBridge\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command Delegate.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @author Filip Procházka <filip@prochazka.su>
 * @author David Fiedor <davefu@seznam.cz>
 */
abstract class DbalDelegateCommand extends Command {

	/** @var Command */
	protected $command;

	abstract protected function createCommand(): Command;

	protected function wrapCommand(string $connectionName): Command {
		CommandHelper::setApplicationConnection($this->getHelper('container'), $connectionName);
		$this->command->setApplication($this->getApplication());
		return $this->command;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure(): void {
		$this->command = $this->createCommand();

		$this->setName($this->command->getName());
		$this->setHelp($this->command->getHelp());
		$this->setDefinition($this->command->getDefinition());
		$this->setDescription($this->command->getDescription());

		$this->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The connection to use for this command');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		return $this->wrapCommand($input->getOption('connection'))->execute($input, $output);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function interact(InputInterface $input, OutputInterface $output): void {
		$this->wrapCommand($input->getOption('connection'))->interact($input, $output);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function initialize(InputInterface $input, OutputInterface $output): void {
		$this->wrapCommand($input->getOption('connection'))->initialize($input, $output);
	}
}
