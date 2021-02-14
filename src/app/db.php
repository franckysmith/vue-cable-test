<?
// File:       db.php
// Contents:   app db functionality
// Created:    22.01.2021
// Programmer: Edward A. Shiryaev

require_once 'dbbase.php';

class db extends dbbase {
  
        // Primary key fields by table names. Single field keys are assumed. Primary fields have to be listed here only
        // if their names don't obey the default rule:
        //    <field name> == <table name>."id" e.g. "userid" = "user"."id"
        // The array is used by dbbase::where(), see below, to resolve integer value, if any, into primary field value.
  
  protected static $PRIMARY_FIELDS = array
  (
  );
  
  protected function __construct()
  {
    parent::__construct(array(
      'db_server'     =>  config::DB_SERVER,
      'db_username'   =>  config::DB_USERNAME,    
      'db_password'   =>  config::DB_PASSWORD,
      'db_name'       =>  config::DB_NAME
    ));
  }
  
  //---- public functions ----  

  public static function main()
  {
    new db();
  }
  
  public static function now()
  {
    $rows = db::query('SELECT NOW()');
    return $rows[0]['NOW()'];
  }
  
}

db::main();
?>