<?php
require_once ROOT . '/service/GenericService.php';

class CatalogEndpoint
{
    
    /**
     * 
     * ...getcatalogs?start=1&limit=1 
     */
    public static function getcatalogs()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'v_catalogs', $start, $limit);
    }

    /**
     * 
     * ...getcatalogvalues?catalog=string&start=1&limit=1 
     */
    public static function getcatalogvalues()
    {
        $catalog = Utils::getParam('catalog', true);
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        Utils::validate(strpos($catalog, 'cat_') === 0, 'Invalid catalog', 404);
        return GenericService::findAll('bugs', $catalog, $start, $limit);
    }

    /**
     * url: ...updatecatalogentry?id=1
     * payload: { catalog: 'string', value: 'string', detail: 'string' }
     */
    public static function updatecatalogentry()
    {
        $id = intval(Utils::getParam('id', true));
        $json = Utils::getPayload();
        Utils::validate(isset($json->catalog) && $json->catalog, 'Missing [catalog] in the body', 400);
        Utils::validate(strpos($json->catalog, 'cat_') === 0, 'Invalid catalog', 404);
        $dataArray = array();
        if (isset($json->value)) {
            $dataArray['value'] = $json->value;
        }
        if (isset($json->detail)) {
            $dataArray['detail'] = $json->detail;
        }
        if (count($dataArray) === 0) {
            return 0; // no updates since there is no input
        }
        return GenericService::update('bugs', $json->catalog, $id, $dataArray);
    }

    /**
     * 
     * ...deletecatalogentry?catalog=string&id=1 
     */
    public static function deletecatalogentry()
    {
        $catalog = Utils::getParam('catalog', true);
        $id = intval(Utils::getParam('id', true));
        Utils::validate(strpos($catalog, 'cat_') === 0, 'Invalid catalog', 404);
        return GenericService::deleteById('bugs', $catalog, $id);
    }

    /**
     * url: ...insertcatalogentry?catalog=string
     * payload: { value: 'string', detail: 'string' }
     */
    public static function insertcatalogentry()
    {
        $catalog = Utils::getParam('catalog', true);
        $json = Utils::getPayload();
        Utils::validate(strpos($catalog, 'cat_') === 0, 'Invalid catalog', 404);
        Utils::validate(isset($json->value) && $json->value, 'Missing [value] in the body', 400);
        $dataArray = array(
            'value' => $json->value
        );
        if (isset($json->detail)) {
            $dataArray['detail'] = $json->detail;
        }
        return GenericService::insert('bugs', $catalog, $dataArray);
    }
}
