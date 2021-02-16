<?
// File:       api.php
// Contents:   app HTTP POST api
// Created:    22.01.2021
// Programmer: Edward A. Shiryaev

require_once 'config.php';
require_once 'db.php';
require_once 'errorhandled.php';
require_once 'form.php';
require_once 'fieldtypes.php';

class api extends errorhandled {
  
        // Field descriptors keyed by methods, inited after the class definition below.
  
  public static $FIELDS;
  
        // Data as a-array obtained from the json-encoded request's POST body, inited in self::main(), see below.
  
  private static $data = null;
  
        // Any fatal error is outputted as '500 Internal Server Error'.
  
  public static function onError($errormsg)
  {
    header("HTTP/1.1 500 Internal Server Error");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([ 'error' => 'Internal Server Error']);
  }
  
        // Shorthand for triggering fatal errors.  
  
  private static function trigger($errormsg)
  {
    trigger_error($errormsg, E_USER_ERROR);
  }
  
        // Convenience function to call a method from within PHP.
        // Parameters:
        //    $method - method name, see self::main() code for supported methods
        //    $data   - a-array of data that self::main() assumes as request data
        // Returns:
        //    If the method assumes a return value, it is returned and not echoed as follows:
        //      * method-specific a-array if succeeded (see self::main() code for return values), or
        //      * NULL if the method does not assume return value, or
        //      * [ 'error' => <error message> ] in case of fatal error where <error message> is 'Internal Server Error'
        //        if the error was handled by self::errorHandler() or else an original error message
  
  public static function call($method, $data = [])
  {
    $_GET['method'] = $method;
    self::$data = &$data;
    
    ob_start();
    self::main();        
    $result = ob_get_contents();  // NULL if method does not return a value
    ob_end_clean();
    
    // not returning a value is equivalent to returning NULL  
    if($result) {
      $decoded = json_decode($result, true);
      return $decoded !== NULL ? $decoded : [ 'error' => $result ];
    }
  }
  
        // Executes the specified api method.
        // GET-parameters:
        //    method  - method to execute in the format '<entity>_<action>', see code for the list of supported methods
        // POST-data:
        //    method-specific json encoded a-array or nothing, see code
        // Outputs:
        //    method-specific json-encoded a-array or nothing, see code
  
  public static function main()
  {
    $method = (string)@$_GET['method'];
    
    if(defined('API_CORS_DEBUG_ORIGIN'))
      header('Access-Control-Allow-Origin: '.API_CORS_DEBUG_ORIGIN);
    
    if(self::$data === null) {
      self::$data = json_decode($body = file_get_contents('php://input'), true);
      // no POST data -> file_get_contents() -> '' and json_decode() -> null
      if($body != '' && (self::$data === null || !is_array(self::$data))) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([ 'error' => 'Not JSON input data' ]);
        exit;
      }
    }
    
    $result = false;  // by default, means no output
    
