<?php
require_once ROOT . '/service/GenericService.php';
require_once ROOT . '/dbaccess/Connector.php';

class TestEndpoint {
    public static function failtransaction() {
        $dbh = Connector::getConnection('bugs');
        $dbh->beginTransaction();

        $id1 = GenericService::insert($dbh, 'bbadge', array(
            'name' => 'test1',
            'image_url' => 'test1',
            'id_creator' => 1
        ));

        $id2 = GenericService::insert($dbh, 'bbadge', array(
            'name' => 'test2',
            'image_url' => 'test2',
            'id_creator' => 2
        ));

        // logging
        echo "<<<<< bbadge records with id $id1 and $id2 will not be saved because of failed transaccion >>>>>";
        Utils::exception('error inside a transaction', 200);
        
        // this line is unreachable, so the transaction will be discarded
        $dbh->commit();
    }
}
?>