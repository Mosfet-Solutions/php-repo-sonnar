<?php
require_once ROOT . '/service/GenericService.php';

class PublicEndpoint
{

    /**
     * 
     * Public access to read tables that start with public_ 
     */
    public static function findall()
    {
        $connector = Utils::getParam('connector', true);
        $tableName = Utils::getParam('table', true);
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll($connector, "public_$tableName", $start, $limit);
    }
}
?>
