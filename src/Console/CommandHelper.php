<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Davefu\KdybyContributteBridge\Console;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Kdyby\Console\ContainerHelper;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Tomáš Jacík <tomas@jacik.cz>
 * @author David Fiedor <davefu@seznam.cz>
 */
final class CommandHelper {

	/**
	 * Private constructor. This class is not meant to be instantiated.
	 */
	private function __construct() {
	}

	public static function setApplicationEntityManager(ContainerHelper $containerHelper, $emName): void {
		/** @var EntityManagerInterface $em */
		$em = $containerHelper->getByType(EntityManagerInterface::class);
		/** @var HelperSet|null $helperSet */
		$helperSet = $containerHelper->getHelperSet();
		if ($helperSet !== null) {
			$helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
			$helperSet->set(new EntityManagerHelper($em), 'em');
		}
	}

	public static function setApplicationConnection(ContainerHelper $containerHelper, $connName): void {
		/** @var EntityManagerInterface $em */
		$em = $containerHelper->getByType(EntityManagerInterface::class);
		$connection = $em->getConnection();
		/** @var HelperSet|null $helperSet */
		$helperSet = $containerHelper->getHelperSet();
		if ($helperSet !== null) {
			$helperSet->set(new ConnectionHelper($connection), 'db');
		}
	}
}
