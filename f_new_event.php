<?php
//this is only for DB connection
include("includes/config.php");
if(isset($_POST['eventTitle'])) {
    $title = intval($_POST['eventTitle']);
    if(empty($title)){
      echo json_encode(array('success'=>false));
      exit;
    }
}
$action = $_POST['action'];
switch($action) {
	case 'add': addEvent();	break;
	case 'edit': updateEvent(); break;
	case 'del': deleteEvent();break;
        case 'get': getEvents(); break;
        default : getEvents();
}

function addEvent()
{
    global $db;
    if(!isset($_POST['endDate'])) {
		$_POST['endDate'] = $_POST['startDate'];
    }
	 $sql = "INSERT INTO ".DBSPCL_CHARGE." SET
                                                    property_id =".(int)$_POST['propID'].",
                                                    user_id = ".(int)$_POST['userID'].",
                                                    start_date = '".$_POST['startDate']."' ,
                                                    end_date = '".$_POST['endDate']."',
                                                    amount = '".$_POST['eventTitle']."' ";

        $query = $db->query($sql);
        $latest = $db->lastInsertedId();

        $str_query = "SELECT charge_id AS id, start_date AS start,end_date AS end,amount AS title
                      FROM ". DBSPCL_CHARGE ." WHERE charge_id = ".(int)$latest ;
        $obj_result = $db->query($str_query);
        $arr_event = $db->fetchNextAssoc($obj_result);
        $arr_event['allDay'] = true;
        $arr_event['color'] = '#383C3D';
        $arr_event['textColor'] = '#A99834';
        $arr_event['editable'] = $arr_event['user_id'] == $_SESSION['userID'] ? true : false;

        echo json_encode(array('success'=>true, 'event'=>$arr_event));
        exit;
}

function updateEvent()
{
          global $db;
	  $sql = "UPDATE ".DBSPCL_CHARGE." SET amount = '".$_POST['eventTitle']."' WHERE charge_id =".(int)$_POST['eventId']." ";

        $query = $db->query($sql);
        $latest =(int)$_POST['eventId'];

        $str_query = "SELECT charge_id AS id, start_date AS start,end_date AS end,amount AS title
                      FROM ". DBSPCL_CHARGE ." WHERE charge_id = ".(int)$latest ;
        $obj_result = $db->query($str_query);
        $arr_event = $db->fetchNextAssoc($obj_result);
        $arr_event['allDay'] = true;
        $arr_event['editable'] = $arr_event['user_id'] == $_SESSION['userID'] ? true : false;
        $arr_event['color'] = '#383C3D';
        $arr_event['textColor'] = '#A99834';
        echo json_encode(array('success'=>true, 'event'=>$arr_event));
        exit;
}
function deleteEvent()
{
    global $db;
    $sql = "DELETE FROM ".DBSPCL_CHARGE." WHERE charge_id =".(int)$_POST['eventId']." ";
    $query = $db->query($sql);
    echo json_encode(array('success'=>true));
    exit;
}
function getEvents()
{
global $db;
$arr_event = array();
$sql = " SELECT c.curcy_symbol FROM ".DBCURNCY." c  INNER JOIN ".DBPRICING." p ON p.currency_id = c.curcy_id
         WHERE p.property_id =".(int)$_POST['propID'];
$symbol = $db->queryUniqueValue($sql);

$run = "SELECT bk.booking_id,bk.checkin_date,bk.checkout_date,bk.booking_name,bk.rent FROM ".DBBOOKING." bk  WHERE property_id =".(int)$_POST['propID'];
$query= $db->query($run);
while($fectchquery= $db->fetchNextObject($query))
{
    $detail = sprintf("<br/><i>Booked By:</i> %s <br/><i>Total Charge(s):</i> %s",
                        $fectchquery->booking_name,html_entity_decode($symbol).$fectchquery->rent);
    $arr_event[] = array(
                        'title' => "Booked",
                        'start' => $fectchquery->checkin_date,
                        'end' => $fectchquery->checkout_date,
                        'allDay'=> true,
                        'description'=> $detail,
                        'className'=>'preBook');
}
echo json_encode($arr_event);
exit;

}
?>