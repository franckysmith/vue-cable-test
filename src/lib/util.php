<?
// File:       util.php
// Contents:   common usage utility functions
// Created:    22.11.2009
// Programmer: Edward A. Shiryaev

class util {
  
        // Calls the specified URL in the background. Asynchronous call is achieved by
        // immediate closing a connection.
        // Parameters:
        //    $url  - URL to the script, can contain GET-parameters that are passed as POST-
        //            parameters to the script.
        // Returns:
        //    true if succeeded or false otherwise.
        // Remark:
        //    The technique does not depend on the OS of the server and the path to PHP
        //    interpreter as in calling via command line:
        //      exec ("/usr/bin/php path/to/script.php >/dev/null &");
        //    The script being called should start with these directives:
        //      ignore_user_abort(true);    
        //      set_time_limit(0);
        //    The first ensures not terminating the script after closing a connection. The
        //    second allows running the script without a time limit.
        //    The technique source:
        //      http://w-shadow.com/blog/2007/10/16/how-to-run-a-php-script-in-the-background/
  
  public static function backgroundPost($url)
  {
    $parts = parse_url($url);
   
    $fp = fsockopen(
      $parts['host'], 
      isset($parts['port']) ? $parts['port'] : 80, 
      $errno, $errstr, 30
    );
   
    if(!$fp)
      return false;
    
    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    if(isset($parts['query']))
      $out.= "Content-Length: ".strlen($parts['query'])."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    
    if(isset($parts['query']))
      $out .= $parts['query'];
      
    fwrite($fp, $out);
    fclose($fp);
    return true;
  }
  
        // Generates and returns GUID both on Unix and Windows platforms.
        // Source:
        //    http://guid.us/GUID/PHP
  
