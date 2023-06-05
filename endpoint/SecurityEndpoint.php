<?php
require_once ROOT . '/service/SecurityService.php';

class SecurityEndpoint
{
    public static function access()
    {
        $userId = intval(Utils::getParam('user_id', true));
        $password = Utils::getParam('password', true);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $accessData = SecurityService::access($userId, $password, $userAgent);
        return $accessData;
    }
}
?>
