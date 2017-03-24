<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Netty\Component\Tablestore;

use Aliyun\OTS\RowExistenceExpectationConst;
use Netty\Component\Tablestore\Exception\InvalidArgumentException;

class Model implements ModelInterface
{
    /**
     * @var string TableStore table name
     */
    protected static $table;

    /**
     * @var array 第一个为主键是分区键, 区分大小写
     *['id'=>ColumnTypeConst::CONST_INTEGER, 'month'=>ColumnTypeConst::CONST_STRING]
     */
    protected static $primaryKey = [];

    /**
     * @var array
     */
    protected static $fields = [];

    /**
     * Mapping $request['primary_key']
     * @var array
     */
    protected $primaryValue = [];

    /**
     * @var array
     */
    protected $request;

    /**
     * @var array
     */
    protected $response;

    /**
     * Mapping $response['row']['primary_key_columns']
     * @var array
     */
    protected $responseKeys = [];

    /**
     * Mapping $response['row']['attribute_columns']
     * @var array
     */
    protected $columns = [];

    /**
     * @var string
     */
    protected $expectionConst = RowExistenceExpectationConst::CONST_IGNORE;

    /**
     * @return \Aliyun\OTS\OTSClient;
     */
    public static function getDb()
    {
        return \Netty::app('Ots');
    }

    public function __set($name, $value)
    {
        $this->invalidColumn($name);
        $this->columns[$name] = $value;
    }

    public function __isset($name)    {

        return isset($this->columns[$name]);
    }

    public function __get($name)
    {
        $this->invalidColumn($name);
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    public function __construct($value = [])
    {
        if ($value) {
            $this->setPrimaryValue($value);
        }
    }

    private function invalidColumn($name)
    {
        if (! in_array($name, static::$fields)) {
            throw new InvalidArgumentException('Invalid column fields.');
        }
        return $this;
    }

    private function invalidPrimaryValue()
    {
        if (! $this->primaryValue) {
            throw new InvalidArgumentException('Primary value is empty.');
        }
        if (count($this->primaryValue) != count(static::$primaryKey)) {
            throw new InvalidArgumentException('Primary value length is wrong.');
        }
        foreach (static::$primaryKey as $k => $v) {
            if (! isset($this->primaryValue[$k])) {
                throw new InvalidArgumentException('Invalid primary value.');
            }
            switch ($v) {
                case ColumnTypeConst::CONST_INTEGER:
                    if (! is_int($this->primaryValue[$k])) {
                        throw new InvalidArgumentException('Invalid primary value.');
                    }
                    break;
                case ColumnTypeConst::CONST_STRING:
                    if (! is_string($this->primaryValue[$k])) {
                        throw new InvalidArgumentException('Invalid primary value.');
                    }
                    break;
            }
        }
        return $this;
    }

    /**
     * TableStore table name
     * @return mixed
     */
    public static function getTable()
    {
        return static::$table;
    }

    /**
     * TableStore primary key
     * @return array
     */
    public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    protected function createRequestPrimaryKey($value)
    {
        $request = [];
        $index = 0;
        foreach (static::getPrimaryKey() as $key => $type) {
            if (! isset($value[$index])) {
                throw new InvalidArgumentException("incorrect parameter");
            }
            switch ($type) {
                case ColumnTypeConst::CONST_INTEGER:
                    if (! is_int($value[$index])) {
                        throw new InvalidArgumentException("incorrect parameter type");
                    }
                    $request[$key] = (int)$value[$index];
                    break;
                case ColumnTypeConst::CONST_STRING:
                    $request[$key] = (string)$value[$index];
                    break;
                default:
                    throw new InvalidArgumentException("incorrect parameter type");
                    break;

            }
            $index++;
        }
        return $request;
    }

    public function setPrimaryValue($value)
    {
        $this->primaryValue = $this->createRequestPrimaryKey($value);
    }

    private function setResponse($response)
    {
        $this->response = $response;
        $this->responseKeys = $response['row']['primary_key_columns'];
        $this->columns = $response['row']['attribute_columns'];
    }

    /**
     * @return Model|null
     */
    private function getRow()
    {
        $this->request = array (
            'table_name' => static::getTable(),
            'primary_key' => $this->primaryValue
        );
        $this->setResponse(static::getDb()->getRow($this->request));

        return empty($this->responseKeys) ? null : $this;
    }

    public function setExceptionConst($const = RowExistenceExpectationConst::CONST_IGNORE)
    {
        $this->expectionConst = $const;
        return $this;
    }

    /**
     * @param array $value
     * @return Model|null
     */
    public static function find($value)
    {
        $model = new static();
        $model->setPrimaryValue($value);
        return $model->getRow();
    }

    /**
     * @param array $value
     * @return Model
     */
    public static function findOrNew($value)
    {
        $model = new static($value);
        if ($row = $model->getRow()) {
            $model = $row;
        }
        return $model;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function save()
    {
        $this->invalidPrimaryValue();
        $this->request = [
            'table_name' => static::getTable(),
            'condition' => $this->expectionConst,
            'primary_key' => $this->primaryValue,
            'attribute_columns_to_put' => $this->columns,
        ];
        static::getDb()->updateRow($this->request);
    }
}