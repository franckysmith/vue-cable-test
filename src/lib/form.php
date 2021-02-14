<?
// File:       form.php
// Contents:   HTML-form fields error checking functionality
// Created:    25.08.2015
// Programmer: Edward A. Shiryaev

require_once 'util.php';
require_once 'url.php'; // for url::qualifyUrl()
require_once 'tea.php';

class form {
  
        // Error messages by error keys issued by self::checkErrors(), see below.
  
  public static $ERRORS = array
  (
    'no_value'      =>  "The value is required",
    'empty_value'   =>  "The value is empty",
    'bad_bool'      =>  "The value is not a boolean",
    'bad_int'       =>  "The value is not an integer",
    'bad_number'    =>  "The value is not a number",
    'bad_date'      =>  "The value is not a date",
    'bad_email'     =>  "The value is not an email",
    'bad_url'       =>  "The value is not an internet address",
    'bad_value'     =>  "The value is illegal",
    'too_short'     =>  "The value is too short",
    'too_long'      =>  "The value is too long",
    'too_small'     =>  "The value is too small",
    'too_big'       =>  "The value is too big",
    'not_confirmed' =>  "The value is not confirmed"
  );
  
        // Checks specified field values for error(s) according to field descriptors. If for a given field, there is no
        // value but the default value exists in field descriptor, that value is checked.
        // Parameters:
        //    $fields - a-array of field descriptors by field names as follows:
        //    array
        //    (
        //      <fieldname> =>
        //      array
        //      (
        //        ['type']        =>  <'number', 'int', 'bool', 'date' (MySQL date), 'url' or 'email'; may issue
        //                            'bad_bool', 'bad_int', 'bad_number', 'bad_date', 'bad_url', or 'bad_email' errors;
        //                            'array' means the field value is not scalar but 1d i-array or a-array instead,
        //                            may be used in conjunction with 'subfields', 'subfield' and 'pack' attributes, see
        //                            below>,
        //        ['required']    =>  <non-empty value required; may issue 'no_value' error>,
        //        ['nonempty']    =>  <value is not required but if present must be nonempty; may issue 'empty_value'>,
        //        ['dropempty']   =>  <empty value, if any, is silently excluded from $values (for prepareValues() only,
        //                            see below)>,
        //        ['checkonly']   =>  <checkable by checkErrors() then silently excluded from $values by prepareValues();
        //                            introduced for fields that do not need to be written to the db such as the captcha
        //                            field for which 'encrypted' and 'equalfield' should also be specified, see below>,
        //        ['minlength']   =>  <minimum length allowed; may issue 'too_short' error>,
        //        ['maxlength']   =>  <maximum length allowed; may issue 'too_long' error>,
        //        ['minvalue']    =>  <minimum value allowed ('number', 'date'); may issue 'too_small' error>,
        //        ['maxvalue']    =>  <maximum value allowed ('number', 'date'); may issue 'too_big' error>,
        //        ['range']       =>  <range ('number', 'date') in one of these forms: '1..100', '1..' or '..100'; if
        //                            specified, non-empty $value is set in range in both checkErrors() and
        //                            prepareValues()>,
        //
        //        ['urlprotocol'] =>  <URL protocol such as 'http://' (nonempty 'url' only); passes $value through
        //                            url::qualifyUrl($value, 'urlprotocol') in both checkErrors() and prepareValues()>,
        //
        //        ['equalfield']  =>  <equal value field; may issue 'not_confirmed' error>,
        //        ['encrypted']   =>  <if used without 'equalfield', means that this field value is tea-encrypted;
        //                            non-empty value is checked for decryption in checkErrors() and resulting integer
        //                            value may further be checked for errors such as 'minvalue', 'maxvalue' or 'regex'
        //                            - no need to specify type 'number' for this; type 'number' is still needed to use
        //                            with 'range' attribute, then the value is decrypted in prepareValues();
        //                            'equalfield' attribute changes the meaning of the encrypted field, saying that
        //                            'equalfield' is encrypted instead of this field; may be used as captcha>,
        //        ['regex']       =>  <regex to match against; may issue 'bad_value' error>,
        //        ['errors']      =>  <custom error messages by error keys for given field>,
        //        ['value']       =>  <the default value for the field; a value from $values, if any, overrides it>,
        //
        //    
        //        ['subfields']   =>  <('array' only) i-array or a-array of field descriptors per each subfield of
        //                            the given 'array' field, assumes respective values are keyed by the same keys>,
        //        ['subfield']    =>  <('array' only) alternative to 'subfields' attribute and is used if no keys of the
        //                            respective values array (subvalues) is known; attributes of 'subfield' are applied
        //                            to all the subvalues; if the array field has neither 'subfields' nor 'subfield' no
        //                            error checking and preparing values is made>,
        //        ['pack']        =>  <('array' only) if specified, no json-encoding is made in self::prepareValues()>
        //      ),
        //      ...
        //    )
        //    $values - a-array of field values by field names; may keep field names not presented in $fields such as
        //              equal fields values (confirm fields)
        //    $all    - whether to return all errors or just the first one; false by default
        // Returns:
        //    array
        //    (
        //      <fieldname> =>  <error message>,
        //      ...
        //    ), or an empty array in case of no errors
        // Note:
        //    Empty field descriptors may be freely passed to the function as they are ignored while checking for errors.
        //    (Empty field descriptors are used to allow respective fields in values while intersecting values with
        //    field descriptors, see form::prepareValues() below.)
  
