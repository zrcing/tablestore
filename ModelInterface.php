<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */
namespace Planfox\Component\Tablestore;

use Aliyun\OTS\RowExistenceExpectationConst;

interface ModelInterface
{
    /**
     * TableStore table name
     * @return mixed
     */
    public static function getTable();

    /**
     * TableStore primary key
     * @return array
     */
    public static function getPrimaryKey();

}