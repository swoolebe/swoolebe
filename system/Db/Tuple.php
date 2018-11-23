<?php
namespace System\Db;

use System\Be;
use System\CacheProxy;

/**
 * 数据库表行记录
 */
abstract class Tuple
{
    /**
     * 默认查询的数据库
     *
     * @var string
     */
    protected $db = 'master';

    /**
     * 应用名
     *
     * @var string
     */
    protected $app = '';

    /**
     * 表全名
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = '';

    protected $quote = '`'; // 字段或表名转义符 mysql: `

    /**
     * 启动缓存代理
     *
     * @param int $expire 超时时间
     * @return CacheProxy | Mixed
     */
    public function withCache($expire = 600)
    {
        return new CacheProxy($this, $expire);
    }

    /**
     * 切换库
     *
     * @param string $db db配置文件中的库名
     * @return Tuple
     */
    public function db($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * 绑定一个数据源， GET, POST, 或者一个数组, 对象
     *
     * @param string | array | object $data 要绑定的数据对象
     * @return \System\Db\Tuple | bool
     * @throws DbException
     */
    public function bind($data)
    {
        if (!is_object($data) && !is_array($data)) {
            throw new DbException('绑定失败，不合法的数据源！');
        }

        if (is_object($data)) $data = get_object_vars($data);

        $properties = get_object_vars($this);

        foreach ($properties as $key => $value) {
            if (isset($data[$key])) {
                $val = $data[$key];
                $this->$key = $val;
            }
        }

        return $this;
    }

    /**
     * 加载记录
     *
     * @param string|int|array $field 要加载数据的键名，$val == null 时，为指定的主键值加载，
     * @param string $value 要加载的键的值
     * @return \System\Db\Tuple | false
     * @throws DbException
     */
    public function load($field, $value = null)
    {
        $sql = null;
        $values = [];

        if ($value === null) {
            if (is_array($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE';
                foreach ($field as $key => $val) {
                    $sql .= ' ' . $this->quote . $key . $this->quote . '=? AND';
                    $values[] = $val;
                }
                $sql = substr($sql, 0, -4);
            } elseif (is_numeric($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . ' = \'' . intval($field) . '\'';
            } elseif (is_string($field)) {
                $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $field;
            }
        } else {
            if (is_array($field)) {
                throw new DbException('Tuple->load() 方法参数错误！');
            }
            $sql = 'SELECT * FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $field . $this->quote . '=?';
            $values[] = $value;
        }

        $db = Be::getDb($this->db);
        $tuple = $db->getObject($sql, $values);

        if (!$tuple) {
            throw new DbException('未找到指定数据记录！');
        }

        return $this->bind($tuple);
    }

    /**
     * 保存数据到数据库
     *
     * @return Tuple
     */
    public function save()
    {
        $db = Be::getDb($this->db);

        $primaryKey = $this->primaryKey;
        if ($this->$primaryKey) {
            $db->update($this->tableName, $this, $this->primaryKey);
        } else {
            $db->insert($this->tableName, $this);
            $this->$primaryKey = $db->getLastInsertId();
        }

        return $this;
    }

    /**
     * 删除指定主键值的记录
     *
     * @param int $id 主键值
     * @return Tuple
     * @throws DbException
     */
    public function delete($id = null)
    {
        $primaryKey = $this->primaryKey;
        if ($id === null) $id = $this->$primaryKey;

        if ($id === null) {
            throw new DbException('参数缺失, 请指定要删除记录的编号！');
        }

        $db = Be::getDb($this->db);
        $db->execute('DELETE FROM ' . $this->quote . $this->tableName . $this->quote . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?', array($id));

        return $this;
    }

    /**
     * 自增某个字段
     *
     * @param string $field 字段名
     * @param int $step 自增量
     * @return Tuple
     */
    public function increment($field, $step = 1)
    {
        $primaryKey = $this->primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '+' . $step . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?';

        $db = Be::getDb($this->db);
        $db->execute($sql, array($id));

        return $this;
    }

    /**
     * 自减某个字段
     *
     * @param string $field 字段名
     * @param int $step 自减量
     * @return Tuple
     */
    public function decrement($field, $step = 1)
    {
        $primaryKey = $this->primaryKey;
        $id = $this->$primaryKey;
        $sql = 'UPDATE ' . $this->quote . $this->tableName . $this->quote . ' SET ' . $this->quote . $field . $this->quote . '=' . $this->quote . $field . $this->quote . '-' . $step . ' WHERE ' . $this->quote . $this->primaryKey . $this->quote . '=?';

        $db = Be::getDb($this->db);
        $db->execute($sql, array($id));

        return $this;
    }

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
     * 获取主键名
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * 转成简单数组
     *
     * @return array
     */
    public function toArray() {
        $array = get_object_vars($this);
        unset($array['db'], $array['tableName'], $array['primaryKey'], $array['quote']);

        return $array;
    }

    /**
     * 转成简单对象
     *
     * @return Object
     */
    public function toObject() {
        return (Object) $this->toArray();
    }
}
