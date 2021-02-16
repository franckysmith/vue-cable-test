<?
// File:        dbbase.php
// Contents:    basic database superclass; application specific database subclasses inherit from it
// Created:     01.11.2013
// Programmer:  Edward A. Shiryaev

        // Helper class for string values that should not be quoted while composing 'SET' or 'WHERE' SQL clauses, see
        // dbbase::set(), dbbase::where() below.

class Literal {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

        // Introduced to avoid to write 'new' in user code when defining unquoted values.

function literal($str)
{
  return new Literal($str);
}

        // Like Literal, see above, but resolves to not quoted literal value without 'key=' part of expression ('WHERE'
        // SQL clause only). Useful for composing bitwise expressions like 'x & 1'.

class Value {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

        // Creates new Value-object.

function value($str)
{
  return new Value($str);
}

        // Helper class for string values that should be resolved to 'LIKE' operator while composing 'WHERE' SQL clauses,
        // see dbbase::where() below.

class Like {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

        // Introduced to avoid to write 'new' in user code when defining unquoted values.

function like($str)
{
  return new Like($str);
}

        // When composing 'WHERE' SQL clause, resolves a string value to 'LIKE('value%')' to search for fields prefixed
        // (starting with) the given value.

class Prefix {
  
  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

        // The shorthand to create new Prefix object.

function prefix($str)
{
  return new Prefix($str);
}

// Comparison classes Gt, Gte, Lt, Lte for string values to be used when composing 'WHERE' clause in dbbase::where.

class Gt {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

function gt($str)
{
  return new Gt($str);
}

class Gte {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

function gte($str)
{
  return new Gte($str);
}

class Lt {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

function lt($str)
{
  return new Lt($str);
}

class Lte {

  private $value;
  
  public function __construct($value)
  {
    $this->value = (string)$value;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}

function lte($str)
{
  return new Lte($str);
}

        // Helper class to return 'OR' expression while composing 'WHERE' SQL clauses, see dbbase::where() below.

class Or_ {

  private $fields;
  
        // $fields  - a-array of <field name>/<field value> pairs, 'Literal', 'Like', 'And', and 'Or' may be applied to
        //            field values. A field value may be either a scalar value or i-arrays of values that is internally
        //            resolved into field elements with fictitious keys with values assigned as follows:
        //              or_([<key> => <value])
        //            where <key> is original key of i-array values, and <value> is elementary value from i-array. The
        //            number of such fictitious keys elements for one <key> is equal to number of values in i-array for
        //            that <key>. The original array element for <key> is removed. Fictitious keys are formed by this
        //            format:
        //              '__field<no>__'
        //            where <no> is sequence number of fictitious field.
        //            Important!!! Original field names have to be different from fictitious field names to be not 
        //            overriden by them.
        //            Resulting fields should be at least 2 elements array to ensure 'OR' operation between them. Single
        //            element array is a singular case though not an error.
  
  public function __construct($fields)
  {
    $count = 1;
    foreach($fields as $key => $values) {
      if(!is_array($values))
        continue;      
      
      foreach($values as $value) {
        $fields["__field{$count}__"] = or_([$key => $value]);
        $count++;
      }
          
      unset($fields[$key]);
    } 
    
    $this->fields = $fields;
  }
  
        // Gets copy of $this->fields.  
  
  public function fields()
  {
    return $this->fields;
  }
  
        // Returns 'OR' expression.  
  
  public function __toString()
  {
    return dbbase::where($this->fields, '', 'OR');
  }
}

        // Introduced to avoid to write 'new' in user code when defining Or-expression.

function or_($fields)
{
  return new Or_($fields);
}

        // Helper class to return 'AND' expression while composing 'WHERE' SQL clauses, see dbbase::where() below.

class And_ {

  private $fields;
  
        // $fields - a-array of <field name>/<field value> pairs, 'Literal', 'Like', 'And', and 'Or' may be applied to
        // field values. The fields should be at least 2 elements array to ensure 'OR' operation between them. Single
        // element array is a singular case though not an error.
  
  public function __construct($fields)
  {
    $this->fields = $fields;
  }
  
        // Gets copy of $this->fields.  
  
  public function fields()
  {
    return $this->fields;
  }
  
        // Returns 'AND' expression.  
  
  public function __toString()
  {
    return dbbase::where($this->fields);
  }
}

        // Introduced to avoid to write 'new' in user code when defining Or-expression.

function and_($fields)
{
  return new And_($fields);
}


class dbbase {
  
