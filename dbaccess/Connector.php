<?php
require_once ROOT . '/dbaccess/connectors.php';

class Connector {
    
    /**
     * Creates a DB connection using @param conector from CONNECTORS and @return dbh PDO connection
     * @throws 404 if @param connector not exist inside of CONNECTORS
     * @throws exception on DB connection
     */
    public static function getConnection($connector) {
        Utils::validate(isset(CONNECTORS[$connector]), "connector [$connector] not found", 404);
        $conString = CONNECTORS[$connector]['connection_string'];
        $dbh = new PDO($conString, CONNECTORS[$connector]['user'], CONNECTORS[$connector]['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }
}
?>