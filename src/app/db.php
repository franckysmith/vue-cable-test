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
    '`order`' => 'orderid'
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
  
        // For each specified cable, calculates maximum number of pieces simultaneously ordered at some time by current
        // or future orders. Current is an order whose affair's 'receipt_date' < 'now' and 'return_date' > 'now', future
        // is an order whose 'receipt_date' > 'now'. Only 'done' orders are counted.
        // Parameters:
        //    $cableids  - i-array of cable ids for which to calculate numbers of ordered pieces
        // Returns:
        //    a-array of calculated numbers of ordered pieces keyed by cable ids
  
  public static function calcOrdereds($cableids)
  {
    assert(is_array($cableids));
    
    $cableids = implode(',', $cableids);
    
    // if CURDATE() == return_date, we do not include an order to the result set (consider it past order) only if
    // return_time == 'morning' and current time is afternoon, i.e. HOUR(NOw()) > 12 
      
    $query =  "SELECT cableid,receipt_date,receipt_time,return_date,return_time,count ".
              "FROM `order` o ".
              "INNER JOIN affair a ".
              "ON o.cableid IN ($cableids) AND done AND a.affairid=o.affairid AND ".
              "(return_date > CURDATE() OR return_date = CURDATE() AND ".
              "(IFNULL(return_time, '') != 'morning' OR HOUR(NOW()) <= 12)) ".
              "ORDER BY cableid";
    
    // prepare $summables keyed by cable ids
    $summables = [];
    foreach(db::query($query) as &$order) {
      extract($order);
      
      if(!isset($summables[$cableid]))
        $summables[$cableid] = [];
      
      $summables[$cableid][] = [ 'date' => $receipt_date, 'time' => $receipt_time, 'count' => $count ];
      $summables[$cableid][] = [ 'date' => $return_date, 'time' => $return_time, 'count' => -$count ];
    }
    
    $ordereds = [];
    
    // for each cable, calc the peak sum of $count's 
    foreach($summables as $cableid => &$summable) {
      
      usort($summable, function($item1, $item2) {
        if($item1['date'] < $item2['date'])
          return -1;
        else if($item1['date'] > $item2['date'])
          return 1;
        
        if($item1['count'] > 0 && $item2['count'] > 0 || $item1['count'] < 0 && $item2['count'] < 0)
          return 0;
        
        // here dates are equal and counts are of opposite sign
        
        if($item1['time'] == 'morning' && $item2['time'] == 'afternoon')      // 'morning' goes before 'afternoon'
          return -1;
        else if($item2['time'] == 'morning' && $item1['time'] == 'afternoon') // 'morning' goes before 'afternoon'
          return 1;
        
        // if time is NULL for any or both items, we use pessimistic scenario: positive count goes before negative count
        return $item1['count'] > 0 ? -1 : 1;  
      });
      
      // calc $ordered as peak $sum  
      $sum = $ordered = 0;
      foreach($summable as $item) {
        $sum += $item['count'];
        if($sum > $ordered)
          $ordered = $sum;
      }
      
      $ordereds[$cableid] = $ordered;
    }
    
    return $ordereds;
  }
}

db::main();
?>