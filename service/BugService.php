<?php
require_once ROOT . '/dbaccess/Connector.php';

class BugService {

    /**
     * Search for bugs with filters
     * @param start
     * @param limit
     * @param person
     * @param priority
     * @param status
     * @param project
     * @param type
     * @param showClosed
     * @param sortByUpdate
     */
    public static function search($start, $limit, $person, $priority, $status, $project, $type, $title, $showClosed,
            $sortByUpdate) {
        // assemble where
        $where = 'where 1 = 1';
        if ($person > 0) {
            $where .= ' and id_assigned = :person';
        }
        if ($priority > 0) {
            $where .= ' and id_priority = :priority';
        }
        if ($status > 0) {
            $where .= ' and id_status = :status';
        }
        if ($project > 0) {
            $where .= ' and id_project = :project';
        }
        if ($type > 0) {
            $where .= ' and id_btype = :type';
        }
        if ($title) {
            $where .= ' and (lower(title) like lower(:title1) or lower(description) like lower(:title2))';
        }
        if ($showClosed !== true) {
            $where .= ' and id_status not in(5, 6, 7)';
        }

        // statements
        $dbh = Connector::getConnection('bugs');
        $stmt1 = $dbh->prepare('select * from v_bug ' . $where . ' order by ' .
                ($sortByUpdate === true ? 'tstamp' : 'id') . ' desc limit ' . $start . ', ' . $limit);
        $stmt2 = $dbh->prepare('select count(1) from v_bug ' . $where);
        
        // params
        if ($person > 0) {
            $stmt1->bindValue('person', $person);
            $stmt2->bindValue('person', $person);
        }
        if ($priority > 0) {
            $stmt1->bindValue('priority', $priority);
            $stmt2->bindValue('priority', $priority);
        }
        if ($status > 0) {
            $stmt1->bindValue('status', $status);
            $stmt2->bindValue('status', $status);
        }
        if ($project > 0) {
            $stmt1->bindValue('project', $project);
            $stmt2->bindValue('project', $project);
        }
        if ($type > 0) {
            $stmt1->bindValue('type', $type);
            $stmt2->bindValue('type', $type);
        }
        if ($title) {
            $titleToSend = '%' . $title . '%';
            $stmt1->bindValue('title1', $titleToSend);
            $stmt1->bindValue('title2', $titleToSend);
            $stmt2->bindValue('title1', $titleToSend);
            $stmt2->bindValue('title2', $titleToSend);
        }
        
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