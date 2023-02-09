<?php

namespace Davefu\KdybyContributteBridge\Console;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Helper\Helper;

/**
 * Doctrine CLI Connection Helper.
 * @author David Fiedor <davefu@seznam.cz>
 */
class ConnectionHelper extends Helper {

	/** @var Connection The Doctrine database Connection. */
    protected $_connection;

    public function __construct(Connection $connection) {
        $this->_connection = $connection;
    }

    /**
     * Retrieves the Doctrine database Connection.
     */
    public function getConnection(): Connection {
        return $this->_connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string {
        return 'connection';
    }
}