  //---- MySQL specific error codes we handle --------------------------------------------------------------------------
  
        // Inserting/updating a row that violates a unique index constraint.
  
  const ER_DUP_ENTRY = 1062;
  
        // Deleting/updating a parent row violates a foreign key constraint in a child row.
  
  const ER_ROW_IS_REFERENCED = 1451;
	
	//---- database specific information ---------------------------------------------------------------------------------
  
        // Primary key fields by table names. Single field keys are assumed. Primary fields have to be listed here only
        // if their names don't obey the default rule:
        //    <field name> == <table name>."id" e.g. "userid" = "user"."id"
        // The array is used by dbbase::where(), see below, to resolve integer value, if any, into primary field value.
  
  protected static $PRIMARY_FIELDS = array();
  
  /*protected static function &primaryFields()
  {
    if(function_exists('get_called_class'))   // means PHP version >= 5.3
      return static::$PRIMARY_FIELDS;
    
    return db::$PRIMARY_FIELDS;
  }*/
  
        // Connection link, initialized in constructor.
  
	protected static $link;
	
	//---- helper functions ----------------------------------------------------------------------------------------------
	
          // Adds backslashes before quotes, double quotes and backslashes into a string or an array. The function
          // appropriately processes nested arrays.
					// Parameters:
					//		$data - either a string or an array, possibly nested.
  
	public static function &addslashes(&$data)
	{
		if(is_array($data))	{
			foreach($data as &$item)
			  self::addslashes($item);
		}
    else if(is_string($data))
			$data = addslashes($data);
    else if($data instanceof Like)
      $data = like(addslashes((string)$data));
    else if($data instanceof And_) {
      $fields = $data->fields();
      $data = and_(self::addslashes($fields));
    }
    else if($data instanceof Or_) {
      $fields = $data->fields();
      $data = or_(self::addslashes($fields));
    }
			
		return $data;
	}
	
          // Strips backslashes in a string or an array. The function appropriately processes nested arrays.
					// Parameters:
					//		$data - either a string or an array, possibly nested.
	
	public static function &stripslashes(&$data)
	{
		if(is_array($data)) {	
			foreach($data as &$item)
			  self::stripslashes($item);
		}
    else if(is_string($data))
			$data = stripslashes($data);
			
		return $data;
	}
  
          // Returns how many rows are affected by UPDATE, INSERT or DELETE. Unlike the
          // standard mysql_affected_rows():
          //  * call to affectedRows() resets the function (subsequent calls return -1)
          //  * any SELECT also resets the function
  
  public static function affectedRows()
  {
    $result = mysqli_query(self::$link, 'select ROW_COUNT()') or trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    $row = mysqli_fetch_assoc($result);
    return $row['ROW_COUNT()'];
  }
  
          // Returns error code for the most recent SQL query call.   
  
  public static function errNo()
  {
    return mysqli_errno(self::$link);
  }
  
          // Returns error message for the most recent SQL query call.
  
  public static function error()
  {
    return mysqli_error(self::$link);
  }
  
        // Returns id of the auto incremented id field of last 'insert' operation.
  
  public static function insertId()
  {
    return mysqli_insert_id(self::$link);
  }
  
        // Helper function that returns SET-clause for an SQL query by the specified array of fields with values.
        // Parameters:
        //    $fields - non-empty a-array of column name/value pairs; empty values become MySQL null values
  
  public static function set($fields)
  {
    if(!$fields || !is_array($fields))
      trigger_error('db::set(): $fields is empty or not an array', E_USER_ERROR);
    
    self::addslashes($fields);
    
    foreach($fields as $key => $value) {
			if($value === '' || $value === NULL)
				$tmp[] = "$key = NULL";
      else if(preg_match('/^`(.+)`$/', $value, $matches))
        $tmp[] = "$key = {$matches[1]}";
			else if($value instanceof Literal)
        $tmp[] = "$key = $value";
      else
	      $tmp[] = "$key = '$value'";
		}
		
    return implode(',', $tmp);
  }
  
        // Helper function that returns AND-WHERE-clause, or OR-WHERE-clause for an SQL query.
        // Parameters:
        //    $fields   - one of the following:
        //                * non-empty a-array of column name/value pairs; empty values become MySQL null values; values
        //                  that are instances of Literal object, see above, are inserted literally rather than enclosed
        //                  into quotes
        //                * non-empty string value not convertible into nonzero integer value is interpreted as already
        //                  made where clause and returned as is
        //                * value convertible into nonzero integer value is interpreted as primary field value for the
        //                  first table in comma-delimited $tables (processed only if $tables is not empty)
        //    [$tables] - comma-delimited table names to define the first table for the primary key, see $fields above
        //    [$op]     - 'AND' or 'OR' operation; 'AND' by default
  
