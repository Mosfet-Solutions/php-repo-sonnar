<?php
require_once ROOT . '/service/GenericService.php';

class FileEndpoint
{
    public static function upload()
    {

        // validate input
        if (!(isset($_FILES) && isset($_FILES['fileToUpload']) && isset($_POST) && strlen($_POST['name']) > 0)) {
            Utils::exception('Missing input params', 400);
        }

        // validate type
        $imageFileType = strtolower(pathinfo(basename($_FILES['fileToUpload']["name"]), PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" 
        ) {
            Utils::exception("Only JPG, JPEG, PNG & GIF files are allowed", 400);
        }

        // validate if image file is a actual image or fake image
        if (getimagesize($_FILES["fileToUpload"]["tmp_name"]) === false) {
            Utils::exception("File is not an image", 400);
        }

        // validate size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            Utils::exception("Max size is 500KB");
        }

        // assemble name
        $name = $_POST['name'];
        $name = strtolower(str_replace(' ', '-', $name));
        $name = preg_replace('/[^a-z\d-]/', '', $name);
        if (strlen($name) < 3) {
            Utils::exception("Invalid name '$name'");
        }
        
        // THIS CHANGE WITH ENVIRONMENT
        $target_dir = "/var/www/html/taskmanager/resources/img/uploads";
        $target_file = $target_dir . '/' . $name . '.' . $imageFileType;

        // validate repeated name
        if (file_exists($target_file)) {
            Utils::exception("File already exists", 400);
        }

        // move file
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            Utils::exception("Unknow error");
        }

        // insert record
        GenericService::insert(
            'bugs', 'file', array(
            'name' => "$name.$imageFileType"
            )
        );

        echo "{\"success\":true, \"name\":\"$name.$imageFileType\",\"message\":\"Please return and refresh to see the link\"}";
    }

    public static function edit()
    {

        // validate input
        if (!(isset($_FILES) && isset($_FILES['fileToUpload']) && isset($_POST) && strlen($_POST['name']) > 0)) {
            Utils::exception('Missing input params', 400);
        }

        // validate type
        $imageFileType = strtolower(pathinfo(basename($_FILES['fileToUpload']["name"]), PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" 
        ) {
            Utils::exception("Only JPG, JPEG, PNG & GIF files are allowed", 400);
        }

        // validate if image file is a actual image or fake image
        if (getimagesize($_FILES["fileToUpload"]["tmp_name"]) === false) {
            Utils::exception("File is not an image", 400);
        }

        // validate size
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            Utils::exception("Max size is 500KB");
        }

        // assemble name
        $name = $_POST['name'];
        $name = strtolower(str_replace(' ', '-', $name));
        $name = preg_replace('/[^a-z\d-]/', '', $name);
        if (strlen($name) < 3) {
            Utils::exception("Invalid name '$name'");
        }
        
        // THIS CHANGE WITH ENVIRONMENT
        $target_dir = "/var/www/html/taskmanager/resources/img/uploads";
        $target_file = $target_dir . '/' . $name . '.' . $imageFileType;

        // validate repeated name
        if (!file_exists($target_file)) {
            Utils::exception("File not exists", 400);
        }

        
        // remove previous file
        chmod($target_file, 0755);
        if (unlink($target_file)) {
            // move file
            if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                Utils::exception("Unknow error");
            }
        }else{
            Utils::exception("Unknow error");
        }

        echo "{\"success\":true, \"name\":\"$name.$imageFileType\",\"message\":\"Image updated! Please return and refresh to see the link\"}";
    }

    public static function get()
    {
        $start = intval(Utils::getParam('start') ?: 0);
        $limit = intval(Utils::getParam('limit') ?: 999);
        return GenericService::findAll('bugs', 'file', $start, $limit, '', null, 'id desc');
    }
}
?>
