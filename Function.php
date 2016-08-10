<?php
function error_default($place){
	echo('<p class = "error">Ошибка. Проверьте фильтр "'. $place.'"');
}
function error($side, $place) {
	echo('<p class = "error">Ошибка. Проверьте ');
	if($side)
		echo('нижнюю');
	else 
		echo('верхнюю');
	echo(' границу фильтра "'.$place.'".</p>');
	return false;	
}
function error_echo($message) {
	echo('<p class = "error">Ошибка. '. $message);
}


function day_of_year($day, $month) {
	$a = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$result = $day;
	for ($i = 1; $i < $month; $i++) {
		$result += $a[$i];
	}
	return $result;
}

function ValidateForm($Data) {
	global $Names;
	global $Date;
	//не нажата кнопка submit
	if (empty($Data['SubmitAll'])) 
		return false;
	
	//проверка существования столбцов	
	if (empty($Data['Columns'])) {
		echo('Вы ничего не выбрали');
		return false;		
	}
		
	//проверка даты
	$l = 0;
	$r = 0;
	for ($i = 0; $i < count($Date); $i++) {
		//проверка на то, что дата - набор чисел
		if (!empty($Data[$Date[$i][0]][0]))  {
			$l++;
			if (!is_numeric($Data[$Date[$i][0]][0]) || $Data[$Date[$i][0]][0] < $Date[$i][2] || $Data[$Date[$i][0]][0] > $Date[$i][3])
			 	return error(true, $Names[0][1].': '.$Date[$i][1]); 
		}
		if (!empty($Data[$Date[$i][0]][1]))  {
			$r++;		
			if (!is_numeric($Data[$Date[$i][0]][1]) || $Data[$Date[$i][0]][1] < $Date[$i][2] || $Data[$Date[$i][0]][1] > $Date[$i][3])
				return error(false, $Names[0][1].': '.$Date[$i][1]);
		}
	}		
	
	if ($Data['DateType'] == "method1"){ 
		//проверка даты в первом случае
		if(empty($Data['DAY'][0]) && !empty($Data['MONTH'][0])) return error(true, $Names[0][1].': '.$Date[0][1]);
		if(!empty($Data['DAY'][0]) && empty($Data['MONTH'][0])) return error(true, $Names[0][1].': '.$Date[1][1]);
		if(empty($Data['DAY'][1]) && !empty($Data['MONTH'][1])) return error(false, $Names[0][1].': '.$Date[0][1]);
		if(!empty($Data['DAY'][0]) && empty($Data['MONTH'][1])) return error(false, $Names[0][1].': '.$Date[1][1]);
	}
	else 
		//проверка даты во втором случае
		if (($l > 0 && $l < 3)||($r > 0 && $r < 3)) return error_default($Names[0][1]);
	
	
	
	
	
	for ($i = 1; $i < count($Names); $i++) {			
		if (!empty($Data[$Names[$i][0]][0])  && !is_numeric($Data[$Names[$i][0]][0])) return error(true, $Names[$i][1]);
		if (!empty($Data[$Names[$i][0]][1]) && !is_numeric($Data[$Names[$i][0]][1])) return error(false, $Names[$i][1]);
		if (!empty($Data[$Names[$i][0]][0]) 
			&& !empty($Data[$Names[$i][0]][1]) 
			&& $Data[$Names[$i][0]][0] > $Data[$Names[$i][0]][1])
			return error_echo('Левая граница фильтра "'.$Names[$i][1].'" больше правой.');	
	}
		
	//проверка параметра количество строк на странице
	if (!empty($Data['TableSize']) && !is_numeric($Data['TableSize'])) {
		return error_echo('Проверьте значение параметра "количество строк на странице".');	
	}
		
	return true;
}






