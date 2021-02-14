// File:       util.js
// Contents:   global utility functions
// Created:    02.06.2014
// Programmer: Edward A. Shiryaev

        // Polyfill for Object.keys() for IE8.

if(!Object.keys) {
  Object.keys = function(o)
  {
    var result = [];
    for(var key in o)
      if(o.hasOwnProperty(key))
        result.push(key);
        
    return result;
  }
}

        // Handy shorthand for empty function.

function noop() {}

        // Asserts a boolean expression. First, tries to use console assert(), then throws an Error object, if any.
        // Parameters:
        //    expr  - boolean expression to assert its TRUE value
        //    [msg] - custom error message

function assert(expr, msg)
{
  if(window.console && console.assert)
    console.assert(expr, msg);
  else if(!expr)
    throw new Error('Assert failed' + (msg ? ': ' + msg : ''));
}

function throwError(msg)
{
  throw new Error(msg);
}

        // Checks whether the specified object is 'Array' object. The code was taken from:
        // http://perfectionkills.com/instanceof-considered-harmful-or-how-to-write-a-robust-isarray
        // It is cross-frames solution. The check 'o instanceof Array' doesn't work if 'o' was created in other 'iframe'.

function isArray(o)
{
  return Object.prototype.toString.call(o) === '[object Array]'; 
}

        // Check whether the specified object is empty.

function isEmpty(o)
{
  for(var key in o)
    if(o.hasOwnProperty(key)) 
      return false;
  return true;
}

        // Returns the number of own properties for an object.

function length(o)
{
  return Object.keys(o).length;
}

        // Returns a deep copy of the object. The object may have properties of primitive types, objects or arrays.
        // Internal objects may in turn contain primitive types, objects or arrays.
        // Limitations:
        //    * Object o cannot contain functions inside
        //    * There should not be circular dependencies
        //    * properties cannot have values Infinity or undefined
        // An array of such objects or primitive types can also be cloned.
        // The code is taken from here, see second answer:
        //    https://stackoverflow.com/questions/728360/how-do-i-correctly-clone-a-javascript-object

function clone(o)
{
  return JSON.parse(JSON.stringify(o));
}

        // Returns a new object with just one property depth level by the source object having nested properties. Keys
        // of new object are obtained as paths to nested properties of the sourse object like this:
        //  { 
        //    a: {                       {
        //      a1,                        a[a1],
        //      a2                         a[a2], 
        //    },
        //    b,                      =>   b,
        //    c: {                          
        //      c1,                        c[c1],
        //      c2: {                      c[c2][c3]
        //        c3  
        //      }
        //    }
        //  }                            }
        //
        // The format of keys for result object is taken to comply PHP requirements for data sent in POST request to
        // be parsable by PHP into nested arrays.
        // Parameters:
        //    o       - source object with nested properties
        //    [path]  - current key path - should not be specified as used internally
        // Note:
        //    * Source object must fit the same requirements as for clone(), see above
        //    * Properties having empty object or array value are automatically removed by flatten()

var flatten = (function() {
  var result = {};
  
  function nextPath(path, key)
  {
    return path != '' ? path + '[' + key + ']' : key;
  }
  
  return function flatten(o, path) {
    
    path = path || '';
    if(path == '')
      result = {};  // discard the result filled on previous run

    for(var key in o)
      if(o.hasOwnProperty(key))
        if(typeof o[key] == 'object')
          flatten(o[key], nextPath(path, key));
        else
          result[nextPath(path, key)] = o[key];
    
    return result;      
  }
})();

        // For an array of object items each having string property usable as a unique key, makes the items accessible
        // by those keys in the manner of hash map. Accessing items by integer indexes remains!
        // Parameters:
        //    a   - indexed array of object items with unique keys
        //    key - key property name
        // Returns:
        //    Reference to the array - just the syntax sugar as the array is modified in place

function hashify(a, key)
{
  assert(key);
  
  a.forEach(function(item, index) {
    a[item[key]] = a[index];
  });
  
  return a;
}

        // Creates a new array by a keyed map of objects. Key value for an object may optionally be assigned to the
        // associated object for each object. If so, the object clones are modified and copied to the new array to keep
        // original objects untouched; in case of no key value assigned, just references to original objects are copied.
        // The resulting array may optionally be sorted by the sort function using a sort field, if any.
        // Parameters:
        //    m         - map of objects keyed by string keys
        //    [keyname] - name under which keys will be assigned to each object as property; undefined or null value
        //                prevents keys from writing to objects
        //    [sort]:   - sort parameters as follows, undefined means no sorting is made:
        //    {
        //      [sortby]: <object field name used for sorting, undefined means to sort by key if specified, see above>,
        //      [order]:  <'asc'|'desc', 'asc' if undefined>
        //    }

