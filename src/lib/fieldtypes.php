<?
// File:       fieldtypes.php
// Contents:   shorthands for commonly used form field descriptors, see form.php
// Created:    06.02.2021
// Programmer: Edward A. Shiryaev

// No-parameters shorthands are implemented as constants, while others requiring parameters are implemented as functions.
// By convention, 'required' versions are prepended with 'R'. Scalar data types are only implemented.

//---- STR ----

        // Untyped data of any length.
        
const ANY   = [];
const RANY  = [ 'required' => true ];

        // Untyped data with a maximum and optional minimum length(s) in any range.
        // Parameters:
        //    $length1    - maximum if no $length2, or else minimum length
        //    [$length2]  - maximum length if specified
        
function STR($length1, $length2 = NULL)
{
  assert($length1 >= 0);
  
  if($length2 === NULL)
    return [ 'maxlength' => $length1 ];
    
  assert($length2 >= $length1);
    
  return [ 'minlength' => $length1, 'maxlength' => $length2 ];
}

function RSTR($length1, $length2 = NULL)
{
  return STR($length1, $length2) + [ 'required' => true ];
}

//---- BOOL ----

const BOOL  = [ 'type' => 'bool' ];
const RBOOL = [ 'type' => 'bool', 'required' => true ];

//---- INT ----

const INT   = [ 'type' => 'int' ]; 
const RINT  = [ 'type' => 'int', 'required' => true ];
  
//---- UINT ----

const UINT  = [ 'type' => 'int', 'minvalue' => 0 ];
const RUINT = [ 'type' => 'int', 'minvalue' => 0, 'required' => true ];

//---- NUMBER ----

        // Numbers with optional decimal point.

const NUMBER  = [ 'type' => 'number' ];
const RNUMBER = [ 'type' => 'number', 'required' => true ];

//---- DATE ----

        // Dates in MySQL format.

const DATE  = [ 'type' => 'date' ];
const RDATE = [ 'type' => 'date', 'required' => true ];

//---- EMAIL ----

        // Email with maximum length 255.

const EMAIL   = [ 'type' => 'email', 'maxlength' => 255 ];
const REMAIL  = [ 'type' => 'email', 'maxlength' => 255, 'required' => true ];

//---- URL ----

        // URL with maximum length 255.

const URL   = [ 'type' => 'url', 'maxlength' => 255 ];
const RURL  = [ 'type' => 'url', 'maxlength' => 255, 'required' => true ];

//---- ID ----

        // Integer database id.
        
const ID  = [ 'type' => 'int', 'minvalue' => 1 ];
const RID = [ 'type' => 'int', 'minvalue' => 1, 'required' => true ];

//---- ENUM ----

      // Takes one or more string arguments of allowed tokens. The function does not assume regex special characters
      // inside the tokens, so if used, they must be escaped beforehand (prepended with '\').

function ENUM()
{
  return [ 'regex' => '/^('.implode('|', func_get_args()).')$/' ];
}

function RENUM()
{
  return [ 'regex' => '/^('.implode('|', func_get_args()).')$/', 'required' => true ];
}

?>