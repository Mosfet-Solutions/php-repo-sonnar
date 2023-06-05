<?php
require_once ROOT . '/service/GenericService.php';
require_once ROOT . '/service/PersonService.php';

class PersonEndpoint
{
    public static function findall()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_bperson_full', $start, $limit);
    }

    public static function getbyrfcorid()
    {
        $rfc = Utils::getParam('rfc');
        $personId = Utils::getParam('id');
        if ($rfc) {
            return PersonService::getFullByRfc($rfc);
        }
        if ($personId) {
            return PersonService::getFullById($personId);
        }
        Utils::validate(false, 'RFC or ID are required', 400);
    }

    public static function getbyid()
    {
        $id = intval(Utils::getParam('id', true));
        return PersonService::getById($id);
    }

    public static function update()
    {
        $idToUpdate = intval(Utils::getParam('id', true));
        $sessionUserId = Utils::getSessionUserId();
        $json = Utils::getPayload();
        $aboutMe = $json->about_me;
        $photoUrl = $json->photo_url;
        Utils::validate($aboutMe, 'about me is required', 400);
        Utils::validate($photoUrl, 'photo URL is required', 400);
        return PersonService::update($idToUpdate, $sessionUserId, $aboutMe, $photoUrl);
    }

    public static function findersearch()
    {
        $name = Utils::getParam('q', true);
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return PersonService::finderSearch($name, $start, $limit);
    }
}
?>