  public static function where($fields, $tables = '', $op = 'AND')
  {
    if(!$fields)
      trigger_error('db::where(): $fields is empty!', E_USER_ERROR);
      
    if(!is_array($fields)) {
      
      if((int)$fields && $tables) {
        $value = (int)$fields;
        $tmp = explode(',', $tables);
        $table = $tmp[0];
        
        // Important!!! For PHP5.2- uncomment PHP5.2- line and comment PHP5.3+
        // $primaryFields = db::$PRIMARY_FIELDS;      // PHP5.2-
        $primaryFields = static::$PRIMARY_FIELDS;     // PHP5.3+
        
        $key = isset($primaryFields[$table]) ? $primaryFields[$table] : $table.'id';
        return "$table.$key=$value";
      }
      
      return $fields;
    }
    
    self::addslashes($fields);
    
    foreach($fields as $key => $value) {
      if($value === '' || $value === NULL)
        $tmp[] = "$key IS NULL";
      else if($value === true)
        $tmp[] = "$key IS TRUE";
      else if($value === false)
        $tmp[] = "$key IS FALSE";
      else if(preg_match('/^`(.+)`$/', $value, $matches))
        $tmp[] = "$key = {$matches[1]}";
			else if($value instanceof Literal)
        $tmp[] = "$key = $value";
			else if($value instanceof Value)
        $tmp[] = "$value";
			else if($value instanceof Like)
        $tmp[] = "$key LIKE '%$value%'";
			else if($value instanceof Prefix)
        $tmp[] = "$key LIKE '$value%'";
      else if($value instanceof Or_)
        $tmp[] = "($value)";
      else if($value instanceof And_)
        $tmp[] = "($value)";
      else if($value instanceof Gt)
        $tmp[] = "$key > '$value'";
      else if($value instanceof Gte)
        $tmp[] = "$key >= '$value'";
      else if($value instanceof Lt)
        $tmp[] = "$key < '$value'";
      else if($value instanceof Lte)
        $tmp[] = "$key <= '$value'";
      else
        $tmp[] = "$key = '$value'";
    }			
    return implode(" $op ", $tmp);  
  }

	//--------------------------------------------------------------------------------------------------------------------
    
        // Connects to the database.
        // Parameters:
        //    $params  - array
        //    (
        //        'db_server'     =>  <db host>,
        //        'db_username'   =>  <db user name>,
        //        'db_password'   =>  <db user password>,
        //        'db_name'       =>  <db name>,
        //    )

  protected function __construct($params)
  {
    self::$link =
    mysqli_connect($params['db_server'], $params['db_username'], $params['db_password'], $params['db_name']) or
      trigger_error(mysqli_connect_error(), E_USER_ERROR);
      
    mysqli_set_charset(self::$link, 'utf8') or
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
      
    // default values:
    // cinod MySQL: STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION (MariaDB from 10.2.4)
    // local MySQL:
    // sql_mode possible values see here: https://mariadb.com/kb/en/library/sql-mode/
    mysqli_query(self::$link, 'SET sql_mode=NO_ENGINE_SUBSTITUTION') or  
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    
    // default values: cinod MySQL: 1048576, local MySQL: 1024
    mysqli_query(self::$link, 'SET group_concat_max_len=1048576') or  
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
      
    // default values: cinod MySQL: 16777216, local MySQL: 16777216
    /*mysqli_query(self::$link, 'SET GLOBAL max_allowed_packet=16777216') or  // ensure big images
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);*/
  }
  
          // General purpose query execution function. Should be used with queries that cannot be otherwise run by
          // specific query functions such as select(), update(), etc.. These may be:
          //    * complex joins, or
          //    * queries that are not 'UPDATE', 'INSERT' or 'DELETE'
          // Parameters:
          //    $query        -  SQL-query as a string
          //    $triggerError - if true, in case of an error, the error is triggered, otherwise no action is done
          // Returns:
          //    Boolean true for 'UPDATE', 'INSERT' or 'DELETE', or resulting data rows as i-array of a-arrays for
          //    'SELECT', 'SHOW', 'DESCRIBE' or 'EXPLAIN'. In case of an error, the function triggers the error.
  
