<?php

	error_reporting(E_ALL);
 	ini_set("display_errors", 1);
 	 $id = $_POST['id'];

	$db = new mysqli("localhost", "root", "mysqldbhh", "nij_db_test");

	$result = $db->query("SELECT edges.type AS type, edges.id AS id, nodes.z_pos AS startz, nodes.x_pos AS startx, nodes.y_pos AS starty, edges.floor AS floor FROM edges INNER JOIN nodes ON (edges.start_node_fk=nodes.node_id AND edges.floor=nodes.floor AND edges.building_fk =".$id ." AND nodes.building_fk = ".$id.") order by id;") or die(mysqli_error($db));
	$result2 = $db->query("SELECT  edges.id AS id, nodes.z_pos AS endz, nodes.x_pos AS endx, nodes.y_pos AS endy,  edges.floor AS ffk FROM edges INNER JOIN nodes ON (edges.end_node_fk=nodes.node_id AND edges.floor=nodes.floor AND edges.building_fk =".$id ." AND nodes.building_fk = ".$id.") order by id;") or die(mysqli_error($db));

	//$result = $db->query("SELECT edges.type AS type, edges.id AS id, nodes.z_pos AS startz, nodes.x_pos AS startx, nodes.y_pos AS starty, edges.floor AS floor FROM edges INNER JOIN nodes ON (edges.start_node_fk=nodes.node_id AND edges.building_fk =".$id ." AND nodes.building_fk = ".$id.");") or die(mysqli_error($db));
	//$result2 = $db->query("SELECT edges.offsetX AS OX, edges.offsetY AS OY, edges.id AS id, nodes.z_pos AS endz, nodes.x_pos AS endx, nodes.y_pos AS endy,  edges.floor AS ffk FROM edges INNER JOIN nodes ON (edges.end_node_fk=nodes.node_id AND edges.building_fk =".$id ." AND nodes.building_fk = ".$id.");") or die(mysqli_error($db));

	$response = array();

	$counter = 0;

	while($row = $result->fetch_assoc()){
		$response[$counter]["id"]      = $row["id"];
		$response[$counter]["type"]      = $row["type"];
		$response[$counter]["start_x"]   = $row["startx"];
		$response[$counter]["start_y"] = $row["starty"];
		$response[$counter]["start_z"] = $row["startz"];
		$response[$counter]["floor"] = $row["floor"];
		$counter++;
	}
	
	$counter = 0;

	while($row = $result2->fetch_assoc()){
		$response[$counter]["end_x"]   = $row["endx"];
		$response[$counter]["end_y"] = $row["endy"];
		$response[$counter]["end_z"] = $row["endz"];
		$counter++;
	}

	$db->close();

	echo json_encode($response);

	// error_reporting(E_ALL);
 // 	ini_set("display_errors", 1);

	// $db_connection = pg_connect("host=ccis017.uncc.edu dbname=nij_new_database user=flange password=trudge");
	// $result = pg_query($db_connection, "SELECT * FROM denny_flr2_hallway");

	// while ($row = pg_fetch_row($result)) {
	//  echo json_encode($row);

	// }
