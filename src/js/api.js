// File:       api.js
// Contents:   Api, the convenience class to provide promisified interface to API
// Created:    13.02.2019
// Programmer: Edward A. Shiryaev

//<?require_once 'es6-promise.auto.js';?>
//<?require_once 'url.js';?>
//<?require_once 'util.js';?>
//<?require_once 'ajax.js';?>

        // Parameters:
        //    apiUrl  - 'BookingTech' api url.

function Api(apiUrl)
{
  var apiUrl_ = apiUrl;
  
  ajax.timeout = 15000;
  
        // Requests the server to execute specified API method with parameters.
        //    method    - method name, see api.php for supported methods
        //    [params]  - object with parameters, specific to the method requested, see api.php
        // Returns:
        //    Promise<object>, or nothing if the method does not assume return data;
        //    in case of an error, <object> has 'error' field keeping the error message and optionally other fields
        //    keeping info about the error details
  
  this.call = function(method, params)
  {
    params = params || {};
    
    return new Promise(function(resolve, reject) {
      ajax.send({
        url: apiUrl_,
        urlParams: {
          method: method
        },
        postData: params,
        format: {
          output: 'json'
        }
      }, function(response) {
        if(response.error)
          reject(response);
        else
          resolve(response);
      });
    });
  }
  
        // Returns api-url of the specified method.  
  
  this.url = function(method)
  {
    return url.params(apiUrl_, { method: method });
  }
}