  public static function checkErrors(&$fields, &$values, $all = false)
  {
    $errors = array();
    
    foreach($fields as $name => $field) {
      
      //---- process scalar field ----
      
      if(@$field['type'] != 'array') {
        if($errorKey = self::checkError_($name, $fields, $values)) {
          ($errors[$name] = (string)@$field['errors'][$errorKey]) || ($errors[$name] = self::$ERRORS[$errorKey]);
          if(!$all)
            break;
        }
        continue;
      }
      
      //---- process 'array' field ----
      
      if(!($subfields = @$field['subfields'])) {
        if(!($subfield = @$field['subfield']))
          continue; // do not check for errors if neither $subfields nor $subfield is defined
        
        if(!isset($values[$name]))  
          continue; // $subfield assumes $values[$name] unlike $subfields
        
        $subfields = array_fill_keys(array_keys($values[$name]), $subfield);
      }
      
      // process subfields
      ($subvalues = @$values[$name]) || ($subvalues = []);
      foreach($subfields as $subname => $subfield) {
        if($errorKey = self::checkError_($subname, $subfields, $subvalues)) {
          $name2 = "{$name}[{$subname}]";
          ($errors[$name2] = (string)@$field['errors'][$errorKey]) || ($errors[$name2] = self::$ERRORS[$errorKey]);
          if(!$all)
            return $errors;
        }
      }
    }
    
    return $errors;
  }
  
        // Prepares field values for storage. Does these things:
        //    * adds default values from field descriptors, if any, into $values, if they are absent in $values
        //    * trims $values
        //    * unsets empty values in $values for fields that have 'dropempty' attribute set
        //    * unsets values in $values for fields that are not listed in $fields
        //    * qualifies nonempty 'url' value with url::qualifyUrl() if 'urlprotocol' attribute is set
        //    * sets in range numbers or dates if 'range' is specified
        //    * pack subfields values into a single json-encoded values
        // Parameters:
        //    $fields - [in] field descriptors, see self::checkErrors() above
        //    $values - [in] field values, see self::checkErrors() above
        // Returns:
        //    The resulting array of values.
  
  public static function prepareValues(&$fields, &$values)
  {
    $values2 = array_intersect_key($values, $fields);
    
    foreach($fields as $name => $field) {
      
      //---- process scalar field ----
      
      if(@$field['type'] != 'array') {
      
        if(isset($field['value']) && !isset($values2[$name]))
          $values2[$name] = $field['value'];
          
        if(!isset($values2[$name]))
          continue;
        
        $values2[$name] = trim($values2[$name]);
        
        if(@$field['dropempty'] && $values2[$name] == '' || @$field['checkonly']) {
          unset($values2[$name]);
          continue;
        }
        
        if(@$field['encrypted'] && !@$field['equalfield'] && $values2[$name] != '')
          $values2[$name] = tea::decrypt($values2[$name]);
        
        if(@$field['range'] && (@$field['type'] == 'number' || @$field['type'] == 'date') && $values2[$name] != '')
          $values2[$name] = self::setInRange_($values2[$name], $field['range']);
        
        if(@$field['urlprotocol'] && @$field['type'] == 'url' && $values2[$name] != '')
          $values2[$name] = url::qualifyUrl($values2[$name], $field['urlprotocol']);
          
        continue;
      }
      
      //---- process 'array' field ----
      
      if(!($subfields = @$field['subfields']))
        if(($subfield = @$field['subfield']) && isset($values2[$name])) // $subfield assumes $values2[$name]
          $subfields = array_fill_keys(array_keys($values2[$name]), $subfield);  
      
      // process subfields
      if($subfields) {
        ($subvalues = @$values2[$name]) || ($subvalues = []);
        $values2[$name] = self::prepareValues($subfields, $subvalues);
      }
      // encode non-empty array or else turn it to ''
      if(@$field['pack'] !== false)
        $values2[$name] = @$values2[$name] ? json_encode($values2[$name]) : '';
    }
    
    return $values2;
  }
  
