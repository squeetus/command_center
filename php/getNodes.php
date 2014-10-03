<?php

	error_reporting(E_ALL);
 	ini_set("display_errors", 1);
 	$id = $_POST['id'];


	$db = new mysqli("localhost", "root", "mysqldbhh", "nij_db_test");

	$result = $db->query("SELECT id AS id, z_pos AS Z, x_pos AS X, y_pos AS Y, type AS type, width, height, floor FROM nodes WHERE building_fk = ". $id .";") or die(mysqli_error($db));
	
	$response = array();

	$counter = 0;

	while($row = $result->fetch_assoc()){
		
		$response[$counter]["id"]      = $row["id"];
		$response[$counter]["X"]   = $row["X"];
		$response[$counter]["Y"] = $row["Y"];
		$response[$counter]["Z"] = $row["Z"];
		$response[$counter]["type"] = $row["type"];
		$response[$counter]["floor"] = $row["floor"];
		$response[$counter]["width"] = $row["width"];
		$response[$counter]["height"] = $row["height"];
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