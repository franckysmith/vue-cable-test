<?
require_once 'config.php';
/*define('API_DONT_CALL_MAIN', true);
require_once 'api.php';*/

//var_dump((array)json_decode(file_get_contents('php://input'), true));
//var_dump(json_decode('{}', true));

/*$cables = api::call('cable_get');
print_r($cables);*/

/*$cables = [
  [
    'cableid' => 22,
    'name'  => 'do48',
    'type'  => 'microphone',
    'total' =>  100
  ],
  [
    'cableid' => 23,
    'name'  => 'rs232',
    'type'  => 'speaker',
    'total' => 50
  ]
];

$result = api::call('cable_update', $cables);
print_r($result);*/

/*$data = [ 22, 23 ];
$result = api::call('cable_delete', $data);
print_r($result);*/

//$result = api::call('affair_get'/*, [ 'name' => 'Cabrel Olympia' ]*/);
//print_r($result);

/*const ID = [ 'required' => true, 'type' => 'int', 'minvalue' => 1 ];
var_dump(ID);*/

phpinfo();


?>