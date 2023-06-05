<?php
require_once ROOT . '/dbaccess/Connector.php';

class ActionService
{
    public static function getCount($event, $context)
    {
        $query = 'select count(1) from baction where event = :event and context = :context';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('event', $event);
        $stmt->bindValue('context', $context);
        $stmt->execute();
        $count = intval($stmt->fetchAll(PDO::FETCH_COLUMN)[0]);
        return $count;
    }

    public static function getIdByContent($event, $context, $userId)
    {
        $query = 'select id from baction where event = :event and context = :context and id_user = :userId';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('event', $event);
        $stmt->bindValue('context', $context);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return $data['id'];
        }
        return 0;
    }
}
?>
