<?php
require_once ROOT . '/dbaccess/Connector.php';
require_once ROOT . '/service/GenericService.php';

class SecurityService {
    public static function access($userId, $password, $userAgent) {

        // check credentials
        $dbh = Connector::getConnection('bugs');
        $query = 'select a.* from v_bperson_full a, bperson b where a.id = :id and b.id = a.id and b.password = :password';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id', $userId);
        $encryptedPassword = md5($password);
        $stmt->bindValue('password', $encryptedPassword);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            Utils::exception('Invalid credentials', 404);
        }

        $clientFingerprint = md5($userAgent);

        // delete any access from the same user agent and old access (general)
        $query = 'delete from accesstoken where (id_user = :id_user and client_fingerprint = :client_fingerprint) ' .
                'or now() > expire_date';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id_user', $userId);
        $stmt->bindValue('client_fingerprint', $clientFingerprint);
        $stmt->execute();

        // insert new access
        $accessTokenId = GenericService::insert('bugs', 'accesstoken', array(
            'id_user' => $userId,
            'client_fingerprint' => $clientFingerprint
        ));
        $token = md5('_token#' . $accessTokenId);
        $data['token'] = $token;

        // update access
        $query = 'update accesstoken set token = :token, expire_date = date_add(expire_date, interval 1 day) where id = :id';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('token', $token);
        $stmt->bindValue('id', $accessTokenId);
        $stmt->execute();

        return $data;
    }

    /** validate token or @throws 401 unauthorized */
    public static function validateToken($sessionUserId, $sessionUserToken, $userAgent) {

        // check if exists
        Utils::validate($sessionUserId && $sessionUserToken && $userAgent, 'unauthorized', 401);
        $query = 'select id from accesstoken where token = :token and id_user = :id_user and ' .
                'client_fingerprint = :client_fingerprint and now() <= expire_date';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('token', $sessionUserToken);
        $stmt->bindValue('id_user', $sessionUserId);
        $clientFingerprint = md5($userAgent);
        $stmt->bindValue('client_fingerprint', $clientFingerprint);
        $stmt->execute();
        $accessData = $stmt->fetch(PDO::FETCH_ASSOC);
        Utils::validate($accessData, 'unauthorized', 401);
        $accessId = $accessData['id'];

        // add 1 day
        $query = 'update accesstoken set expire_date = date_add(now(), interval 1 day) where id = :id';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('id', $accessId);
        $stmt->execute();
    }

    /** validate user is system admin or @throws 403 forbidden */
    public static function validateSessionUserIsSystemAdmin($sessionUserId) {
        $user = GenericService::getById('bugs', 'bperson', $sessionUserId);
        Utils::validate(intval($user['is_system_admin']) === 1, 'forbidden', 403);
    }
}
?>