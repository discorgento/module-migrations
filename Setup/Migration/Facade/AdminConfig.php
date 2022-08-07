<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;

class AdminConfig
{
    protected ConfigWriter $configWriter;
    protected ScopeConfig $scopeConfig;

    public function __construct(
        ConfigWriter $configWriter,
        ScopeConfig $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve config value from given path
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     */
    public function get($path, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = null)
    {
        $this->scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * Set config value of given path
     *
     * @param string|array $path can also be a map of multiple config
     * @param string|int|array $value
     * @param string $scope
     * @param int $scopeId
     */
    public function set($path, $value = null, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        if (!is_array($path)) {
            return $this->configWriter->save($path, $value, $scope, $scopeId);
        }

        $paths = $path;
        foreach ($paths as $path => $value) {
            $this->set(
                $path,
                $value['value'] ?? $value,
                $value['scope'] ?? $scope,
                $value['scope_id'] ?? $scopeId,
            );
        }
    }

    /**
     * Delete given config from core_config_data
     * (thus resetting to config.xml value)
     *
     * @param string|array $path
     * @param string $scope
     * @param int $scopeId
     */
    public function reset($path, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $paths = is_string($path) ? [$path] : $path;
        foreach ($paths as $path) {
            $this->configWriter->delete($path, $scope, $scopeId);
        }
    }

    /**
     * Append given value to given path
     *
     * @param string $path
     * @param string|int|array $value
     * @param string $scope
     * @param int $scopeId
     */
    public function append($path, $value, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = null)
    {
        $oldValue = $this->scopeConfig->getValue($path, $scope, $scopeId);
        $newValue = $oldValue . $value;
        $this->configWriter->save($path, $newValue, $scope, $scopeId ?: 0);
    }
}