  public static function getGUID()
  {
    if(function_exists('com_create_guid')) {
      return com_create_guid();
    } else {
      mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45);// "-"
      $uuid = chr(123)// "{"
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12)
        .chr(125);// "}"
      return $uuid;
    }
  }
  
        // Validates the specified value for integer or floating number. Hexadecimal or scientific notations are not
        // validated. Examples:
        //    * 30
        //    * 30.55
        //    * +128.
        //    * .5
        //    * -534.875
        // Parameters:
        //    $number       - number to validate (without leading or trailing spaces)
        //    $decimalPoint - string with characters (may be several) used as decimal point
  
  public static function isNumber($number, $decimalPoint = '.')
  {
    return (bool)preg_match("/^[+-]?([0-9]+|[0-9]*[$decimalPoint][0-9]+|[0-9]+[$decimalPoint][0-9]*)$/", $number);
  }
  
        // Check if $value is true integer or 'integer string'. Examples:
        //    * 30
        //    * '30'
        //    * '030'
        // Parameters:
        //    $strict - if true then 'integer strings' such as '030' are not validated
  
  public static function isInt($value, $strict = true)
  {
    return $strict ? (string)(int)$value === (string)$value :
                     (string)(int)$value == (string)$value; 
  }
  
        // Checks if $value is true boolean (true|false), 'boolean integer' (0|1), or 'boolean string' ('0'|'1').
        
  public static function isBool($value)
  {
    return (int)(boolean)$value == (int)$value;
  }
  
        // Validates the specified date (and/or time) according to the given format.
        // Parameters:
        //    $date   - date (and/or time) in any format recognized by strtotime() PHP function
        //    $format - format for $date in syntax recognized by date() PHP function; default value is for MySQL date
        //              format yyyy-mm-dd  
  
  public static function isDate($date, $format = 'Y-m-d')
  {
    return date($format, strtotime($date)) == $date;
  }
  
        // Roughly validates the specified $email.
        // Returns:
        //    true, if $email looks like a valid email address, false otherwise.
  
  public static function isEmail($email)
  {
    $validEmailChars = 'abcdefghijklmnopqrstuvwxyz0123456789@#$%^&*+/-_[]{}().~';
    
    $email = strtolower($email);
    $n = strlen($email);
    
    for($i = 0; $i < $n; $i++)
      if(strpos($validEmailChars, $email[$i]) === FALSE)
        return false;
    
    return ($n >= 3) && strpos($email, '@') >= 1;
  }
  
        // Validates the specified absolute $url (should start with 'http://', 'https:' or other protocol)
        // Returns:
        //    true if valid, false otherwise.   
  
  public static function isUrl($url)
  {
    return (bool)filter_var($url, FILTER_VALIDATE_URL);
  }
  
        // Validates IPv4 address 'xxx.xxx.xxx.xxx' where each xxx is a number in the range 0..255.
        // Returns:
        //    true if valid, false otherwise.
  
  public static function isIp($ipaddress)
  {
    $parts = explode('.', $ipaddress);
    
    return count($parts) == 4 && $parts[0] >= 0 && $parts[0] <= 255 && $parts[1] >= 0 && $parts[1] <= 255 &&
                                 $parts[2] >= 0 && $parts[2] <= 255 && $parts[3] >= 0 && $parts[3] <= 255;
  }
  
        // Checks if specified mixed value is evaluated to PHP true or is equal to '0'.
        // Returns:
        //    false if $value is:
        //      * array()
        //      * null
        //      * ''
        //      * 0 (integer)
        //      * false,
        //    or true otherwise
  
  public static function isval($value)
  {
    return $value || $value === '0';
  }
 
        // Sends HTML bodied letter possibly with Russian subject and message.
        // Parameters:
        //    $to       - target email address 
        //    $from     - source email address 
        //    $subject  - email subject; for Russian value the $encoding in which this string
        //                is encoded (such as 'utf-8' or 'windows-1251') have to be specified
        //                as equal to the script (site) encoding.
        //    $message  - html-formatted email message
        //    $encoding - if specified, this value is used for both 'Content-type' header and
        //                the subject; otherwise, 'iso-8859-1' is used for 'Content-type'
        //                header, and subject goes as is assuming it is English.
        //	Returns:
        //    true if succeeded, false otherwise. 
  
  public static function sendMail($to, $from, $subject, $message, $encoding = 'iso-8859-1')
  {
  	$header  = "Content-Type: text/html; charset=\"$encoding\"\n";
  	$header .= "From: $from";
    
    if($encoding != 'iso-8859-1')
      $subject = "=?$encoding?b?".base64_encode($subject)."?=";
    
  	return @mail($to, $subject, $message, $header);
  }
  
        // Associates a name with a email address causing email agents to show that name instead of the email address.
        // New friendly email address is composed as follow:
        //    'name <email>'
        // where name is associated name and email is plain email address. Nice emails are used on par with plain email
        // addresses by util::sendMail(), see above.
        // Parameters:
        //    $name   - name for the email
        //    $email  - plain email address
  
  public static function niceEmail($name, $email)
  {
    return "$name <$email>";
  }
	
				// Deletes files or old files from the specified directory.
				// Parameters:
				//		$dir				- either relative or absolute path to the files directory
				//		$olderThan	-	if specified, the files are only deleted that are older than the
				//									current time by this value in seconds
	
	public static function deleteFiles($dir, $olderThan = '')
	{
		$olderThan = (int)$olderThan;
		
    $d = dir($dir);
    while (false !== ($entry = $d->read())) {
      if($entry == '.' || $entry == '..')
        continue;
      if(!is_file("$dir/$entry"))
				continue;
			
			if($olderThan == 0 || time() - filemtime("$dir/$entry") > $olderThan) {
				chmod("$dir/$entry", 0777);
				unlink("$dir/$entry");
			}
    }  
    $d->close();
	}
	
				// Returns first $limit characters of the specified $text. If there are more characters
				// than $limit in the $text, an ellipsis is appended to the end causing the result length to be equal:
        //    * $limit + 3 if $strict == false (the default), or
        //    * $limit if $strict == true ($limit must be >= 3 in this case)
	
	public static function textChunk($text, $limit, $strict = false)
	{
    if(strlen($text) <= $limit)
      return $text;
    
    if($strict)
      $limit -= 3;
      
    return substr($text, 0, $limit).'...';
	}
  
        /*// Converts 2D i-array of primitive values such as strings or numbers to csv-string as follows:
        //    * each primitive value is separated from another ','
        //    * each line is separated from another by "\n"
        // Parameters:
        //    $data - 2D i-array of primitive values such as strings or numbers not containing ',' and "\n"
  
  public static function csv($data)
  {
    foreach($data as &$row)
      $row = implode(',', $row);
      
    return "SEP=,\n".implode("\n", $data);  // SEP=, is needed for Excel to open the data in separate columns
  }*/
  
        // Gets the value of the specified parameter first as GET-parameter and if not set
        // then as SESSION-parameter. If the parameter is still not set or has illegal value
        // (if $allowedValues is provided), then it is assigned the default value. At the end,
        // the parameter is updated as the session parameter.
        // Parameters:
        //    $name           - parameter name
        //    $defaultValue   - default value to assign
        //    $allowedValues  - i-array of allowed values for the parameter, if any
        // Remark:
        //    In session, the parameter is stored under the name that is the concatenation of
        //    the current script name (without extension) and the parameter name.
  
  public static function gsParam($name, $defaultValue, $allowedValues = array())
  {
    $name2 = basename($_SERVER['PHP_SELF'], '.php').".$name";
    
    $value = isset($_GET[$name]) ? $_GET[$name] : @$_SESSION[$name2];
    
    if(!isset($value) || $allowedValues && !in_array($value, $allowedValues))
      $value = $defaultValue;
    
    $_SESSION[$name2] = $value;
    
    return $value;
  }
  
        // Outputs HTTP headers to make a browser to cache a dynamically generated resource
        // that is assumed to be not changeable (at least for the specified expiration period).
        // Parameter:
        //    $now      - current time as Unix timestamp
        //    $modified - last modified resource time as Unix timestamp
        //    $expires  - expiration period in days
  
  public static function cacheHeaders($now, $modified, $expires = 30)
  {
    $expires *= 60 * 60 * 24;   // get value in seconds
    
    header("Cache-Control: public, max-age=$expires, pre-check=$expires");  // pre-check for IE 
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', $now + $expires));
    header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $modified)); 
  }
	
				// Finds and replaces URLs in $text with the respective HTML links. 
				// Source: http://stackoverflow.com/questions/206059/php-validation-regex-for-url
				// Author: Owen, mundanity.com
				// What I have changed:
				//		* Subexpression #4: one-character alternatives changed for classes, added '\.$'
				//			branch to process the case when URL ends with dot and is at the end of text
				//		* Added linkifying of emails by analogy
				// Note. Fully qualified URls are only linkified. 
	
	public static function linkify($text)
	{
		$text = preg_replace(
			"#((http|https|ftp)://(\S*?\.\S*?))([\s;)\]\[{},\"':<>]|\.\s|\.$|$)#i",
			'<a href="$1">$1</a>$4',
			$text
		);
		
		$text = preg_replace(
			"#(\S*?@\S*?)([\s;)\]\[{},\"':<>]|\.\s|\.$|$)#i",
			'<a href="mailto:$1">$1</a>$2',
			$text
		);
		
		return $text;
	}
	
				// Detects whether the user agent is 'Opera'. 
	
	public static function isUserAgentOpera()
	{
		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
		return stristr($userAgent, 'opera');
	}
  
        // Simple yet effective solution to detect if the program runs on a mobile device. The method is recommended by
        // Mozilla here:
        //    https://developer.mozilla.org/en-US/docs/Web/HTTP/Browser_detection_using_the_user_agent
        // From this source:
        //    https://deviceatlas.com/blog/mobile-browser-user-agent-strings
        // we know that most popular mobile browsers on the end of 2018 are:
        //    * Safari Mobile: 54.87%
        //    * Chrome Mobile: 31.6%
        //    * Samsung Browser: 9.99% 
        // with total globe share 96.46%. Other popular browsers are:
        //    * UC Browser, Yandex Broswer, IE Mobile, Opera Mobile, Opera Mini, Firefox, MIUI Browser, Android Browser
        // All except Opera Mini have 'Mobi' substring in their User Agent strings.

  public static function isMobileDevice()
  {
    return strpos((string)@$_SERVER['HTTP_USER_AGENT'], 'Mobi') !== false;
  }
  
        // Returns maximum possible upload file size as minimum of 'post_max_size' and 'upload_max_filesize' PHP-ini
        // settings. The value is given in Mb with maximum 3 digits after decimal point as follows:
        //  '32M' ->  32
        //  '32K' ->  0.031
        //   '32' ->  0 
        // Remark:
        //    * Both 'post_max_size' and 'upload_max_filesize' cannot be modified via PHP-script.
  
  public static function maxUploadSize()
  {
    if(!function_exists('toBytes')) {   // to prevent redeclaring toBytes() for repeated maxUploadSize() calls
      function toBytes($val)
      {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
        case 'g':
          $val *= 1024;
        case 'm':
          $val *= 1024;
        case 'k':
          $val *= 1024;
        }
      
        return $val;
      }
    }
    
    $result = min(toBytes(ini_get('post_max_size')), toBytes(ini_get('upload_max_filesize')));
    
    // number_format() always have 3 digits after decimal point even for integer-like numbers (such as 32.000),
    // floatval() removes unneeded zero digits keeping non-zero digits!
    
    return floatval(number_format($result / (1024 * 1024), 3));
  }
  
        // Replaces accent characters in a string to their ASCII equivalents.
        // Parameters:
        //    $str -  a string to replace characters in
        // Returns:
        //    Copy of the original string with all accent characters replaced.
        // Note:
        //    The function is based on code chunk taken from here:
        //    http://stackoverflow.com/questions/10054818/convert-accented-characters-to-their-plain-ascii-equivalents
  
  public static function replaceAccents($str)
  {
    $normalizeChars = array(
      'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
      'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
      'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
      'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
      'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
      'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
      'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
      'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
    );
  
    return strtr($str, $normalizeChars);
  }
  
        // Gets short file name without extension given a full file path.
        // Parameters:
        //    $filepath - file path, either short or full
  
  public static function basename($filepath)
  {
    return basename($filepath, strrchr($filepath, '.'));
  }
  
        // Gets file extension with leading dot given a full file path.
        // Parameters:
        //    $filepath - file path, either short or full
  
  public static function fileext($filepath)
  {
    return strrchr($filepath, '.');
  }
  
