<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Aliyun\OTS\RowExistenceExpectationConst;

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
    public static function getDb();

    /**
     * Set expect type when put data to tablestore
     * @param string $const
     * @return mixed
     */
    public function setExceptionConst($const = RowExistenceExpectationConst::CONST_IGNORE);

    /**
     * Get tabletore request
     * @return mixed
     */
    public function getRequest();

    /**
     * Get tabletore response
     * @return mixed
     */
    public function getResponse();

    /**
     * Tablestore primary value
     * @param array $value
     * @return void
     */
    public function setPrimaryValue($value);

    /**
     * Find tablestore data
     * @param array $value
     * @return Model|null
     */
    public static function find($value);

    /**
     * Find or new tablestore data
     * @param array $value
     * @return Model
     */
    public static function findOrNew($value);

    /**
     * Put data to tablestore[attribute_columns_to_put]
     * @return mixed
     */
    public function save();
}