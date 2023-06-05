<?php
require_once ROOT . '/service/GenericService.php';
require_once ROOT . '/service/ActionService.php';

class ActionEndpoint {
    public static function insert() {
        $json = Utils::getPayload();
        Utils::validate($json->event, 'event is required', 400);
        Utils::validate($json->context, 'context is required', 400);
        $id = GenericService::insert('bugs', 'baction', array(
            'event' => $json->event,
            'context' => $json->context,
            'id_user' => intval(Utils::getSessionUserId())
        ));
        return $id;
    }

    public static function getcount() {
        $event = Utils::getParam('event', true);
        $context = Utils::getParam('context', true);
        return ActionService::getCount($event, $context);
    }

    public static function getidbycontent() {
        $event = Utils::getParam('event');
        $context = Utils::getParam('context');
        $userId = Utils::getSessionUserId();
        $id = ActionService::getIdByContent($event, $context, $userId);
        return $id;
    }

    public static function delete() {
        $actionId = intval(Utils::getParam('id', true));
        $userId = Utils::getSessionUserId();
        // we use session-user-id beacuse ONLY THE OWNER can delete his actions
        $whereQuery = 'id = :id and id_user = :id_user';
        $whereData = array(
            'id' => $actionId,
            'id_user' => $userId
        );
        return GenericService::deleteByWhere('bugs', 'baction', $whereQuery, $whereData);
    }
}
?>