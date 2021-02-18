<?
// File:       config.php
// Contents:   app config script
// Created:    22.01.2021
// Programmer: Edward A. Shiryaev

        // App root absolute directory and URL.
        
define('APP_DIR', str_replace('\\', '/', __DIR__));
define('APP_URL', substr($_SERVER['PHP_SELF'], 0, - (strlen($_SERVER['SCRIPT_FILENAME']) - strlen(APP_DIR))));

        // 2-letter language code, either 'en' or 'fr', used to select UI string resources.
  
@define('LANG', $_GET['lang'] ? $_GET['lang'] : ($_COOKIE['lang'] ? $_COOKIE['lang'] : 'en'));

define('ASSERT_ENABLED', true);         // comment/uncomment to disable/enable assert()
define('ASSERT_TRIGGER_ERROR', true);   // true value makes assert() trigger errors - good for release mode if errors
require_once APP_DIR.'/lib/assert.php'; // are handled properly

        // Introduced to sync PHP time with MySQL time which is local.

define('TIMEZONE', 'Europe/Paris');

        // Introduced to be able to run server's api.php locally.

define('API_CORS_DEBUG_ORIGIN', 'http://localhost');

class config {
  
        // Database settings.
            
  const DB_SERVER   = 'localhost';
  const DB_USERNAME = 'Smith2138p';
  const DB_PASSWORD = '9Pca#foz2FadW9rx';
  const DB_NAME     = 'Cinod_otherthings';
  
  public static function main()
  {
    // enlarge from default 30 to 60
    ini_set('max_execution_time', 60);
    // enlarge from default '64M' to '256M'
    ini_set('memory_limit', '256M');
    
    // turn off to gurantee they will not interfere with 'magic_quotes_gpc'
    ini_set('magic_quotes_runtime', 0);
    ini_set('magic_quotes_sybase', 0);
    
    // writing php errors to log.txt (not to screen)
    error_reporting(E_ALL);
    ini_set('log_errors_max_len', '0');   // no limit
    ini_set('log_errors' , '1');
    ini_set('error_log', APP_DIR.'/tmp/log.txt');
    ini_set('display_errors' , '1');
    
    // make scripts from 'app' and 'lib' folders includable from anywhere
    set_include_path(
      get_include_path().
      PATH_SEPARATOR.APP_DIR.'/app'.
      PATH_SEPARATOR.APP_DIR.'/lib'
    );
    
    date_default_timezone_set(TIMEZONE);
  }  
}

config::main();
?>