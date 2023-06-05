<?php
require_once ROOT . '/dbaccess/Connector.php';

class GenericService
{

    /**
     * 
     * connect to DB @param connector can be the string from connectors or already a connection 
     */
    private static function connect($connector, $tableName)
    {
        if (gettype($connector) === 'string') {

            // get connection
            $dbh = Connector::getConnection($connector);

            // validate table
            $query = 'select 1 from information_schema.tables '.
                    'where table_schema = "' . CONNECTORS[$connector]['db_name'] . '" and table_name = :tableName';
            $stmt = $dbh->prepare($query);
            $stmt->bindValue('tableName', $tableName);
            $stmt->execute();
            Utils::validate($stmt->fetch(), "table [$tableName] not found", 404);
        } else {
            // if not string, it already should be a connection
            $dbh = $connector;
        }
        return $dbh;
    }

    /**
     * @return subset [@param start - @param limit] of records from table @param tableName inside @param connector
     * @param  whereQuery example = "lastname = :lastname and age = :age"
     * @param  whereData example = "array("lastname" => "perez", "age" => 18)"
     * @param  sortQuery example = "id desc"
     * @throws 404 if invalid @param connector
     * @throws 404 if invalid @param tableName
     */
    public static function findAll($connector, $tableName, $start, $limit, $whereQuery = '', $whereData = null,
        $sortQuery = null
    ) {
        $dbh = self::connect($connector, $tableName);
        $query1 = "select * from $tableName";
        $query2 = "select count(1) from $tableName";
        if ($whereQuery) {
            $query1 .= " where $whereQuery";
            $query2 .= " where $whereQuery";
        }
        if ($sortQuery) {
            $query1 .= ' order by ' . $sortQuery;
        }
        $query1 .= " limit " . intval($start) . ', ' . intval($limit);
        $stmt1 = $dbh->prepare($query1);
        $stmt2 = $dbh->prepare($query2);
        if ($whereData) {
            foreach ($whereData as $column => $value) {
                $stmt1->bindValue($column, $value);
                $stmt2->bindValue($column, $value);
            }
        }
        $stmt1->execute();
        $stmt2->execute();
        $list = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $count = intval($stmt2->fetchAll(PDO::FETCH_COLUMN)[0]);
        return (object) array(
            'list' => $list,
            'count' => $count,
            'start' => $start,
            'limit' => $limit
        );
    }

    /**
     * Example:
     * connector = 'bugs'
     * tableName = 'accesstoken'
     * data = {
     *   id_user => 1,
     *   user_agent => 'abcdefghijklmnopqrstuvwxyz'
     * }
     */
    public static function insert($connector, $tableName, $data)
    {
        $dbh = self::connect($connector, $tableName);
        $query = 'insert into ' . $tableName . ' (' . implode(',', array_keys($data)) . ') ' .
                'values (:' . implode(', :', array_keys($data)) . ')';
        $stmt = $dbh->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $dbh->lastInsertId();
    }

    /**
     * 
     * Find a row by id or @throws 404 
     */
    public static function getById($connector, $tableName, $id)
    {
        $dbh = self::connect($connector, $tableName);
        $query = 'select * from ' . $tableName . ' where id = :id';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        Utils::validate($data, 'Row not found', 404);
        return $data;
    }

    /**
     * Example:
     * connector = 'bugs'
     * tableName = 'accesstoken'
     * id = 1
     * data = {
     *   id_user => 1,
     *   user_agent => 'abcdefghijklmnopqrstuvwxyz'
     * }
     */
    public static function update($connector, $tableName, $id, $data)
    {
        $dbh = self::connect($connector, $tableName);
        $query = 'update ' . $tableName . ' set id = id';
        foreach (array_keys($data) as $column) {
            $query .= ", $column = :$column";
        }
        $query .= ' where id = :id';
        $stmt = $dbh->prepare($query);
        foreach ($data as $column => $value) {
            $stmt->bindValue($column, $value);
        }
        $stmt->bindValue('id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function deleteById($connector, $tableName, $id)
    {
        $dbh = self::connect($connector, $tableName);
        $query = 'delete from ' . $tableName . ' where id = :id';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function deleteByWhere($connector, $tableName, $whereQuery, $whereData = null)
    {
        $dbh = self::connect($connector, $tableName);
        $query = "delete from $tableName where $whereQuery";
        $stmt = $dbh->prepare($query);
        if ($whereData) {
            foreach ($whereData as $column => $value) {
                $stmt->bindValue($column, $value);
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>
