<?php
/**
 * @author jtezva 2021/10/20
 * 
 * This file maps any url in the format rutback/[class]/[method][?params...] to call [Class]Endpoint::[method]()
 * 
 * examples:
 * the URL whatever.com/rutback/example/test?hello=1&world=2 will call ExampleEndpoint::test()
 * the URL whatever.com/rutback/example/errorcase will call ExampleEndpoint::errorcase()
 * 
 * @return http 200 in successful response
 * @return http 400 when url is malformed
 * @return http 404 when endpoint not found
 * @return http 404 when method not found
 * @return http 500 when any other error
 * @return http body (json) {
 *     success: (boolean)
 *     message: (string)
 *     data: (object)
 *     exception: (object)
 * }
 * 
 * Apache httpd.conf config needed:
 *  RewriteEngine on
 *  RewriteCond %{REQUEST_FILENAME} !-f
 *  RewriteCond %{REQUEST_FILENAME} !-d
 *  RewriteRule ^(.*)$ router.php [L,QSA]
 */

// to avoid CORS policy
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

// to cut the request if it's a preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    die();
}

define('ROOT', __DIR__);  // for future require_once
ini_set('display_errors', 0);  // hide errors

require_once 'util/Utils.php';
require_once 'util/AppException.php';
require_once 'service/SecurityService.php';

// unexpected errors management
register_shutdown_function("shutdown_function");
function shutdown_function()
{
    $error = error_get_last();
    if ($error) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(
            (object) array(
            'success' => false,
            'message' => 'internal server error',
            'exception' => var_export($error, true)
            )
        );
    }
}

try {
    // whatever.com/example/test?alvaro=1&jair=2
    // 0 => ''
    // 1 => 'rutback'
    // 2 => 'example'
    // 3 => 'test?alvaro=1&jair=2'
    $urlTokens = explode('/', $_SERVER['REQUEST_URI']);
    Utils::validate(sizeof($urlTokens) >= 3, 'malformed URL', 400);
    
    //Change $urlTokens[n] subtracting 1 for master
    $endpoint = ucfirst(strtolower($urlTokens[2])) . 'Endpoint';  // example => ExampleEndpoint
    $method = strtolower(explode('?', $urlTokens[3])[0]);  // clean GET params
    Utils::validate(file_exists("endpoint/$endpoint.php"), 'resource not found (1)', 404);
    include_once "endpoint/$endpoint.php";
    Utils::validate(method_exists($endpoint, $method), 'resource not found (2)', 404);

    // URLs free of security
    $whiteList = array(
        'SecurityEndpoint/access',
        'PublicEndpoint/findall',
        'ActionEndpoint/insert',
        'ActionEndpoint/getcount',
        'TestEndpoint/failtransaction',
        'FileEndpoint/upload',
        'FileEndpoint/edit'
    );

    // URLs only for system admin
    $onlySystemAdminEndpoints = array(
        'CatalogEndpoint/updatecatalogentry',
        'CatalogEndpoint/deletecatalogentry',
        'CatalogEndpoint/insertcatalogentry'
    );

    // login check or throw 401
    if (!in_array("$endpoint/$method", $whiteList)) {
        $sessionUserId = Utils::getSessionUserId();
        $sessionUserToken = Utils::getSessionUserToken();
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        SecurityService::validateToken($sessionUserId, $sessionUserToken, $userAgent);

        // system admin check or throw 401
        if (in_array("$endpoint/$method", $onlySystemAdminEndpoints)) {
            SecurityService::validateSessionUserIsSystemAdmin($sessionUserId);
        }
    }

    // An endpoint can access $_GET $_POST and file_get_contents('php://input') (json payload)
    $data = $endpoint::$method();
    // TODO: 3 different responses here:
    // a) Full response = httpstatus + httpbody
    // b) Detailed data response = success + message + data (httpstatus = 200)
    // c) Minimal data response = ["juan", "maria"] (httpstatus = 200, success = true, message = '')
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        (object) array(
        'success' => true,
        'message' => '',
        'data' => $data
        )
    );
} catch (Exception $e) {
    if ($e instanceof AppException) {  // error management
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($e->getCode() ?: 404);
        echo json_encode(
            (object) array(
            'success' => false,
            'message' => $e->getMessage(),
            'exception' => var_export($e, true)
            )
        );
    } else {
        throw $e;
    }
}
?>