  public static function query($query, $triggerError = true)
  {
    $result = mysqli_query(self::$link, $query) or !$triggerError or trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    
    if(is_bool($result))
      return $result;
    
    $rows = array();
    while($row = mysqli_fetch_assoc($result))
      $rows[] = $row;
    mysqli_free_result($result);

    return $rows;
  }
  
          // Selects specified fields values from one or multiple tables.
          // Parameters:
          //    $fields  	  - i-array with filed names to select values for, or a string with one or multiple comma-
          //                  delimited field names; use '*' to specify all fields; can not be empty
					//		$tables			- i-array with one or more table names, or a string with one or multiple comma-delimited
          //                  table names; can not be empty
          //    $where    	- optional 'where' condition as follows:
          //                  * non-empty a-array of column name/value pairs; empty values become MySQL null values;
          //                    values that are instances of Literal object, see above, are inserted literally rather
          //                    than enclosed into quotes
          //                  * non-empty string value not convertible into nonzero integer value is interpreted as
          //                    already made where clause
          //                  * value convertible into nonzero integer value is interpreted as primary field value for
          //                    the first table in $tables
					// 		$groupby 		- optional 'group by' condition					
    			// 		$orderby 		- optional 'order by' condition
          //    $lockMode   - optional MySQL row lock mode inside a transaction, either 'FOR UPDATE' or
          //                  'LOCK IN SHARE MODE'
          // Returns:
          //    I-array of a-arrays whose keys are from $fields and values are the respective values retrieved.
  
  public static function select($fields, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
		if(!$fields)
			trigger_error('dbbase::select(): empty $fields', E_USER_ERROR);
		if(is_array($fields))
      $fields = implode(',', $fields);
		
		if(!$tables)
			trigger_error('dbbase::select(): empty $tables', E_USER_ERROR);
		if(is_array($tables))
			$tables = implode(',', $tables);
    
    $query = "SELECT $fields FROM $tables";
    
    if($where) 
      $query .= ' WHERE '.self::where($where, $tables);
    if($groupby)
      $query .= " GROUP BY $groupby";
    if($orderby)
      $query .= " ORDER BY $orderby";
    if($lockMode)
      $query .= " $lockMode";
      
    return self::query($query);
  }
  
        // The same as previous, but returns just the first row of the resulting data, or false if not found. Should be
        // used if only one row is assumed in the result.
  
  public static function selectRow($fields, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    $rows = self::select($fields, $tables, $where, $groupby, $orderby, $lockMode);
    return $rows ? $rows[0] : false;
  }
  
        // The same as previous, but returns just one field from the first row of the resulting data, or false if not
        // found. Should be used if only one row is assumed in the result.
        // Parameters:
        //    $field  - single field name to return value for (instead of fields)
        //    other parameters are identical
  
  public static function selectField($field, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    $rows = self::select($field, $tables, $where, $groupby, $orderby, $lockMode);
    
    // remove table prefix from the field if any e.g. <table>.<field> -> <field>
    /*$tmp = explode('.', $field);
    $field = $tmp[count($tmp) - 1];*/
    
    //return $rows ? $rows[0][$field] : false;
    
    return $rows ? current($rows[0]) : false;
  }
  
        // Returns one field from all rows as i-array of values, or an empty array if no rows found. Parameters are the
        // same as in self::selectfield(), see above.
  
  public static function selectFields($field, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    $values = array();
    $rows = self::select($field, $tables, $where, $groupby, $orderby, $lockMode);
    foreach($rows as $row)
      $values[] = current($row);
    
    return $values;
  }
  
  //---- shorthand aliases to select-functions ----
  
  public static function rows($fields, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    return self::select($fields, $tables, $where, $groupby, $orderby, $lockMode);
  }
  
  public static function row($fields, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    return self::selectRow($fields, $tables, $where, $groupby, $orderby, $lockMode);
  }
  
  public static function field($field, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    return self::selectField($field, $tables, $where, $groupby, $orderby, $lockMode);
  }
  
  public static function fields($field, $tables, $where = '', $groupby = '', $orderby = '', $lockMode = '')
  {
    return self::selectFields($field, $tables, $where, $groupby, $orderby, $lockMode);
  }

  //----------------------------------------------
  
        // Finds a row in a single or joined tables.
        // Parameters:
        //    $tables   - a single table or comma-delimited tables
        //    $where    - 'where' condition as follows (empty to check if the table is not empty):
        //                 * non-empty a-array of column name/value pairs; empty values become MySQL null values;
        //                   values that are instances of Literal object, see above, are inserted literally rather
        //                   than enclosed into quotes
        //                 * non-empty string value not convertible into nonzero integer value is interpreted as
        //                   already made where clause
        //                 * value convertible into nonzero integer value is interpreted as primary field value for
        //                   the specified $table
        //    $lockMode - optional MySQL row lock mode inside a transaction, either 'FOR UPDATE' or 'LOCK IN SHARE MODE'
        // Returns:
        //    true if found, false otherwise
  
