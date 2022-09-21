<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\SchemaMigration\Facade;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class DatabaseView
{
    private ResourceConnection $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Create a new view on database
     *
     * @param string $viewName
     * @param Select $select
     */
    public function create($viewName, $select)
    {
        $this->_create($viewName, $select);
    }

    /**
     * Update existing view
     * (or create it if it doesn't exist)
     *
     * @param string $viewName
     * @param Select $select
     */
    public function update($viewName, $select)
    {
        $this->_create($viewName, $select, true);
    }

    /**
     * Drop given view
     *
     * @param string $viewName
     */
    public function drop($viewName)
    {
        $this->getConnection()->query(<<<SQL
            DROP VIEW {$this->escapeViewName($viewName)}
        SQL);
    }

    /**
     * Create a new view on database
     *
     * @param string $viewName
     * @param Select $select
     */
    private function _create($viewName, $select, $replace = false)
    {
        $orReplace = $replace ? 'OR REPLACE' : '';
        $this->getConnection()->query(<<<SQL
            CREATE $orReplace VIEW {$this->escapeViewName($viewName)}
            AS {$select->__toString()}
        SQL);
    }

    /**
     * Validate given view name and preffix it if needed.
     *
     * @param string $viewName
     * @return string
     */
    private function escapeViewName($viewName)
    {
        return $this->resourceConnection->getTableName($viewName);
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
