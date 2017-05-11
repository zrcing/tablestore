<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Aliyun\OTS\RowExistenceExpectationConst;
use Planfox\Component\Tablestore\Exception\InvalidArgumentException;

class Model implements ModelInterface
{
    /**
     * Default connection tablestore
     * @var string
     */
    protected $connection = 'default';

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
    public function getDb()
    {
        return Connection::getDb($this->connection);
    }

    public function __set($name, $value)
    {
        $this->verifyColumn($name);
        $this->columns[$name] = $value;
    }

    public function __isset($name)    {

        return isset($this->columns[$name]);
    }

    public function __get($name)
    {
        $this->verifyColumn($name);
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    public function __construct($value = [])
    {
        if ($value) {
            $this->setPrimaryValue($value);
        }
    }

    private function verifyColumn($name)
    {
        if (! in_array($name, static::$fields)) {
            throw new InvalidArgumentException('Invalid column fields.');
        }
        return $this;
    }

    private function verifyPrimaryValue()
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

    public function setResponse($response)
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
        $this->setResponse($this->getDb()->getRow($this->request));

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

    /**
     * @param array $startPK
     * @param array $endPK
     * @param DirectionConst $direction
     * @param int $limit
     * @return mixed
     */
    public static function findAll($startPK, $endPK, $direction = DirectionConst::CONST_FORWARD, $limit = 5000)
    {
        $records = [];
        $o = new static();
        while (! empty ($startPK) && $limit > 0) {

            $o->setRequest(array (
                'table_name' => static::getTable(),
                'direction' => $direction,
                'inclusive_start_primary_key' => $startPK, // 开始主键
                'exclusive_end_primary_key' => $endPK, // 结束主键
                'limit' => $limit
            ));
            $response = $o->getDb()->getRange($o->getRequest());
            foreach ($response['rows'] as $rowData) {
                $limit --;
                $temp = new static();
                $temp->setPrimaryValue(array_values($rowData['primary_key_columns']));
                $temp->setResponse($rowData['attribute_columns']);
                $records[] = $temp;
                // 处理每一行数据
            }

            $startPK = $response['next_start_primary_key'];
        }
        return $records;
    }

    public function setRequest($request)
    {
        $this->request = $request;
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
        $this->verifyPrimaryValue();
        $this->request = [
            'table_name' => static::getTable(),
            'condition' => $this->expectionConst,
            'primary_key' => $this->primaryValue,
            'attribute_columns_to_put' => $this->columns,
        ];
        $this->getDb()->updateRow($this->request);
    }

    public function delete()
    {
        $this->verifyPrimaryValue();
        $this->request = [
            'table_name' => static::getTable(),
            'condition' => $this->expectionConst,
            'primary_key' => $this->primaryValue
        ];
        $this->getDb()->deleteRow($this->request);
    }

    public static function createTable()
    {
        $o = new static();
        $o->setRequest([
            'table_meta' => [
                'table_name' => static::getTable(),
                'primary_key_schema' => static::getPrimaryKey()
            ],
            'reserved_throughput' => [
                'capacity_unit' => [
                    'read' => 0, // 预留读写吞吐量设置为：0个读CU，和0个写CU
                    'write' => 0
                ]
            ]
        ]);
        $o->getDb()->createTable($o->getRequest());

    }
}