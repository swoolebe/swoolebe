<?php
namespace System\Db;

/**
 * Class TableConfig
 * @package \System\Db
 */
class TableConfig
{
    /**
     * 表名
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * 字段明细列表
     *
     * @var array
     */
    protected $fields = array();


    /**
     * 获取表名
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * 获取字段明细列表
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * 获取指定字段
     *
     * @param string $fieldName 字段名
     * @return array
     */
    public function getField($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : null;
    }


}
