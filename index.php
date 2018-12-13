<?php
/**
 * if you want Error uncomment this and make error_reporting(E_ALL)
 */
// ini_set('display_errors'        , 'On');
// ini_set('display_startup_errors', 'On');
// ini_set('error_reporting'       , 'E_ALL | E_STRICT');
// ini_set('track_errors'          , 'On');
// ini_set('display_errors'        , 1);
error_reporting(0);



/**
 * DEFINE
 */

// Directories
define('VIEW_DIR', __DIR__.'/app/view/');
define('CONTROLLER_DIR', __DIR__.'/app/controller/');
define('MODEL_DIR', __DIR__.'/app/model/');


// Database
define('DB_SERVER_NAME', 'localhost');
define('DB_USER_NAME', 'root');
define('DB_PASSWORD', 'javadkhof');
define('DB_NAME', 'LB');
/**
 * generate token by : sha1(mt_rand(1, 90000) . 'Online_Leader_Bord');
 *
 * it's use by make an default database...if you want to make another database you can copy mysql query on installation and run it on mysql (actually you should change it :D )
 *
 * @define string
 */
define('DEFAULT_TOKEN', 'fd40c20e30d7c258f6bacfe892a5c48a3f7b954d');


//open_ssl (hash set data)
/**
 * see : 
 * http://php.net/manual/en/function.openssl-get-cipher-methods.php
 * http://php.net/manual/en/function.openssl-encrypt.php
 *
 * will decrypt data like this : openssl_decrypt($_POST['hash'], HASH_METHOD, HASH_KEY, $options=0, HASH_IV, $_POST['tag'])
 * you should encrypt data by this method and send an hash and tag to server...
 */
define('HASH_METHOD', 'AES-128-CBC');
// generate by : http://www.miraclesalad.com/webtools/md5.php
define('HASH_KEY', '2fa4231a009e148288114ea5dafc149f');
// has different size in every method
define('HASH_IV',  'a874a935c9680esd');


/**
 * FINISH DEFINE
 */


require_once('include.php');

$model = new Model();
$view = new View();
$controller = new Controller();
