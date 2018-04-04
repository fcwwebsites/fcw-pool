<?PHP

//Error reporting
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

// System Data
define ( 'SYSTEM_NAME', 'FCW Pool for ADV' );
define ( 'BASE_PATH', '/var/www/html/crud-model/fcw-config.php');

// Database
define ( 'DB_HOST', 'localhost' );
define ( 'DB_USER', 'root' );
define ( 'DB_PASSWORD', 'masterblaster' );
define ( 'DB_NAME', 'travelsystem' );
define ( 'DB_PREFIX', 'ts_' );


//Classes Autoload
spl_autoload_register(function ($class_name) {
    include 'class/' . $class_name . '.class.php';
});