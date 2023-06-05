<?php
require_once ROOT . '/dbaccess/Connector.php';

class PersonService {
    public static function getFullByRfc($rfc) {
        $query = 'select * from v_bperson_full where rfc = :rfc';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('rfc', $rfc);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            ErrorHandler::exception('Person not found', 404);
        }
        return $data;
    }

    public static function getFullById($id) {
        $query = 'select * from v_bperson_full where id = :id';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            ErrorHandler::exception('Person not found', 404);
        }
        return $data;
    }

    public static function getById($id) {
        $query = 'select * from bperson where id = :id';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            ErrorHandler::exception('Person not found', 404);
        }
        // secret data
        $data['password'] = '';
        return $data;
    }

    public static function update($idToUpdate, $sessionUserId, $aboutMe, $photoUrl) {
        $query = 'update bperson set about_me = :about_me, photo_url = :photo_url ' .
                'where id = :id_to_update and id = :session_user_id';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('about_me', $aboutMe);
        $stmt->bindValue('photo_url', $photoUrl);
        $stmt->bindValue('id_to_update', $idToUpdate);
        $stmt->bindValue('session_user_id', $sessionUserId);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function finderSearch($name, $start, $limit) {
        $query1 = 'select id, shortname person from bperson ' .
                'where lower(name) like :token or lower(lastname) like :token or lower(shortname) like :token ' .
                'order by shortname limit ' . intval($start) . ', ' . intval($limit);
        $query2 = 'select count(1) from bperson ' .
                'where lower(name) like :token or lower(lastname) like :token or lower(shortname) like :token';
        $dbh = Connector::getConnection('bugs');
        $stmt1 = $dbh->prepare($query1);
        $stmt2 = $dbh->prepare($query2);
        $token = '%' . strtolower($name) . '%';
        $stmt1->bindValue('token', $token);
        $stmt2->bindValue('token', $token);
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
}
?>