function array(m, keyname, sort)
{
        // Parameters:
        //    o - object to insert with already written key to it if it was specified
  
  function insert(o)
  {
    var pos = a.length;  // insert to the end by default
    
    if(sortby)
      a.find(function(o2, index) {
        var found = order == 'asc' ? o[sortby] < o2[sortby] : o[sortby] > o2[sortby];
        if(found) {
          pos = index;
          return true;
        }
        
        return false;
      });
    
    a.splice(pos, 0, o);
  }
  
  var a = []; // resulting array
  
  // init 'sortby' and 'order'  
  if(sort) {
    var sortby = sort.sortby || keyname;
    var order = sort.order || 'asc';
  }
  
  for(var key in m) {
    var o = m[key];
    if(keyname) {
      o = clone(o);
      o[keyname] = key;
    }
    insert(o);  
  }
  
  return a;
}

        // Analog to PHP array_flip().
        // Parameters:
        //    o - either object or array with unique values

function arrayFlip(o)
{
  var result = {};

  for(var key in o)
    if(o.hasOwnProperty(key))
      result[o[key]] = key;

  return result;
}

        // Simplified analog to PHP array_interset_key() (with single 'keys' argument).
        // Parameters:
        //    values  - keys to values map object to be filtered
        //    keys    - keys to dummy values map object to be filtered with
        // Returns:
        //    New object with only those values from 'values' whose keys are present in 'keys' map object.

function arrayIntersectKey(values, keys)
{
  var result = {};
  
  for(var key in values)
    if(values.hasOwnProperty(key))
      if(key in keys)
        result[key] = values[key];
        
  return result;      
}

        // Simplified analog to PHP array_merge():
        //    * with one 'o2' object
        //    * with 'o1' and 'o2' only having string keys
        // Parameters:
        //    o1    - string-keyed object
        //    [o2]  - string-keyed object, may be undefined
        // Returns:
        //    string-keyed object having:
        //      * all keys of o1 with o1's values if o2 does not have those keys, or o2's values otherwise
        //      * all keys of o2 with o2's values if o1 does not have those keys

function arrayMerge(o1, o2)
{
  var r = {};
  
  // make shallow copy of o1
  for(var key in o1)
    if(o1.hasOwnProperty(key))
      r[key] = o1[key];
  
  // merge with o2
  o2 = o2 || {};
  for(var key in o2)
    if(o2.hasOwnProperty(key))
      r[key] = o2[key];
      
  return r;      
}

        // Analog to PHP array_combine(): creates an object taking keys from the first array and values from
        // the second array.
        //    keys    - array of unique string keys
        //    values  - array of values
        // Note:
        //    keys and values arrays must be of equal length, or an error is fired

function arrayCombine(keys, values)
{
  assert(keys.length == values.length);
  
  var r = {};
  for(var i = 0; i < keys.length; i++)
    r[keys[i]] = values[i];
  
  return r;
}

        // Reduces frequent in time multiple function calls to a single call. Multiple calls are assumed to occur more
        // than one in a specified period. May be used to effectively handle a series of 'resize' and 'scroll' events
        // produced in a browser.
        // Parameters:
        //    func      - function to call once
        //    [period]  - period in ms within which to reduce multiple calls, 50 ms if not specified; 0 - means to stop
        //                calling functions until next throttle() with non-null period

var throttle = (function() {
  var timer;
  var funcs = {};
  
  function run()
  {
    for(var key in funcs) {
      funcs[key]();
      delete funcs[key];
    }
  }

  return function(func, period)
  {
    if(period == undefined)
      period = 50;
    
    funcs[func.toString()] = func;
    
    if(timer) clearTimeout(timer);
    if(period) timer = setTimeout(run, period);
  }
})();

        // Polyfill for Array.prototype.find(), taken from here:
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
        // Array.prototype.find() is natively supported: CH45+, Edge12+, FF25+, Opera32+ Safari8+, IE - not supported.

if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value: function(predicate) {
     // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If IsCallable(predicate) is false, throw a TypeError exception.
      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      }

      // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
      var thisArg = arguments[1];

      // 5. Let k be 0.
      var k = 0;

      // 6. Repeat, while k < len
      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];
        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        }
        // e. Increase k by 1.
        k++;
      }

      // 7. Return undefined.
      return undefined;
    },
    configurable: true,
    writable: true
  });
}

        // Returns true if the current browser is IE, false otherwise. Taken from here:
        // https://www.w3docs.com/snippets/javascript/how-to-detect-internet-explorer-in-javascript.html
  
