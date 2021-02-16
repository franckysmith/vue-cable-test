// File:        url.js (ES6 version)
// Contents:    URL-related functions
// Created:     04.04.2014
// Programmer:  Edward A. Shiryaev

var url = (function() {
  
  //---- private members ----
  
        // Used by url.isEmail(), see below.  
  
  var VALID_EMAIL_CHARS_ = 'abcdefghijklmnopqrstuvwxyz0123456789@#$%^&*+/-_[]{}().~';
  
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // The code for URL validator below has been ripped out from google closure library and adapted by me.
  // Origin: https://code.google.com/p/closure-library/source/browse/closure/goog/string/linkify.js
  // Original comments are retained. Used by url.isUrl(), see below.
  //
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
          // Set of characters to be put into a regex character set ("[...]"), used to match against a url hostname and
          // everything after it. It includes "#-@", which represents the characters "#$%&'()*+,-./0123456789:;<=>?@".
  
  var ACCEPTABLE_URL_CHARS_ = '\\w~#-@!\\[\\]';
  
          // List of all protocols patterns recognized in urls.
   
  var RECOGNIZED_PROTOCOLS_ = ['https?', 'ftp'];
  
          // Regular expression pattern that matches the beginning of an url. Contains a catching group to capture the
          // scheme.
          
  var PROTOCOL_START_ = '(' + RECOGNIZED_PROTOCOLS_.join('|') + ')://';
  
          // Regular expression pattern that matches the beginning of a typical http url without the http:// scheme.
          
  var WWW_START_ = 'www\\.';
  
          // Regular expression pattern that matches an url.
          
  var URL_ = '^(?:' + PROTOCOL_START_ + '|' + WWW_START_ + ')\\w[' + ACCEPTABLE_URL_CHARS_ + ']*$';
  
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  //---- public members ----
  
  var url = {};
  
        // Analog to PHP urlencode.

  url.urlencode = function(str)
  {
    return encodeURIComponent(str).
            replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').
            replace(/\*/g, '%2A').replace(/%20/g, '+').replace(/~/g, '%7E').replace(/-/g, '%2D').
            replace(/\./g, '%2E');
  }
  
        // Analog to PHP urldecode.
  
  url.urldecode = function(str)
  {
    return decodeURIComponent(str.replace(/\+/g, '%20'));
  }
  
        // Composes 'tel' URL by phone number keeping only digits and lead '+' in the phone number.
  
  url.telUrl = function(phone)
  {
    return 'tel:' + phone.trim().replace(/[^0-9]/g, function(nondigit, offset) {
      return nondigit == '+' && offset == 0 ? '+' : '';
    });
  }
  
        // Not strict email validator.
  
  url.isEmail = function(email)
  {
    email = email.toLowerCase();
    for(var i = 0; i < email.length; i++)
      if(VALID_EMAIL_CHARS_.indexOf(email.charAt(i)) == -1)
        return false;
  
    return (email.length >= 3) && (email.indexOf("@") >= 1);
  }
  
        // Url validator.  
  
  url.isUrl = function(url)
  {
    var regexp = new RegExp(URL_);
    return regexp.test(url);    
  }
  
        // Parses the specified URL into its parts.
        // Parameters:
        //    url - absolute URL or URL with one or more missing part(s) such as:
        //          * absolute URL e.g. 'http://example.com:80/path/to/file?id=5#fragment'
        //          * absolute path URL e.g. '/path/to/file?id=5#fragment'
        //          * relative path URL e.g. 'path/to/file?id=5#fragment'
        //          * origin URL e.g. 'http://example.com', or even
        //          * scheme relative URL e.g. //example.com/path/to/file'
        // Returns:
        //    {
        //      [ scheme:   scheme part if present e.g. 'http', 'https', 'ftp', or '//' (scheme relative URL) ]
        //      [ host:     host part if present e.g. 'example.com', ]
        //      [ port:     port number part if present e.g. 80, ]
        //      [ path:     path part if present e.g. '/path/to/file' or 'path/to/file', ]
        //      [ query:    query part without leading '?' if present e.g. 'id=5&name=abc' (without lead '?'), ]
        //      [ fragment: fragment part without leading '#' if present ]
        //    }
        //    Only parts explicitly presenting in the original URL go into the result.
        //
        // Remark:
        //    Partial URLs without scheme like:
        //      * 'test.php'
        //      * '/test.php'
        //      * 'path/to/test.php'
        //      * 'example.com'
        //      * 'www.example.com'
        //      * 'example.com/test.php' or even
        //      * 'example.com:80/test.php'
        //    are all treated as path (no host is filled up) -- this is exactly as PHP's parse_url() works (except the
        //    last case with port number where 'example.com' go into host in PHP's parse_url()). So the rule of thumb is
        //    valid URLs should have both scheme and host, or neither scheme nor host. 

  url.parseUrl = function(url)
  {
    var parts = {}, i;
    
    // process scheme
    if((i = url.indexOf('//')) == 0) {   // scheme relative URL
      parts.scheme = '//';
      url = url.substr(2);
    }
    else if((i = url.indexOf('://')) > 0) {
      parts.scheme = url.substr(0, i);
      url = url.substr(i + 3);
    }
    
    // process fragment
    if((i = url.indexOf('#')) >= 0) {
      parts.fragment = url.substr(i + 1);
      url = url.substr(0, i);
    }
    
    // process query
    if((i = url.indexOf('?')) >= 0) {
      parts.query = url.substr(i + 1);
      url = url.substr(0, i);
    }
    
    // process partial URL (see remark above)
    if(!parts.scheme) { 
      parts.path = url;  
      return parts;
    }
    
    // process path
    if((i = url.indexOf('/')) >= 0) {
      parts.path = url.substr(i);  
      url = url.substr(0, i);
    }
    
    // process port
    if((i = url.indexOf(':')) >= 0) {
      parts.port = url.substr(i + 1);  
      url = url.substr(0, i);
    }
    
    // process host
    if(url.length)
      parts.host = url;
    
    return parts;
  }
  
        // Builds URL by its parts.
        // Parameters:
        //    parts: {
        //      [ scheme:   scheme part if present e.g. 'http', 'https', 'ftp', or '//' (scheme relative URL) ]
        //      [ host:     host part if present e.g. 'example.com', ]
        //      [ port:     port number part if present e.g. 80, ]
        //      [ path:     path part if present e.g. '/path/to/file', ]
        //      [ query:    query part without leading '?' if present e.g. 'id=5&name=abc' (without lead '?'), ]
        //      [ fragment: fragment part without leading '#' if present ]
        //    }
        // Returns:
        //    Built URL.
  
  url.buildUrl = function(parts)
  {
    var url = '';    
    
    if(parts.scheme == '//')
      url += '//';
    else if(parts.scheme)
      url += parts.scheme + '://';
      
    if(parts.host)
      url += parts.host;  
    if(parts.port)
      url += ':' + parts.port;  
    if(parts.path)
      url += parts.path;
    if(parts.query)
      url += '?' + parts.query;
    if(parts.fragment)
      url += '#' + parts.fragment;
    
    return url;
  }
  
  //---- single URL part functions ----
  
        // Retrieves query part of the URL (without '?').
  
  url.getQuery = function(url)
  {
    var i;
    
    if((i = url.indexOf('?')) < 0)
      return '';
    
    url = url.substr(i + 1);
    
    // remove fragment part if any
    if((i = url.indexOf('#')) >= 0)
      url = url.substr(0, i);

    return url;
  }
  
        // Retrieves fragment part of the URL (without '#').
  
  url.getFragment = function(url)
  {
    var i;
    
    if((i = url.indexOf('#')) < 0)
      return '';
    
    return url.substr(i + 1);
  }
  
  //---------------------------------------------
  
          // Parses the query part of an URL.
          // Parameters:
          //    query - query part of the url as string (without ?)
          // Returns:
          //    Object with properties whose names are parameter names and values are url decoded
          //    parameters values.
  
  url.parseQuery = function(query)
  {
    var params = {};
    
    var params2 = query.split('&');
    for(var i = 0; i < params2.length; i++) {
      var nameval = params2[i].split('=');
      if(nameval.length == 2 && nameval[0] != '')
        params[url.urldecode(nameval[0])] = url.urldecode(nameval[1]);
    }
    
    return params;
  }
  

          // Builds the query part of an URL by parameters given as an object's properties.
          // Parameters:
          //    params  - object with properties; properties can be strings, numbers or booleans; all others are ignored,
          //              so no nested properties; boolean true is replaced for '1' while boolean false for '0';
          //              property values don't have to be url encoded as they are encoded inside
          // Returns:
          //    Built query part without '?'.
  
  url.buildQuery = function(params)
  {
    var query = [];
    for(var name in params) 
      if(typeof params[name] == 'string' || typeof params[name] == 'number')
        query.push(url.urlencode(name) + '=' + url.urlencode(params[name]));
      else if(typeof params[name] == 'boolean')
        query.push(url.urlencode(name) + '=' + (params[name] ? '1' : '0'));
    
    return query.join('&');
  }
  
        // Gets/sets GET-parameters from/to specified URL. When setting, new same-name parameters overwrite the old ones
        // keeping others untouched unless 'replace' flag is set. If 'replace' is set to true, new parameters fully
        // replace the old ones, i.e. old parameters that are not in new ones are deleted. For example, empty parameters,
        // {}, remove all parameters from the url, if any. Null values in new parameters remove same-name old parameters.
        // Parameters:
        //    url       - URL in any form that url.parseUrl() accepts, see above
        //    params    - a-array of name/value pairs to set into the URL, null value causes removing the parameter
        //                rather than upserting it; if not specified, the function instead gets parameters from the URL
        //    [replace] - if specified and evaluates to true, new parameters completely replace the old ones; otherwise
        //                only same-name parameters are replaced; used only in set-branch of the function
        // Returns:
        //    A-array of name/value pairs when getting parameters, or new URL when setting parameters
  
  url.params = function(url, params, replace)
  {
    var parts = this.parseUrl(url);
    var qParams = parts.query ? this.parseQuery(parts.query) : {};
    
    if(!params)             // get parameters
      return qParams;
    
    // set parameters
    
    if(replace)
      qParams = {};
      
    for(var name in params) {
      qParams[name] = params[name];
      if(qParams[name] === null)
        delete qParams[name];
    }

    parts.query = this.buildQuery(qParams);
    
    return this.buildUrl(parts);
  }
  
  return url;
  
})();

export { url };