/**
  * Changes permissions on files and directories within $dir and dives recursively
  * into found subdirectories.
  */
  
  
        // Recursively changes permissions on files and subdirectories under specified root directory.
        // Parameters:
        //    $dir              - root directory
        //    $dirPermissions   - permissions for subdirectories
        //    $filePermissions  - permissions for files
        //    [$dirsToExclude]  - i-array of subdirectories to exclude, if any
        // Returns:
        //    i-array of all processed subdirectories and files with the first enclosed in square brackets.
  
  public static function chmod_r($dir, $dirPermissions, $filePermissions, $dirsToExclude = array())
  {
    $result = array();
    
    $dp = opendir($dir);
    while($file = readdir($dp)) {
      if(($file == ".") || ($file == ".."))
        continue;

      $fullPath = $dir."/".$file;

      if(is_dir($fullPath) && !in_array($fullPath, $dirsToExclude)) {
        $result[] = "[$fullPath]";
        chmod($fullPath, $dirPermissions);
        $result = array_merge($result, self::chmod_r($fullPath, $dirPermissions, $filePermissions, $dirsToExclude));
      }
      else if(is_file($fullPath)) {
        $result[] = $fullPath;
        chmod($fullPath, $filePermissions);
      }
    }
    
    closedir($dp);
    
    return $result;
  }
  
        // Swaps values of 2 variables. May be used to swap values of 2 array elements like this:
        //    $a = [ 1, 2, 3 ];
        //    swap(a[0], a[1])
        //    $a -> [ 2, 1, 3 ]
        // Note:
        //    Nothing is done if $a and $b have equal values, or point to the same variable.
  
  public static function swap(&$a, &$b)
  {
    if($a != $b) {
      $tmp = $a;
      $a = $b;
      $b = $tmp;
    }
  }
  
        // Formats float $sum as number with 2 decimals and no thousand separator such as:
        //    1234.5678 -> 1234.57
        //    1234.5 -> 1234.50
  
  public static function money($sum)
  {
    return number_format($sum, 2, '.', '' /* no thousand separator */);
  }
  
        // Returns the output of the standard var_dump() instead of echoing. Several variables may be passed exactly as
        // in standard var_dump().

  public static function var_dump()
  {
    ob_start();
    call_user_func_array('var_dump', func_get_args());         
    $result = ob_get_contents();  // NULL if method does not return a value
    ob_end_clean();
    
    return $result;
  }
  
        // Inserts $what a-array into $where a-array before the specified $key. If $key is not among keys of $where,
        // $what is appended at the end of $where. Inserting is made as array merge, so values under same keys of $what
        // override those of $where, if any.
  
  public static function array_insert($where, $key, $what)
  {
    if(($pos = array_search($key, array_keys($where))) === false)
      return array_merge($where, $what);
      
    return  array_merge(array_slice($where, 0, $pos), $what, array_slice($where, $pos));
  }
}
?>