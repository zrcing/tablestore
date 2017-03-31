<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Planfox\Component\Tablestore\Exception\ConnectionException;
use Aliyun\OTS\OTSClient;

class Connection implements ConnectionInterface
{
    /**
     * Tablestore config
     * @var array
     */
    protected static $configs = [];

    /**
     * Tablestore connections
     * @var array
     */
    protected static $connections = [];

    /**
     * Get OTSClient instance
     *
     * @param string $connection
     * @return OTSClient
     * @throws ConnectionException
     */
    public static function getDb($connection = 'default')
    {
        if (isset(static::$connections[$connection])) {
            return static::$connections[$connection];
        }
        if (! isset(static::$configs[$connection])) {
            throw new ConnectionException("Doesn't config tablestore connection.");
        }
        static::$connections[$connection] = new OTSClient(static::$configs[$connection]);
        return static::$connections[$connection];
    }

    /**
     * set tablestore config
     *
     * @param array $config [
     *  // Connection Name
     *  'default' => [
     *      'EndPoint' => '',
     *      'AccessKeyID' => '',
     *      'AccessKeySecret' => '',
     *      'InstanceName' => ''
     *   ]
     * ]
     * @throws ConnectionException
     */
    public static function config(array $config)
    {
        if (! empty(static::$configs)) {
            throw new ConnectionException("Doesn't repeatedly config tablestore.");
        }
        static::$configs = $config;
    }

    /**
     * Add tablestore config
     *
     * @param array $args [
     *      'EndPoint' => '',
     *      'AccessKeyID' => '',
     *      'AccessKeySecret' => '',
     *      'InstanceName' => ''
     * ]
     *
     * @param string $connection
     */
    public static function addConfig(array $args, $connection = 'default')
    {
        static::$configs[$connection] = $args;
    }

}