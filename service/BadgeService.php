<?php
require_once ROOT . '/service/GenericService.php';
require_once ROOT . '/dbaccess/Connector.php';

class BadgeService {
    public static function assign($badgeId, $personId, $sessionUserId) {

        // verify ownership of the badge
        $badgeData = GenericService::getById('bugs', 'bbadge', $badgeId);
        Utils::validate(intval($badgeData['id_creator']) === intval($sessionUserId), 'forbidden', 403);

        // insert assignment
        $id = GenericService::insert('bugs', 'rel_bbadge_bperson', array(
            'id_bbadge' => $badgeId,
            'id_bperson' => $personId
        ));
        return $id;
    }

    public static function unassign($assignationId, $sessionUserId) {

        // verify ownership of the badge
        $assignationData = GenericService::getById('bugs', 'v_rel_bbadge_bperson', $assignationId);
        Utils::validate(intval($assignationData['bbadge_id_creator']) === intval($sessionUserId), 'forbidden', 403);

        // delete
        $deletedRows = GenericService::deleteById('bugs', 'rel_bbadge_bperson', $assignationId);
        return $deletedRows;
    }

    public static function update($badgeId, $sessionUserId, $name, $imageUrl) {
        $query = 'update bbadge set name = :name, image_url = :image_url where id = :id_badge and id_creator = :id_creator';
        $dbh = Connector::getConnection('bugs');
        $stmt = $dbh->prepare($query);
        $stmt->bindValue('name', $name);
        $stmt->bindValue('image_url', $imageUrl);
        $stmt->bindValue('id_badge', $badgeId);
        $stmt->bindValue('id_creator', $sessionUserId);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>