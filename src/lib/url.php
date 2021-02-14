<?
// File:       url.php
// Contents:   url related functons
// Created:    05.07.2011
// Programmer: Edward A. Shiryaev

class url {
  
          // Returns true if current page was requested over https, false otherwise.  
  
  public static function https()
  {
    return @$_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' || $_SERVER['SERVER_PORT'] == 443;
  }

          // Returns an absolute url/path for the specified path.
          // Parameters:
          //    $path     - may be one of the following:
          //                * path relative to the current script such as 'file.php' or
          //                  'dir/subdir/file.php' (with no leading slash)
          //                * path relative to the document root (absolute path) such as
          //                  '/path/to/file.php' (with leading slash) - what is in  
          //                  $_SERVER["PHP_SELF"]
          //                * fully qualified URL (with 'http://', 'https://' or '//' scheme) in which case the function
          //                  simply returns it as is
          //    $protocol - 'auto', 'http://', 'https://', '//', or ''
          // Returns:
          //    Fully qualified URL if $protocol is not empty; otherwise an absolute path
          //    (path relative to the document root) with no protocol and host name
        
  public static function absoluteUrl($path, $protocol = 'auto')
  {
    if(strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '//') === 0)
      return $path;
    
    if(@$path[0] != '/') {    // relative to the current script
      // we need URI not filename, so use $_SERVER['REQUEST_URI'] instead of $_SERVER['PHP_SELF'] -- this is because
      // files may not reflect URIs, e.g. when domain is mapped to subfolder where code lives by .htaccess
      //$result = dirname($_SERVER['PHP_SELF']);
      $result = dirname(explode('?', $_SERVER['REQUEST_URI'])[0]);  // truncate parameters, if any
      $last = $result[strlen($result) - 1];
      $result .= ($last == '/' ? $path : "/$path");
    }
    else
      $result = $path;
    
    if($protocol) {
      if($protocol == 'auto')
        $protocol = url::https() ? 'https://' : 'http://';
      $result = $protocol.$_SERVER["HTTP_HOST"].$result;
    }
      
    return $result;
  }
  
          // Gets a relative URL as the absolute path by the fully qualified absolute URL.
          // Parameters:
          //    $absoluteUrl - fully qualified absolute URL e.g. 'http://host.com/path/to/file'
          // Returns:
          //    Relative URL in the form of absolute path e.g. '/path/to/file'. This includes
          //    path, query and fragments parts of an URL (leading scheme, host, optional port
          //    and optional username/password is not included). If $absoluteUrl is already
          //    relative URL or cannot be recognized as URL at all, it is returned as is.
  
  public static function relativeUrl($absoluteUrl)
  {
    if(!($parts = @parse_url($absoluteUrl)))
      return $absoluteUrl;
    
    $result = isset($parts['path']) ? $parts['path'] : '/';
    if(isset($parts['query']))
      $result .= '?'.$parts['query'];
    if(isset($parts['fragment']))
      $result .= '#'.$parts['fragment'];
    
    return $result;    
  }

          // Makes specified $url fully qualified with the specified $protocol if it is not yet.
          // Parameters:
          //    $url      - not fully qualified url such as 'www.site.com' or john@smith.com'
          //    $protocol - protocol schema one of 'auto', 'http://', 'https://', '//' or 'mailto:'

  public static function qualifyUrl($url, $protocol = 'auto')
  {
    if($scheme = parse_url($url, PHP_URL_SCHEME))
      return $url;  // already qualified with some protocol
    
    if($protocol == 'auto')
      $protocol = url::https() ? 'https://' : 'http://';
    
    // try to resolve malformed URL such as 'example.com' with $protocol
    if(parse_url($protocol.$url, PHP_URL_SCHEME))
      return $protocol.$url;
    
    // seriously malformed URL: return it as is
    return $url;
  }
  
          // Strips fully qualified $url of the specified $protocol if it is not yet.
          // Parameters:
          //    $url      - fully qualified url such as 'http://www.site.com' or
          //                mailto:john@smith.com'
          //    $protocol - protocol schema such as 'http://' or 'mailto:'
          // Returns:
          //    Stripped off URL or the original one if it is already stripped.
          
  public static function stripUrl($url, $protocol)
  {
    return strpos($url, $protocol) === 0 ? substr($url, strlen($protocol)) : $url;          
  }
  
          // Redirects to the page specified by its path and exits current script.
          // Parameters:
          //    $path - path that is relative to the current script or document root, or even fully qualified URL. See
          //            'path' parameter in absoluteUrl() above; if not specified, current page is refreshed
          //            ($_SERVER['REQUEST_URI'] holds document-root-relative-path with GET-parameters of current page
          //            such as: '/path/to/page.php?a=A&b=B')
  
  public static function redirectTo($path = '')
  {
    ($path = $path) || ($path = $_SERVER['REQUEST_URI']);
    
    header('Location: '.self::absoluteUrl($path));
    exit;
  }
  
          // Builds urlencoded query part of an URL by parameters given as a-array.
          // Parameters:
          //    params  - a-array of name/value pairs
          // Returns:
          //    Built query part without '?'. Parameters whose values are NULL are not included in the result. 
          
  public static function buildQuery($params)
  {
    return http_build_query($params, '', '&');
  }
  
          // Correctly adds a parameter to an URL which may or may not have other parameters.
          // Parameters:
          //    $url    - specified URL with optional parameters
          //    $name   - parameter name
          //    $value  - parameter value
  
  public static function addParam($url, $name, $value)
  {
    return $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').http_build_query(array($name => $value), '', '&');
  }
  
          // Adds new parameters to an URL that already may have parameters. Same name new parameters values override
          // old ones, if any.
          // Parameters:
          //    $url    - URL to set parameters to
          //    $params - a-array of new parameters as name/value pairs
          // Returns:
          //    new URL
  
  public static function addParams($url, $params)
  {
    if(!$params)
      return $url;
    
    @list($url, $query) = explode('?', $url, 2);
    $query = (string)$query;  // convert NULL -> '' if any
    parse_str($query, $params0);
    
    return $url.'?'.http_build_query(array_merge($params0, $params), '', '&');
  }
  
          // Composes 'tel' URL by phone number keeping only digits and lead '+', if any, in the phone number.
  
  public static function telUrl($phone)
  {
    $phone = trim($phone);
    assert($phone != "");
    $plus = $phone[0] == '+' ? '+' : '';
    $phone = preg_replace('/[^0-9]/', '', $phone);
    echo "tel:$plus$phone";
  }
}
?>