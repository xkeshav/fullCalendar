<?php
include_once("includes/config.php");
$charges = array();
$stmt = "SELECT * FROM ".DBSPCL_CHARGE."  WHERE property_id = ".(int)$_POST['propID'];
$query = $db->query($stmt);
while($fetchquery= $db->fetchNextObject($query))
{
    $price = html_entity_decode($symbol.round($fetchquery->amount));
    $editable = ($fetchquery->user_id == $_SESSION['userId']) ? true : false;
    //__print($editable);
    //continue;
    $charges[] = array(
                        'id'=> $fetchquery->charge_id,
                        'title' => $fetchquery->amount,
                        'start' => $fetchquery->start_date,
                        'end' => $fetchquery->end_date,
                        'allDay'=> true,
                        'className' =>'editable',
                        'editable'=> $editable
                    );
}
echo json_encode($charges);
?>
