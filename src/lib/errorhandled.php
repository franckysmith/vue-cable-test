<?
// File:       errorhandled.php
// Contents:   basic class for all subclasses that handle E_USER_ERROR and E_RECOVERABLE_ERROR errors
// Created:    11.11.2015
// Programmer: Edward A. Shiryaev

class errorhandled {
  
        // Low-level error handling function. Subclass can set this handler by 2 ways:
        //  * call PHP's set_error_handler() on its own passing the handler as callback in first parameter, such as
        //      set_error_handler('<subclass>::errorHandler', E_USER_ERROR | E_RECOVERABLE_ERROR);
        //    where <subclass> is the name of the subclass, or even in better way:
        //  * just create the subclass instance with operator new - it implicitly calls errorhandled::__construct(), see
        //    below, which sets the handler for you; good news is that there is no need to define the subclass's
        //    constructor itself for this!
        // The handler almost never need to be overriden in subclasses. errorhandled::onError() should be defined
        // instead, see below. The function logs an error, calls static::onError() and exits.
  
  public static function errorHandler($errno, $errmsg, $filename, $lineno)
  {
    // pass not handled errors to the standard handler
    if($errno != E_USER_ERROR && $errno != E_RECOVERABLE_ERROR)
      return false;
    
    $errormsg = sprintf('Error: %s in %s on line %d', $errmsg, $filename, $lineno);
    
    // skip messages caused by statements prepended with @: error_reporting() returns 0 in that case
    if(error_reporting())                 
      if(ini_get('log_errors'))
        error_log($errormsg);
        
    static::onError($errmsg); // original message - not one that is logged
    exit;
  }
  
        // User-level error handling function that needs to be defined in a subclass. No need for the function to exit
        // as the low-level error handler exits the script.
        // Parameters:
        //    $errormsg - error message with indication of the file name and line number.
  
  public static function onError($errormsg)
  {}
  
        // Sets errorHandler(), whether defined here or overriden in subclass, if any.
  
  public function __construct()
  {
    set_error_handler([ get_called_class(), 'errorHandler' ], E_USER_ERROR | E_RECOVERABLE_ERROR);
  }
}
?>