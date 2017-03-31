<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Planfox\Component\Tablestore\Exception\ConnectionException;
use Aliyun\OTS\OTSClient;

interface ConnectionInterface
{
    /**
     * Get OTSClient instance
     *
     * @param string $connection
     * @return OTSClient
     * @throws ConnectionException
     */
    public static function getDb($connection = 'default');

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
    public static function config(array $config);

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
    public static function addConfig(array $args, $connection = 'default');
}