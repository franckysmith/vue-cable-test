<?
// File:       assert.php
// Contents:   enables/disables standard PHP assert() function in including script
// Created:    14.05.2017
// Programmer: Edward A. Shiryaev

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// To enable assert(), the including script must define ASSERT_ENABLED before the inclusion of this script.
// To disable assert(), the including script must just include this script assuming ASSERT_ENABLED is not defined
// elsewhere.
//
// By default, assert messages are HTML-formatted. To have them in plain text, ASSERT_HTML must be defined as false
// before inclusion of this script - unless ASSERT_TRIGGER_ERROR is defined true, see respective comments in code.
//
// By default, i.e. when this script is not included, assert() is enabled by PHP issuing just warnings on every assert()
// failure. This script overrides issuing warnings to errors with script termination on any assert() failure. Also, the
// assert() failure message is customized, in particular, to not display word 'Warning' in the message by default.
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

assert_options(ASSERT_ACTIVE, false);     // disable assert() by default

if(defined('ASSERT_ENABLED')) {
  assert_options(ASSERT_ACTIVE,   true);
  assert_options(ASSERT_BAIL,     true);  // causes to terminate the script in case of assert() failure
  assert_options(ASSERT_WARNING,  false); // prevents default warning message to be issued
  
        // If ASSERT_TRIGGER_ERROR is defined true in higher code:
        //    * E_USER_ERROR error is triggered with an assert message instead of outputting it to the screen
        //    * ASSERT_HTML is automatically set to false to better prepare error messages for logging
        // So:
        //    * ASSERT_ENABLED true alone is good for debugging
        //    * ASSERT_ENABLED true along with ASSERT_TRIGGER_ERROR true is good for release - provided that original
        //      error messages are intercepted and logged while general error messages are presented to the end user,
        //      see errorhandled.php for details. 
  
  if(!defined('ASSERT_TRIGGER_ERROR'))
    define('ASSERT_TRIGGER_ERROR', false);  // by default
  
  if(ASSERT_TRIGGER_ERROR)
    define('ASSERT_HTML', false); // HTML is bad for logging
  
  if(!defined('ASSERT_HTML'))
    define('ASSERT_HTML', true);  // by default
  
  if(ASSERT_HTML) {
    
          // Sets the callback to echo custom assert failure message.
          // Parameters:
          //    $file     - script file name in which assert() failed
          //    $line     - line number on which assert() failed
          //    [$expr]   - PHP expression that was passed to assert() as string, empty (not specified) if expression is
          //                passed as boolean value
          //    [$descr]  - user description of assert() failure, if any, passed to assert() as second parameter
          // Note:
          //    text/html content type of the page is assumed
    
    assert_options(ASSERT_CALLBACK, function ($file, $line, $expr, $descr = '') {
      
      if(ASSERT_TRIGGER_ERROR)
        ob_start();      
  
      echo "<br>Assertion failed in <b>$file</b> on line <b>$line</b>";
      
      if($expr)
        echo ": <span style='background:yellow'>$expr</span>";
      
      if($descr)
        echo ": <span style='background:yellow'>$descr</span>";
        
      if(ASSERT_TRIGGER_ERROR) {  
        $message = ob_get_contents();
        ob_end_clean();
        trigger_error($message, E_USER_ERROR);
      }
  
    });
    
  } else {
    
          // Plain text version of the callback, see above.    
    
    assert_options(ASSERT_CALLBACK, function ($file, $line, $expr, $descr = '') {
      
      if(ASSERT_TRIGGER_ERROR)
        ob_start();      
  
      echo "\nAssertion failed in $file on line $line";
      
      if($expr)
        echo ": $expr";
      
      if($descr)
        echo ": $descr";
        
      if(ASSERT_TRIGGER_ERROR) {  
        $message = ob_get_contents();
        ob_end_clean();
        trigger_error($message, E_USER_ERROR);
      }
    });
  }
}
?>