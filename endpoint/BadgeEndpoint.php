<?php
require_once ROOT . '/service/GenericService.php';
require_once ROOT . '/service/BadgeService.php';

class BadgeEndpoint
{
    public static function findallbyassigned()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        $personId = intval(Utils::getParam('person_id', true));
        $whereQuery = 'id_bperson = :id_bperson';
        $whereData = array('id_bperson' => $personId);
        return GenericService::findAll('bugs', 'v_rel_bbadge_bperson', $start, $limit, $whereQuery, $whereData);
    }

    public static function findall()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_bbadge', $start, $limit);
    }

    public static function findallassignations()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_rel_bbadge_bperson', $start, $limit);
    }

    public static function findallbycreator()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        $sessionUserId = Utils::getSessionUserId();
        $whereQuery = 'id_creator = :id_creator';
        $whereData = array('id_creator' => $sessionUserId);
        return GenericService::findAll('bugs', 'bbadge', $start, $limit, $whereQuery, $whereData, 'id desc');
    }

    public static function findallassignationsbycreator()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        $sessionUserId = Utils::getSessionUserId();
        $whereQuery = 'bbadge_id_creator = :id_creator';
        $whereData = array('id_creator' => $sessionUserId);
        return GenericService::findAll('bugs', 'v_rel_bbadge_bperson', $start, $limit, $whereQuery, $whereData);
    }

    public static function assign()
    {
        $json = Utils::getPayload();
        $badgeId = $json->id_badge;
        $personId = $json->id_person;
        Utils::validate($badgeId, 'badge id is required', 400);
        Utils::validate($badgeId, 'person id is required', 400);
        $sessionUserId = Utils::getSessionUserId();
        return BadgeService::assign($badgeId, $personId, $sessionUserId);
    }

    public static function unassign()
    {
        $assignationId = Utils::getParam('id', true);
        $sessionUserId = Utils::getSessionUserId();
        return BadgeService::unassign($assignationId, $sessionUserId);
    }

    public static function register()
    {
        $json = Utils::getPayload();
        $name = $json->name;
        $imageUrl = $json->image_url;
        Utils::validate($name, 'name is required', 400);
        Utils::validate($imageUrl, 'image url is required', 400);
        $sessionUserId = Utils::getSessionUserId();
        return GenericService::insert(
            'bugs', 'bbadge', array(
            'id_creator' => $sessionUserId,
            'name' => $name,
            'image_url' => $imageUrl
            )
        );
    }

    public static function getbyid()
    {
        $id = Utils::getParam('id', true);
        return GenericService::getById('bugs', 'bbadge', $id);
    }

    public static function update()
    {
        $badgeId = Utils::getParam('id', true);
        $json = Utils::getPayload();
        $name = $json->name;
        $imageUrl = $json->image_url;
        Utils::validate($name, 'name is required', 400);
        Utils::validate($imageUrl, 'image url is required', 400);
        $sessionUserId = Utils::getSessionUserId();
        return BadgeService::update($badgeId, $sessionUserId, $name, $imageUrl);
    }
}
?>
