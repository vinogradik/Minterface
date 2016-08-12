<?php
	session_start();
	include 'Function.php';
	if (ValidateForm($_SESSION)) {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data'.$_SERVER['REQUEST_TIME'].'.csv');
		$Columns = $_SESSION['Columns'];
		$sqlquery = GenerateQuery($_SESSION);
		$conn = ConnectDB();
   	$result = $conn->query($sqlquery);
		if ($result) {
			for ($i = 0; $i < count($Columns); $i++)
				echo($Columns[$i].'	');
			echo("\n");
			while($row = $result->fetch_assoc()) {
        		for ($i = 0; $i < count($Columns); $i++) 
					if ($row[$Columns[$i]]=="")
						echo("NULL	");
					else					
						echo($row[$Columns[$i]]."	");
					echo("\n"); 	
				}	
	
		}
	}
	

?>