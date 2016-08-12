<?php
session_start();
$_SESSION = $_POST;
include "Function.php";
$stop = true;
if (ValidateForm($_SESSION))
	$stop = false;
if (!empty($_POST['Outfile']) && !$stop) 
	 header("Location: ./DownloadCSV.php");
?>
<html lang = ru>
<head>
			<link rel="stylesheet" type="text/css" href="style.css">
   	   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   	   <title> База Данных </title>
</head>
<body>
<form method = "post">
	<fieldset>
		<legend>Выберите нужные колонки базы данных.</legend>

<?php 		
	$ColumnsID = array();
	if (!isset($_POST['Columns'])) {
		for ($i = 0; $i < count($Names); $i++)
			$ColumnsID[$i] = true;
	}
	else {
		$k = 0;
		for ($i = 0; $i < count($Names); $i++) { 
			if(isset($_POST['Columns'][$k]) && $_POST['Columns'][$k] == $Names[$i][0]) {
				$ColumnsID[$i] = true;
				$k++;					
			}
			else 
				$ColumnsID[$i] = false;
		}
	}	
	
	for ($i = 0; $i < count($Names); $i++) 
		if ($ColumnsID[$i])
			echo('<input type="checkbox" name="Columns[]" value="'.$Names[$i][0].'" checked>'.$Names[$i][1].'<br>');
		else 
			echo('<input type="checkbox" name="Columns[]" value="'.$Names[$i][0].'">'.$Names[$i][1].'<br>');
?>

	</fieldset>
	<fieldset>
		<legend>Фильтры</legend>
<?php
	echo("от ");
	for ($i = 0; $i < count($Date); $i++) {	
		echo("<input type='text' name='".$Date[$i][0]."[]' value='");
		if (isset($_POST[$Date[$i][0]]))
			echo($_POST[$Date[$i][0]][0]);
		echo("' size = 1 placeholder = '".$Date[$i][1]."'>");
	}
	echo(" до ");
	for ($i = 0; $i < count($Date); $i++) {	
		echo("<input type='text' name='".$Date[$i][0]."[]' value='");
		if (isset($_POST[$Date[$i][0]]))
			echo($_POST[$Date[$i][0]][1]);
		echo("' size = 1 placeholder = '".$Date[$i][1]."'> ");
	}
	echo($Names[0][1]."<br><select name='DateType'>");
	if (!isset($_POST['DateType']) || $_POST['DateType'] == 'method1')		
   	echo('<option value="method1">способ 1</option>
   		<option value="method2">способ 2</option>');
   else 
   	echo('<option value="method2">способ 2</option>
   		<option value="method1">способ 1</option>');
	echo("</select><a href = 'info.html'>*</a><p></p>");
	for($i = 1; $i < count($Names); $i++)	{
		echo("от <input type='text' name='".$Names[$i][0]."[]' value='");
		if (isset($_POST[$Names[$i][0]]))
			echo($_POST[$Names[$i][0]][0]);
		echo("' size = 1 placeholder = 'min'> до <input type='text' name='".$Names[$i][0]."[]' value='");
		if (isset($_POST[$Names[$i][0]][1]))
			echo($_POST[$Names[$i][0]][1]);
		echo("' size = 1 placeholder = 'max'> ".$Names[$i][1]."<br>");			
	}
?>
	</fieldset>
	
	<fieldset>
		<legend>Параметры вывода</legend>
		предпросмотр<br>
		<input type = "text" name = "TableSize" value = "<?php if(isset($_POST['TableSize'])) echo($_POST['TableSize']) ?>" size = 1 placeholder = "10">
		количество строк на странице<br>
		<p><input type = "checkbox" name = "Outfile" value = "Outfile">вывод в файл</p>
	</fieldset>
	
	<input type="submit" name="SubmitAll" value="Применить">
</form>

<?php
	if (!$stop && !isset($_POST['Outfile'])) {
		$Columns = $_POST['Columns'];
 		$sqlquery = GenerateQuery($_POST);
			
		echo('<h3> Ваш SQL запрос: </h3>');
    	echo("<p>".$sqlquery."</p>");	
		
			
		
		$conn = ConnectDB();
  		$result = $conn->query($sqlquery);
  		if ($result) {
 			echo('<h3> Ваши данные: </h3>');
  			$row_cnt = $result->num_rows;
  			echo('<p>Количество строк: '.$row_cnt.'</p>');
  			echo('<p><a href = "DownloadCSV.php">скачать в файл</a></p>');
    		$numrows = 10;
  			if (!empty($_POST['TableSize']))
    			$numrows = (int)$_POST['TableSize'];
    		$k = 0;
   		echo('<p>Первые '.$numrows.' строк:</p>');
    		echo('<table><tr><th></th>');
			for ($i = 0; $i < count($Columns); $i++)
				echo('<th>'.$Columns[$i].'</th>');
			echo('</tr>');
    		while($row = $result->fetch_assoc()) {
    			echo('<tr><th>'.($k + 1).'</th>');
        		for ($i = 0; $i < count($Columns); $i++) 
					if ($row[$Columns[$i]]=="")
						echo("<th>NULL</th>");
					else					
						echo('<th>'.$row[$Columns[$i]]."</th>"); 	
   			echo("</tr>");
   			$k++;
     			if ($k == $numrows)
  					break;
  			}
  			echo('</table>');
		}
    	if(!$result)
  			echo('<br>Query '.$sqlquery.' error<br>');
		$conn->close();
	}
		 
?>
</body>
</html>