<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Aliyun\OTS\RowExistenceExpectationConst;
use Aliyun\OTS\DirectionConst;

interface ModelInterface
{
    /**
     * Tablestore table name
     * @return mixed
     */
    public static function getTable();

    /**
     * Tablestore primary key
     * @return array
     */
    public static function getPrimaryKey();

    /**
     * @return \Aliyun\OTS\OTSClient;
     */
    public function getDb();

    /**
     * Set expect type when put data to tablestore
     * @param string $const
     * @return mixed
     */
    public function setExceptionConst($const = RowExistenceExpectationConst::CONST_IGNORE);

    /**
     * @param array $request
     * @return void
     */
    public function setRequest($request);

    /**
     * Get tabletore request
     *
     * @return mixed
     */
    public function getRequest();

    /**
     * Get tabletore response
     *
     * @return mixed
     */
    public function getResponse();

    /**
     * Tablestore primary value
     *
     * @param array $value
     * @return void
     */
    public function setPrimaryValue($value);

    /**
     * Find tablestore data
     *
     * @param array $value
     * @return Model|null
     */
    public static function find($value);

    /**
     * Find or new tablestore data
     *
     * @param array $value
     * @return Model
     */
    public static function findOrNew($value);

    /**
     * @param array $startPK
     * @param array $endPK
     * @param DirectionConst $direction
     * @param int $limit
     * @return mixed
     */
    public static function findAll($startPK, $endPK, $direction = DirectionConst::CONST_FORWARD, $limit = 5000);

    /**
     * Put data to tablestore[attribute_columns_to_put]
     *
     * @return mixed
     */
    public function save();

    /**
     * @return mixed
     */
    public function delete();

    /**
     * Create table in tablestore
     *
     * @return void
     */
    public static function createTable();
}