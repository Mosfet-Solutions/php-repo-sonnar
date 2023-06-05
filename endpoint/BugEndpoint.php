<?php
require_once ROOT . '/service/BugService.php';
require_once ROOT . '/service/GenericService.php';

class BugEndpoint {
    public static function search() {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        $person = intval(Utils::getParam('person') ?: 0);
        $priority = intval(Utils::getParam('priority') ?: 0);
        $status = intval(Utils::getParam('status') ?: 0);
        $project = intval(Utils::getParam('project') ?: 0);
        $type = intval(Utils::getParam('type') ?: 0);
        $title = Utils::getParam('title') ?: "";
        $hideClosed = intval(Utils::getParam('hide_closed') ?: 0);
        $showClosed = $hideClosed === 2;  // 2 = no
        $sortByUpdate = intval(Utils::getParam('sort_by_update')) == 1;
        $data = BugService::search($start, $limit, $person, $priority, $status, $project, $type, $title, $showClosed, $sortByUpdate);
        return $data;
    }

    public static function getbyid() {
        $id = intval(Utils::getParam('id', true));
        $data = GenericService::getById('bugs', 'v_bug', $id);
        return $data;
    }

    public static function register() {
        $json = Utils::getPayload();
        $id = GenericService::insert('bugs', 'bug', array(
            'title' => $json->title,
            'description' => $json->description,
            'id_assigned' => intval($json->id_assigned),
            'id_btype' => intval($json->id_btype),
            'id_creator' => Utils::getSessionUserId(),
            'id_priority' => intval($json->id_priority),
            'id_project' => intval($json->id_project),
            'id_status' => intval($json->id_status)
        ));
        return $id;
    }

    public static function update() {
        $id = intval(Utils::getParam('id', true));
        $json = Utils::getPayload();
        $updatedRows = GenericService::update('bugs', 'bug', $id, array(
            'title' => $json->title,
            'description' => $json->description,
            'id_assigned' => intval($json->id_assigned),
            'id_btype' => intval($json->id_btype),
            'id_priority' => intval($json->id_priority),
            'id_project' => intval($json->id_project),
            'id_status' => intval($json->id_status)
        ));
        return $updatedRows;
    }

    public static function getcombodata() {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_bug_combo', $start, $limit);
    }
}
?>