function isBrowserIE()
{
  return navigator.userAgent.search('/MSIE|Trident/') > -1; // 'MSIE' for IE10-, 'Trident' for IE11
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

function isMobileDevice()
{
  return navigator.userAgent.indexOf('Mobi') > -1;
}

        // Returns true if the current device works under iOS, namely:
        //    * iPhone or iPad iOS < 13 or iPad iOS 13+ not in desktop mode (first line in code)
        //    * iPad iOS 13+ in desktop mode which is on by default (second line in code)
        // Taken from here, second comment:
        // https://stackoverflow.com/questions/9038625/detect-if-device-is-ios 

function isIOS()
{
  return /iPad|iPhone|iPod/.test(navigator.platform) ||  
  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

        // Asynchronously preloads images. Assumed to be called on page load.
        // Parameters:
        //    urls    - array of images' URLs
        //    [delay] - delay in ms to start loading, 100ms by default

function preloadImages(urls, delay)
{
  setTimeout(function() {
    for(var i = 0; i < urls.length; i++)
      (function() {
        var img = new Image();        
        img.src = urls[i];
      })();
  }, delay || 100);
}

        // Opens new popup window according to specified options, and brings focus to it.
        // Parameters:
        //      url   - URL of the resourse to load
        //    [ name  - '_blank' by default to open a new window; may also be '_parent', '_top', '_self' or any name to
        //              identify the window ]
        //    [ options: {
        //      [ left:       <screen coordinate of the left of the window>, ]
        //      [ top:        <screen coordinate of the top of the window>, ]
        //      [ width:      <window client width>, ]
        //      [ height:     <window client height>, ]
        //      [ menubar:    <false by default; specify true to render menubar>, ]
        //      [ toolbar:    <false by default; specify true to render toolbar with back, forward, etc. buttons>, ]
        //      [ location:   <false by default; specify true to render address bar>, ]
        //      [ directories:<false by default; specify true to render bookmarks toolbar>, ]
        //      [ status:     <false by default; specify true to render status bar>, ]
        //      [ resizable:  <true by default; specify false to make window not resizable (FF ignores this)>, ]
        //      [ scrollbars: <true by default; specify false to make window without scrollbars> ]
        //    } ]
        // Returns:
        //    Reference to newly created window.
        // Remarks:
        //    * If neither of options.left, options.top, options.width and options.height is specified, the new window
        //      is opened with the size of previously opened window (maximized if browser is maximized)
        //    * Otherwise all specified are taken as is, all unspecified are calculated as follows:
        //        o width and height take the default value 100
        //        o left and top take the default value 100 (if at least one is specified)
        //        o left and top are calculated to make window appear at screen center (if neither is specified)

function popupWindow(url, name, options)
{
  switch(arguments.length) {
  case 1:
    name = '_blank';
    options = {};
    break;
    
  case 2:
    if(typeof name == 'string' || name instanceof String)
      options = {};
    else {
      options = name;
      name = '_blank';
    }
  }
  
  if(options.left != undefined || options.top != undefined || options.width != undefined || options.height != undefined) {
    
    options.width = options.width || 100;
    options.height = options.height || 100;
    
    if(options.left != undefined || options.top != undefined) {
      if(options.left == undefined)
        options.left = 100;
      if(options.top == undefined)
        options.top = 100;
    }
    else {
    	options.left = (screen.availWidth - options.width) / 2;
    	options.top = (screen.availHeight - options.height) / 2;
    }
  }
  
  if(options.resizable == undefined)
    options.resizable = true;
  if(options.scrollbars == undefined)
    options.scrollbars = true;
  
  options.menubar = options.menubar ? 'yes' : 'no';
  options.toolbar = options.toolbar ? 'yes' : 'no';
  options.location = options.location ? 'yes' : 'no';
  options.directories = options.directories ? 'yes' : 'no';
  options.status = options.status ? 'yes' : 'no';
  options.resizable = options.resizable ? 'yes' : 'no';
  options.scrollbars = options.scrollbars ? 'yes' : 'no';

  var features = '';
  for(prop in options)
    features += prop + '=' + options[prop] + ',';
  features = features.substr(0, features.length - 1);
  
	var win = open(url, name, features);
	win.focus();
  
  return win;
}