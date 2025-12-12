<?
// define error message outputs
function err_print ($e_message) {
	print "<font color=red>".$e_message."</font><p>";
}

$conn = oci_connect("DB계정","DB암호", "localhost/lecture");
if ($conn == false) {
	$e = oci_error();
	err_print("Connection Failed ...");
	print htmlentities($e['message']);
	trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

$query = "select title, year, length from movie";
$stmt = oci_parse($conn,$query);
if (!$stmt) {
	$e = oci_error();
	err_print("Parsing Failed ...");
	print htmlentities($e['message']);
}

$r = oci_execute($stmt);
if (!$r) {
	$e = oci_error();
	err_print("Execution Failed ...");
	print htmlentities($e['message']);
}
$nrows = oci_fetch_all($stmt, $results);
if($nrows > 0) {
	print "<TABLE bgcolor=#abbebabc border=1>\n";
	print "<TR bgcolor=#fbfebaec align=center>";
	print "<TH> 제목 <TH> 연도 <TH> 상영시간</TR>\n";
	/*
	foreach ($results as $key => $val) {
		print "<TH>$key,$val[3]</th>\n";
	}
	print "</TR>\n";
	*/

	for($i = 0; $i < $nrows; $i++) {
		print "<TR>\n";
		foreach ($results as $data) {
			print "<TD> $data[$i]\n";
		}
		print "</TR>\n";
	}
	print "</TABLE>\n";
} else
	print "No Data Found<br>\n";

oci_free_statement($stmt);
oci_close($conn);
?>
