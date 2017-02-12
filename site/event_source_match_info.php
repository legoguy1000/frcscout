<?php
/* include('includes.php');
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");

$lastId = $_SERVER["HTTP_LAST_EVENT_ID"];
if (isset($lastId) && !empty($lastId) && is_numeric($lastId)) {
	$lastId = intval($lastId);
	$lastId++;
}
if(isset($_GET['event']))
{
	$event_key = $_GET['event'];
	$startTime = time();
	$send=false;
	$masterArr = array();
	while (true) {
		$query = 'select * from match_info where event_key = "'.$event_key.'" ORDER BY match_num ASC';
		$result = $db->query($query) or die(errorHandle(mysqli_error($db)));
		if($result->num_rows > 0)
		{
			$data = array();
			while($row = $result->fetch_assoc())
			{
				$timestamp = strtotime($row['timestamp']);
				if($timestamp >= $startTime)
				{
					$send = true;
				}
				$data[$row['match_key']] = $row;
			}
			if($send == true || !isset($lastId))
			{
				sendMessage($lastId, json_encode($data));
				$lastId++;
				$send = false;
				$startTime = time();
			}
		}
		sleep(5);
	}
}
function sendMessage($id, $data) {
	echo "id: $id\n";
	echo "data: $data\n\n";
	ob_flush();
	flush();
}
 */

?>
