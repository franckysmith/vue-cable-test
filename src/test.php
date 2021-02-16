<?
require_once 'config.php';
define('API_DONT_CALL_MAIN', true);
require_once 'api.php';
require_once 'fieldtypes.php';
require_once 'errorhandled.php';

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

/*$data = [ 2 ];
$result = api::call('cable_delete', $data);
print_r($result);*/

//$result = api::call('affair_get'/*, [ 'name' => 'Cabrel Olympia' ]*/);
//print_r($result);

/*$result = api::call('order_get', [ 'tech_id' => 32 ]);
print_r($result);*/

/*$orders = [
  [
    'cableid'   => 7,
    'affairid'  => 3,
    'tech_id'   => 135,
    'count'     => 30,
    'done'      => true
  ],
  [
    'cableid'   => 9,
    'affairid'  => 3,
    'tech_id'   => 135,
    'count'     => 15,
    'done'      => true
  ]
];

$result = api::call('order_add', $orders);
print_r($result);*/

/*$orders = [
  [
    'orderid'   => 10,
    'count'     => 60,
    'done'      => false
  ],
  [
    'orderid'   => 11, 
    'count'     => 30,
    'done'      => false
  ]
];

$result = api::call('order_update', $orders);
print_r($result);*/

/*$result = api::call('order_delete', [10, 11]);
print_r($result);*/

date_default_timezone_set('Europe/Paris');

echo db::now(), '<br>';
echo date('Y-m-d H:i:s');

?>