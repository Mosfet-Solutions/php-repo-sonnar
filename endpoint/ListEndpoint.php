<?php
require_once ROOT . '/service/GenericService.php';

class ListEndpoint
{
    public static function get()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_list', $start, $limit);
    }

    public static function appendbug()
    {
        $json = Utils::getPayload();
        Utils::validate($json->id_list, 'missing list id', 400);
        Utils::validate($json->id_bug, 'missing bug id', 400);
        return GenericService::insert(
            'bugs', 'rel_list_bug', array(
            'id_list' => intval($json->id_list),
            'id_bug' => intval($json->id_bug)
            )
        );
    }

    public static function getbugs()
    {
        $listId = Utils::getParam('id_list', true);
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        $whereQuery = 'id_list = :id_list';
        $whereValues = array('id_list' => $listId);
        return GenericService::findAll('bugs', 'v_rel_list_bug', $start, $limit, $whereQuery, $whereValues);
    }

    public static function getbyid()
    {
        $id = intval(Utils::getParam('id', true));
        $data = GenericService::getById('bugs', 'list', $id);
        return $data;
    }

    public static function register()
    {
        $json = Utils::getPayload();
        Utils::validate($json->name, 'name is required', 400);
        $userId = Utils::getSessionUserId();
        return GenericService::insert(
            'bugs', 'list', array(
            'id_creator' => $userId,
            'name' => $json->name
            )
        );
    }

    public static function update()
    {
        $id = Utils::getParam('id', true);
        $json = Utils::getPayload();
        Utils::validate($json->name, 'name is required', 400);
        return GenericService::update(
            'bugs', 'list', $id, array(
            'name' => $json->name
            )
        );
    }

    public static function removebug()
    {
        $relId = Utils::getParam('id', true);
        return GenericService::deleteById('bugs', 'rel_list_bug', $relId);
    }
}
?>