        // Prepares stored field values for output: unpacks subfields json-encoded values into arrays of values.
        // Parameters:
        //    $fields - [in] field descriptors, see self::checkErrors() above
        //    $values - [in, out] field values, see self::checkErrors() above; unlike self::prepareValues(), is used as
        //              the result instead of return value
  
  public static function restoreValues(&$fields, &$values)
  {
    foreach($fields as $name => $field) {
      if(@$field['type'] != 'array')
        continue;
    
      // json_decode(null) or json_decode('') returns NULL, so (array) cast to convert NULL to empty array
      if(@$field['pack'] !== false)
        $values[$name] = (array)json_decode(@$values[$name], true);
    }
  }
  
        // Checks specified field value for error. If for a given field, there is no value but the default value exists
        // in field descriptor, that value is checked. Empty field descriptors are ignored.
        // Parameters:
        //    $name   - name of the field to check
        //    $fields - field descriptors, see self::checkErrors() above
        //    $values - field values, see self::checkErrors() above
        // Returns:
        //    error key (see self::$ERRORS above), or false if no error is found
  
  private static function checkError_($name, &$fields, &$values)
  {
    if(!($field = $fields[$name]))
      return false;
    
    if(isset($values[$name]))
      $value = trim($values[$name]);
    else if(isset($field['value']))
      $value = trim($field['value']);
      
    if(@$field['encrypted'] && !@$field['equalfield'] && (string)@$value != '') {
      if(!($value = tea::decrypt($value)))
        return 'bad_value';
    }
      
    if((@$field['type'] == 'number' || @$field['type'] == 'date') && @$field['range'] && (string)@$value != '')
      $value = self::setInRange_($value, $field['range']);
   
    if(!isset($value))
      $value = '';   
    else if(@$field['nonempty'] && $value == '')
      return 'empty_value';
    
    if(@$field['required'] && $value == '')
      return 'no_value';
    
    if(@$field['type'] == 'bool' && $value != '' && !util::isBool($value))
      return 'bad_bool';
    
    if(@$field['type'] == 'int' && $value != '' && !util::isInt($value))
      return 'bad_int';
    
    if(@$field['type'] == 'number' && $value != '' && !util::isNumber($value, '.,'))
      return 'bad_number';
    
    if(@$field['type'] == 'date' && $value != '' && !util::isDate($value))
      return 'bad_date';
    
    if(@$field['type'] == 'email' && $value != '' && !util::isEmail($value))
      return 'bad_email';
    
    if(@$field['type'] == 'url' && $value != '' && !util::isUrl(@$field['urlprotocol'] ? url::qualifyUrl($value, $field['urlprotocol']) : $value))
      return 'bad_url';
     
    if(@$field['regex'] && $value != '' && !preg_match($field['regex'], $value))
      return 'bad_value';
    
    if(isset($field['minlength']) && $value != '' && mb_strlen($value, 'utf-8') < $field['minlength'])
      return 'too_short';
    
    if(isset($field['maxlength']) && mb_strlen($value, 'utf-8') > $field['maxlength'])
      return 'too_long';
    
    if(isset($field['minvalue']) && $value != '' && $value < $field['minvalue'])
      return 'too_small';
    
    if(isset($field['maxvalue']) && $value != '' && $value > $field['maxvalue'])
      return 'too_big';
    
    /*if(@$field['equalfield'] && $value != trim((string)@$values[$field['equalfield']]))
      return 'not_confirmed';*/
    
    if(@$field['equalfield']) {
      $value2 = trim((string)@$values[$field['equalfield']]);
      if(@$field['encrypted'])
        $value2 = tea::decrypt($value2);
      if($value != $value2)
        return 'not_confirmed';
    }
  
    return false;  
  }
  
        // Sets a number or date value in the given range.
        // Parameters:
        //    $value  - value of either 'number' or 'date' type
        //    $range  - range in one of these forms: '1..100', '1..', or '..100'
        // Returns:
        //    Value in the given range inclusively
  
  private static function setInRange_($value, $range)
  {
    $range = explode('..', $range);
    
    if((string)$range[0] == '')
      $range[0] = -INF;
      
    if((string)$range[1] == '')
      $range[1] = INF;
      
    return min(max($value, $range[0]), $range[1]);  
  }
}
?>