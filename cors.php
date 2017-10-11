<?php
// Copyright Monwoo 2017, service@monwoo.com
// Enabling CORS in bultin dev to test locally with multiples servers
// used to replace lack of .htaccess support inside php builting webserver.
// call with :
// sudo php -S localhost:80 -t . CORS.php
$CORS_ORIGIN_ALLOWED = "http://localhost:8081";    

function consoleLog($level, $msg) {
    file_put_contents("php://stdout", "[" . $level . "] " . $msg . "\n");
}

function applyCorsHeaders() {
    global $CORS_ORIGIN_ALLOWED;
    header("Access-Control-Allow-Origin: {$CORS_ORIGIN_ALLOWED}");
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
}

if (preg_match('/\.(?:png|jpg|jpeg|gif|csv)$/', $_SERVER["REQUEST_URI"])) {
    consoleLog('info', "Transparent routing for : " . $_SERVER["REQUEST_URI"]);
    return false;
} else if (preg_match('/^.*$/i', $_SERVER["REQUEST_URI"])) {
    $filePath = "{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER["REQUEST_URI"]}";
    applyCorsHeaders();

    if (!file_exists($filePath)) {
        consoleLog('info', "File not found Error for : " . $_SERVER["REQUEST_URI"]);
        // return false;
        http_response_code(404);
        echo "File not Found : {$filePath}";
        return true;
    }
    $mime = mime_content_type($filePath);
    // https://stackoverflow.com/questions/45179337/mime-content-type-returning-text-plain-for-css-and-js-files-only
    // https://stackoverflow.com/questions/7236191/how-to-create-a-custom-magic-file-database
    // Otherwise, you can use custom rules :
    $customMappings = [
        'js' => 'text/javascript', //'application/javascript',
        'css' => 'text/css',
    ];
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    // consoleLog('Debug', $ext);
    if (array_key_exists($ext, $customMappings)) {
        $mime = $customMappings[$ext];
    }
    consoleLog('info', "CORS {$CORS_ALLOWED} added to file {$mime} : {$filePath}");
    header("Content-type: {$mime}");
    echo file_get_contents($filePath);
    return true;
} else {
    consoleLog('info', "Not catched by routing, Transparent serving for : "
    . $_SERVER["REQUEST_URI"]);
    return false; // Let php bultin server serve
}