    switch($method) {
      
        // Gets all cables from 'cable' table.
        // Input:
        //    nothing
        // Output:
        //    [
        //      {
        //        cableid,
        //        name,
        //        type,
        //        total,
        //        reserved,
        //        ordered,
        //        info,
        //        link,
        //        timestamp
        //      },
        //      ...
        //    ]
      
      case 'cable_get':
        
        $rows = db::rows('*', 'cable');
        
        //---- calc 'ordered' fields and merge them into $result ----
        
        $cableids = [];
        foreach($rows as &$row)
          $cableids[] = $row['cableid'];
          
        $ordereds = db::calcOrdereds($cableids);
        
        $result = [];
        foreach($rows as &$row)
          // @ for highly unlikely case when a cable was deleted by concurrent request between db::rows() and
          // db::calcOrdereds(), so $ordereds[$row['cableid'] is missing - it's because we don't use transaction here
          $result[] = util::array_insert($row, 'info', [ 'ordered' => (int)@$ordereds[$row['cableid']] ]);
        
        break;
      
      
        // Adds new cables to 'cable' table.
        // Input:
        //    [
        //      {
        //        name,
        //        [type],
        //        [total]:    <is set to 0 if missing>,
        //        [reserved]: <is set to 0 if missing>,
        //        [info],
        //        [link]
        //      },
        //      ...
        //    ]
        // Output:
        //    [
        //      <cableid1>: <cableid of the first cable added>,
        //      ...
        //    ], or
        //    {
        //      'error' =>  <error message, if at least one cable violates self::$FIELDS['cable_add'] constraints
        //                  or cable with that name already exists in the 'cable' table>
        //    }
      
      case 'cable_add':
        
        db::query('BEGIN');
        
        $cableids = [];
        foreach(self::$data as &$cable) {
          if($error = form::checkErrors(self::$FIELDS[$method], $cable)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
          
          $values = form::prepareValues(self::$FIELDS[$method], $cable);
          
          if(!($cableid = db::insert('cable', $values, false /* insert */, '', false /* silent */))) {
            if(db::errNo() != db::ER_DUP_ENTRY)
              self::trigger(db::error());   // we don't handle such an error
              
            $result['error'] = "Cable '{$values['name']}' already exists";
            $result['field'] = 'name';
            break;
          }
              
          $cableids[] = $cableid;
        }
        
        if(!isset($result['error'])) {
          db::query('COMMIT');
          $result = &$cableids;
        }
        
        break;
      
      
        // Updates cables in 'cable' table.
        // Input:
        //    [
        //      {
        //        cableid,
        //        [name],
        //        [type],
        //        [total],
        //        [reserved],
        //        [info],
        //        [link]
        //      },
        //      ...
        //    ]
        // Output:
        //    nothing if succeeded, or
        //    {
        //      'error' => <error message, if at least one cable violates self::$FIELDS['cable_update'] constraints
        //                 or cable with that name already exists in the 'cable' table>
        //    }
      
      case 'cable_update':
        
        db::query('BEGIN');        
        
        foreach(self::$data as &$cable) {
          if($error = form::checkErrors(self::$FIELDS[$method], $cable)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
          
          $values = form::prepareValues(self::$FIELDS[$method], $cable);
          
          if(!db::update('cable', $values, $values['cableid'], false /* silent */)) {
            if(db::errNo() != db::ER_DUP_ENTRY)
              self::trigger(db::error());   // we don't handle such an error
              
            $result['error'] = "Cable '{$values['name']}' already exists";
            $result['field'] = 'name';
            break;
          }
        }
        
        if(!isset($result['error']))
          db::query('COMMIT');
        
        break;
      
      
        // Deletes cables from 'cable' table.
        // Input:
        //    [
        //      <cableid1>: <id of the first cable to delete>,
        //      ...
        //    ]
        // Output:
        //    nothing, or
        //    {
        //      error  
        //    } in case of missing or invalid cable id
        
      case 'cable_delete':
        
        if(!self::$data) {
          $result['error'] = 'No cable ids specified';
          break;
        }
        
        db::query('BEGIN');
        
        foreach(self::$data as &$cableid) {
          $values = compact('cableid'); // define $values to be able to pass it by reference to checkErrors()
          if($error = form::checkErrors(self::$FIELDS[$method], $values)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
          
          if(!db::delete('cable', $cableid, false /* silent */)) {
            if(db::errNo() != db::ER_ROW_IS_REFERENCED)
              self::trigger(db::error());   // we don't handle such an error
            
            $result['error'] =  'The cable '.json_encode(compact('cableid')).
                                ' cannot be deleted as there are orders for it in the system';
            break;            
          }
        }
        
        if(!isset($result['error']))
          db::query('COMMIT');
        
        break;
      

        // Gets affairs from the 'affair' table, either all or matching a search criteria if specified.
        // Input:
        //    search criteria as an object with these possible fields with specified values:
        //    {
        //      [affairid],
        //      [tech_id],
        //      [tech_name],
        //      [name],
        //      [ref],
        //      [prep_date],
        //      [prep_time],
        //      [receipt_date],
        //      [receipt_time],
        //      [return_date],
        //      [return_time],
        //      [front],
        //      [monitor],
        //      [stage]
        //    },
        //    or nothing to get all affairs
        // Note:
        //    'master_note', 'tech_note' and 'timestamp' are excluded from the list of search fields until the search by
        //    words and value ranges will be supported
        // Output:
        //    [
        //      {
        //        affairid,
        //        tech_id,
        //        tech_name,
        //        name,
        //        ref,
        //        prep_date,
        //        prep_time,
        //        receipt_date,
        //        receipt_time,
        //        return_date,
        //        return_time,
        //        front,
        //        monitor,
        //        stage,
        //        master_note,        
        //        tech_note,
        //        timestamp
        //      },
        //      ...
        //    ]
      
      case 'affair_get':
        
        // silently unset not searchable fields, if any
        $where = self::$data ? form::prepareValues(self::$FIELDS[$method], self::$data) : '';
        
        $result = db::rows('*', 'affair', $where);
        
        break;
      

        // Adds a new affair to 'affair' table.
        // Input:
        //    {
        //      tech_id,
        //      tech_name,
        //      name,
        //      [ref],
        //      [prep_date],
        //      [prep_time],
        //      receipt_date,
        //      [receipt_time],
        //      return_date,
        //      [return_time],
        //      [front]:        <is set to 0 if missing>,
        //      [monitor]:      <is set to 0 if missing>,
        //      [stage]:        <is set to 0 if missing>,
        //      [master_note],
        //      [tech_note]
        //    }
        // Output:
        //    {
        //      affairid: <id of just added affair>
        //    }, or
        //    {
        //      error:  <error message if any field violates self::$FIELDS['affair_add'] constraints or an affair
        //              with same 'name' and 'receipt_date' or else same 'ref' already exists>
        //    }
      
      case 'affair_add':

        if($error = form::checkErrors(self::$FIELDS[$method], self::$data)) {
          $result['error'] = current($error);
          $result['field'] = current(array_keys($error));
          break;
        }
        
        $values = form::prepareValues(self::$FIELDS[$method], self::$data);
        
        if(!($affairid = db::insert('affair', $values, false /* insert */, '', false /* silent */))) {
          if(db::errNo() != db::ER_DUP_ENTRY)
            self::trigger(db::error());   // we don't handle such an error
            
          extract($values);
          $result['error'] = 'A try to add duplicate affair: '.json_encode(@compact('name', 'receipt_date', 'ref'));
          break;
        }
            
        $result = compact('affairid');
        
        break;
      

        // Updates an existing affair in 'affair' table.
        // Input:
        //    {
        //      affairid,
        //      [tech_id],
        //      [tech_name],
        //      [name],
        //      [ref],
        //      [prep_date],
        //      [prep_time],
        //      [receipt_date],
        //      [receipt_time],
        //      [return_date],
        //      [return_time],
        //      [front],
        //      [monitor],
        //      [stage],
        //      [master_note],
        //      [tech_note]
        //    }
        // Output:
        //    nothing if succeeded, or
        //    {
        //      error:  <error message if any field violates self::$FIELDS['affair_update'] constraints or an affair
        //              with same 'name' and 'receipt_date' or else same 'ref' already exists>
        //    }
     
      case 'affair_update':
        
        if($error = form::checkErrors(self::$FIELDS[$method], self::$data)) {
          $result['error'] = current($error);
          $result['field'] = current(array_keys($error));
          break;
        }
        
        $values = form::prepareValues(self::$FIELDS[$method], self::$data);
        
        if(!db::update('affair', $values, $values['affairid'], false /* silent */)) {
          if(db::errNo() != db::ER_DUP_ENTRY)
            self::trigger(db::error());   // we don't handle such an error
            
          extract($values);
          $result['error'] = 'A try to update into duplicate affair: '.json_encode(@compact('name', 'receipt_date', 'ref'));
          break;
        }
        
        break;
      
      
        // Deletes an affair from 'affair' table.
        // Input:
        //    {
        //      affairid: <id of an affair to delete>
        //    }
        // Output:
        //    nothing, or
        //    {
        //      error  
        //    } in case of missing or invalid affair id
        
      case 'affair_delete':

        if($error = form::checkErrors(self::$FIELDS[$method], self::$data)) {
          $result['error'] = current($error);
          $result['field'] = current(array_keys($error));
          break;
        }
        
        db::delete('affair', self::$data['affairid']);
        
        break;

      
        // Gets orders from the 'order' table, either all or matching a search criteria if specified.
        // Input:
        //    search criteria as an object with these possible fields with specified values:
        //    {
        //      [orderid],
        //      [cableid],
        //      [affairid],
        //      [tech_id],
        //      [count],
        //      [done]
        //    },
        //    or nothing to get all orders
        // Note:
        //    For now the exact match search is only implemented.
        // Output:
        //    [
        //      {
        //        orderid,
        //        cableid,
        //        affairid,
        //        tech_id,
        //        count,
        //        done,
        //        timestamp
        //      },
        //      ...
        //    ]
      
      case 'order_get':
        
        // silently unset not searchable fields, if any
        $where = self::$data ? form::prepareValues(self::$FIELDS[$method], self::$data) : '';
        
        $result = db::rows('*', '`order`', $where); // `order` needs backticks as 'order' is reserved in MySQL
        
        break;
      
      
        // Adds new orders to 'order' table.
        // Input:
        //    [
        //      {
        //        cableid,
        //        affairid,
        //        tech_id,
        //        count,
        //        [done]: <is set to false if missing>
        //      },
        //      ...
        //    ]
        // Output:
        //    [
        //      <orderid1>: <orderid of the first order added>,
        //      ...
        //    ], or
        //    {
        //      'error' =>  <error message, if at least one order violates self::$FIELDS['order_add'] constraints
        //                  or an order with that (cableid, affairid, tech_id) already exists in the 'order' table>
        //    }
      
      case 'order_add':
        
        db::query('BEGIN');
        
        $orderids = [];
        foreach(self::$data as &$order) {
          if($error = form::checkErrors(self::$FIELDS[$method], $order)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
          
          $values = form::prepareValues(self::$FIELDS[$method], $order);
          
          if(!($orderid = db::insert('`order`', $values, false /* insert */, '', false /* silent */))) {
            if(db::errNo() != db::ER_DUP_ENTRY)
              self::trigger(db::error());   // we don't handle such an error
              
            extract($values);
            $result['error'] = 'A try to add duplicate order: '.json_encode(compact('cableid', 'affairid', 'tech_id'));
            break;
          }
              
          $orderids[] = $orderid;
        }
        
        if(isset($result['error']))
          break;
        
        db::query('COMMIT');
        $result = &$orderids;
        
        break;
      
      
        // Updates orders in 'order' table.
        // Input:
        //    [
        //      {
        //        orderid,
        //        [cableid],
        //        [affairid],
        //        [tech_id],
        //        [count],
        //        [done]
        //      },
        //      ...
        //    ]
        // Output:
        //    nothing if succeeded, or
        //    {
        //      'error' => <error message, if at least one order violates self::$FIELDS['order_update'] constraints
        //                 or an order with that (cableid, affairid, tech_id) already exists in the 'order' table>
        //    }
      
      case 'order_update':
        
        db::query('BEGIN');        
        
        foreach(self::$data as &$order) {
          if($error = form::checkErrors(self::$FIELDS[$method], $order)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
          
          $values = form::prepareValues(self::$FIELDS[$method], $order);
          
          if(!db::update('`order`', $values, $values['orderid'], false /* silent */)) {
            if(db::errNo() != db::ER_DUP_ENTRY)
              self::trigger(db::error());   // we don't handle such an error
              
            extract($values);
            $result['error'] = 'A try to update into duplicate order: '.
                                json_encode(@compact('cableid', 'affairid', 'tech_id'));
            break;
          }
        }
        
        if(isset($result['error']))
          break;
        
        db::query('COMMIT');
        
        break;
      
      
        // Deletes orders from 'order' table.
        // Input:
        //    [
        //      <orderid1>: <id of the first order to delete>,
        //      ...
        //    ]
        // Output:
        //    nothing, or
        //    {
        //      error  
        //    } in case of missing or invalid order id
        
      case 'order_delete':
        
        if(!self::$data) {
          $result['error'] = 'No order ids specified';
          break;
        }
        
        db::query('BEGIN');
        
        foreach(self::$data as &$orderid) {
          $values = compact('orderid'); // define $values to be able to pass it by reference to checkErrors()
          if($error = form::checkErrors(self::$FIELDS[$method], $values)) {
            $result['error'] = current($error);
            $result['field'] = current(array_keys($error));
            break;
          }
        
          db::delete('`order`', $orderid);
        }
        
        if(isset($result['error']))
          break;
        
        db::query('COMMIT');
            
        break;
      
      
      default:
      
        $result['error'] = $method != '' ? "Not supported method '$method'" : "Not specified method";
    }
    
    if(is_array($result)) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($result);
    }
  }
}

api::$FIELDS = [

  'cable_add' =>
  [
    'name'      =>  RSTR(25),
    'type'      =>  ENUM('electrical', 'speaker', 'microphone'),
    'total'     =>  UINT,
    'reserved'  =>  UINT,
    'info'      =>  STR(255),
    'link'      =>  URL
  ],
    
  'cable_update' =>
  [
    'cableid'   =>  RID,
    'name'      =>  STR(25),
    'type'      =>  ENUM('electrical', 'speaker', 'microphone'),
    'total'     =>  UINT,
    'reserved'  =>  UINT,
    'info'      =>  STR(255),
    'link'      =>  URL
  ],
  
  'cable_delete' =>
  [
    'cableid'   =>  RID
  ],
  
        // searchable fields for WHERE CLAUSE, others than those are silently ignored; we do not check for data types so
        // 'ANY' is used for all; master_note', 'tech_note' and 'timestamp' are excluded as there is no sense to search
        // them for equal values - we will include them back if support fulltext and range searches, if any
  
  'affair_get' =>
  [
    'affairid'      =>  ANY,
    'tech_id'       =>  ANY,
    'tech_name'     =>  ANY,
    'name'          =>  ANY,
    'ref'           =>  ANY,
    'prep_date'     =>  ANY,
    'prep_time'     =>  ANY,
    'receipt_date'  =>  ANY,
    'receipt_time'  =>  ANY,
    'return_date'   =>  ANY,
    'return_time'   =>  ANY,
    'front'         =>  ANY,
    'monitor'       =>  ANY,
    'stage'         =>  ANY
  ],
  
  'affair_add' =>
  [
    'tech_id'       =>  RID,
    'tech_name'     =>  RSTR(50),
    'name'          =>  RSTR(50),
    'ref'           =>  STR(50),
    'prep_date'     =>  DATE,
    'prep_time'     =>  ENUM('morning', 'afternoon'),
    'receipt_date'  =>  RDATE,
    'receipt_time'  =>  ENUM('morning', 'afternoon'),
    'return_date'   =>  RDATE,
    'return_time'   =>  ENUM('morning', 'afternoon'),
    'front'         =>  BOOL,
    'monitor'       =>  BOOL,
    'stage'         =>  BOOL,
    'master_note'   =>  ANY,
    'tech_note'     =>  ANY
  ],
  
  'affair_update' =>
  [
    'affairid'      =>  RID,
    'tech_id'       =>  ID,
    'tech_name'     =>  STR(50),
    'name'          =>  STR(50),
    'ref'           =>  STR(50),
    'prep_date'     =>  DATE,
    'prep_time'     =>  ENUM('morning', 'afternoon'),
    'receipt_date'  =>  DATE,
    'receipt_time'  =>  ENUM('morning', 'afternoon'),
    'return_date'   =>  DATE,
    'return_time'   =>  ENUM('morning', 'afternoon'),
    'front'         =>  BOOL,
    'monitor'       =>  BOOL,
    'stage'         =>  BOOL,
    'master_note'   =>  ANY,
    'tech_note'     =>  ANY
  ],
  
  'affair_delete' =>
  [
    'affairid'      =>  RID
  ],
  
        // searchable fields for WHERE clause, others fields are silently igonored; we do not check for data type, so
        // ANY for all
  
  'order_get' =>
  [
    'orderid'       =>  ANY,
    'cableid'       =>  ANY,
    'affairid'      =>  ANY,
    'tech_id'       =>  ANY,
    'count'         =>  ANY,
    'done'          =>  ANY
  ],
  
  'order_add' =>
  [
    'cableid'       =>  RID,
    'affairid'      =>  RID,
    'tech_id'       =>  RID,
    'count'         =>  RID,  // RID not RUINT to prevent 0
    'done'          =>  BOOL
  ],
  
  'order_update' =>
  [
    'orderid'       =>  RID,
    'cableid'       =>  ID,
    'affairid'      =>  ID,
    'tech_id'       =>  ID,
    'count'         =>  ID,  // ID not UINT to prevent 0
    'done'          =>  BOOL
  ],
  
  'order_delete' =>
  [
    'orderid'       =>  RID
  ]
];

new api();

if(!defined('API_DONT_CALL_MAIN'))
  api::main();
?>