function GenerateQuery($Columns) {
	Global $Names;
 	Global $Date;
		
	if (empty($_POST['Count'])){
		$sqlquery = "SELECT ".$Columns[0];
		for ($i = 1; $i < count($Columns); $i++)
			$sqlquery = $sqlquery.', '.$Columns[$i];
	}
	else {
		$sqlquery = "SELECT COUNT(".$Columns[0].')';
		for ($i = 1; $i < count($Columns); $i++)
			$sqlquery = $sqlquery.', COUNT('.$Columns[$i].')';
	}
	$sqlquery = $sqlquery.' FROM STATION_EXT';
	$Limits = array();
	$k = 0;
	if ($_POST['DateType'] == "method1") {
		if ($_POST[$Date[2][0]][0] != "") {
			$Limits[$k] = $Date[2][0]."(".$Names[0][0].") >= ".$_POST[$Date[2][0]][0];
				$k++;
		}
		if ($_POST[$Date[2][0]][1] != "") {
			$Limits[$k] = $Date[2][0]."(".$Names[0][0].") <= ".$_POST[$Date[2][0]][1];
			$k++;
		}
		if ($_POST[$Date[0][0]][0] != "" && $_POST[$Date[0][0]][1] != "" 
			&& day_of_year($_POST['DAY'][0], $_POST['MONTH'][0]) > day_of_year($_POST['DAY'][1], $_POST['MONTH'][1])) 
			$Limits[$k] = '((MONTH(DAYS) > '.$_POST['MONTH'][0].' || 
				(MONTH(DAYS) = '.$_POST['MONTH'][0].' AND DAY(DAYS) >= '.$_POST['DAY'][0].')) || 
				(MONTH(DAYS) < '.$_POST['MONTH'][1].' || 
				(MONTH(DAYS) = '.$_POST['MONTH'][1].' AND DAY(DAYS) <= '.$_POST['DAY'][1].')))';
		else {
			if ($_POST[$Date[0][0]][0] != "") {
				$Limits[$k] = "(".$Date[1][0]."(".$Names[0][0].") > ".$_POST[$Date[1][0]][0]." OR (".
					$Date[1][0]."(".$Names[0][0].") = ".$_POST[$Date[1][0]][0]." AND ".
					$Date[0][0]."(".$Names[0][0].") >= ".$_POST[$Date[0][0]][0]."))";
					$k++;
			}
			if ($_POST[$Date[0][0]][1] != "") {
				$Limits[$k] = "(".$Date[1][0]."(".$Names[0][0].") < ".$_POST[$Date[1][0]][1]." OR (".
					$Date[1][0]."(".$Names[0][0].") = ".$_POST[$Date[1][0]][1]." AND ".
					$Date[0][0]."(".$Names[0][0].") <= ".$_POST[$Date[0][0]][1]."))";
					$k++;
			}
		}
	}
	else { 
		if ($_POST[$Date[0][0]][0] != "") {
			$Limits[$k] = $Names[0][0]." >= '".$_POST[$Date[2][0]][0]."-".$_POST[$Date[1][0]][0]."-".$_POST[$Date[0][0]][0]."'";
			$k++;
		}
		if ($_POST[$Date[0][0]][1] != "") {
			$Limits[$k] = $Names[0][0]." <= '".$_POST[$Date[2][0]][1]."-".$_POST[$Date[1][0]][1]."-".$_POST[$Date[0][0]][1]."'";
			$k++;
		}
	}
	for ($i = 1; $i < count($Names); $i++) {
		if ($_POST[$Names[$i][0]][0] != "") {
			$Limits[$k] = $Names[$i][0].">=".$_POST[$Names[$i][0]][0];
			$k++;
		}
		if ($_POST[$Names[$i][0]][1] != "") {
			$Limits[$k] = $Names[$i][0]."<=".$_POST[$Names[$i][0]][1];
			$k++;
		}
	}
	if (count($Limits) > 0) {
		$sqlquery .= " WHERE ".$Limits[0];
		for ($i = 1; $i < count($Limits); $i++)
			$sqlquery .= " AND ".$Limits[$i];
	}
	$filename = $_POST['Output'];
   if(!empty($filename))
    	$sqlquery .= " INTO OUTFILE 'temp_output'";//.$filename."'";
	return $sqlquery;			
}
	
	function ConnectDB() {
		$servername = "localhost";
		$username = "root";
		$password = "trololo";
		$conn = new mysqli($servername, $username, $password);
		if ($conn->connect_error) 
   		die("Connection failed: " . $conn->connect_error);
		$DB = "DATA_STATION_2015";
		if (!$conn->query('USE '.$DB)) 
  			echo ("Error using DB".$DB.": ". $conn->error);
  		return $conn;
	}	
?>