  public static function find($tables, $where, $lockMode = '')
  {
		if(!$tables)
			trigger_error('dbbase::find(): empty $tables', E_USER_ERROR);
    
    $query = "SELECT COUNT(*) FROM $tables";
    if($where) 
      $query .= ' WHERE '.self::where($where, $tables);
    if($lockMode)
      $query .= " $lockMode";
      
    $result = mysqli_query(self::$link, $query) or trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    
    return $row ? (bool)$row[0] : false;
  }
  
        // Inserts a record into a table.
        // Parameters:
				//		$table	        -	non-empty table name
        //    $row 		        - non-empty a-array whose keys are column names, and values are to be set to these columns;
        //                      empty string values become MySQL null values
				//		[$update]       - if true, then if duplicate values are not allowed by primary key or unique index, an old
        //                      row is updated
        //    [$idField]      - auto_increment record id field; have to be specified to make the function to return the
        //                      id of the inserted record if $update == true; if $update == false the functions returns
        //                      the inserted record id whether $idField specified or not
        //    [$triggerError] - if true, in case of an error, the error is triggered rather than false is returned
        // Returns:
        //    id of the auto incremented id field on success, or false on failure (if $triggerError is false); if the
        //    table does not have auto incremented id, 0 is returned.

  public static function insert($table, $row, $update = false, $idField = '', $triggerError = true)
  {
		if(!$table)
			trigger_error('insert(): empty $table', E_USER_ERROR);
      
		$set = self::set($row);   
    $query = "INSERT INTO $table SET $set";
		
		if($update) {
			$query .= " ON DUPLICATE KEY UPDATE $set";
      
      if($idField)    
        $query .= ", $idField = LAST_INSERT_ID($idField)";
		}
    
    if(!($done = mysqli_query(self::$link, $query)) && $triggerError)
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    
    return $done ? mysqli_insert_id(self::$link) : false;
  }
  
        // Updates record(s) in a table.
        // Parameters:
				//		$table	      -	non-empty table name 
        //    $row          - non-empty associative array whose keys are column names, and values are new values to set 
        //                    to these columns; empty string values become MySQL null values
        //    $where    	  - optional 'where' condition as follows:
        //                    * non-empty a-array of column name/value pairs; empty values become MySQL null values;
        //                      values that are instances of Literal object, see above, are inserted literally rather
        //                      than enclosed into quotes
        //                    * non-empty string value not convertible into nonzero integer value is interpreted as
        //                      already made where clause
        //                    * value convertible into nonzero integer value is interpreted as primary field value for
        //                      the specified $table
        //    $triggerError - if true, in case of an error, the error is triggered rather than false is returned        

  public static function update($table, $row, $where = '', $triggerError = true)
  {
		if(!$table)
			trigger_error('update(): empty $table', E_USER_ERROR);
      
		$set = self::set($row);
    $query = "UPDATE $table SET $set";
    if($where) 
      $query .= ' WHERE '.self::where($where, $table);
      
    if(!($done = mysqli_query(self::$link, $query)) && $triggerError)
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    
    return $done;
  }
  
          // Deletes record(s) from a table.
          // Parameters:
					//		$table	      -	non-empty table name 
          //    $where    	  - optional 'where' condition as follows:
          //                    * non-empty a-array of column name/value pairs; empty values become MySQL null values;
          //                      values that are instances of Literal object, see above, are inserted literally rather
          //                      than enclosed into quotes
          //                    * non-empty string value not convertible into nonzero integer value is interpreted as
          //                      already made where clause
          //                    * value convertible into nonzero integer value is interpreted as primary field value for
          //                      the specified $table
          //    $triggerError - if true, in case of an error, the error is triggered, otherwise no action is done
  
  public static function delete($table, $where = '', $triggerError = true)
  {
		if(!$table)
			trigger_error('delete(): empty $table', E_USER_ERROR);
		
    $query = "DELETE FROM $table";
    if($where) 
      $query .= ' WHERE '.self::where($where, $table);

    if(!($done = mysqli_query(self::$link, $query)) && $triggerError)
      trigger_error(mysqli_error(self::$link), E_USER_ERROR);
    
    return $done;
  }
}
?>