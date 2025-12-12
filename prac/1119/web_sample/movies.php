<?php
// for correct error message outputs
//putenv("NLS_LANG=KOREAN_KOREA.AL32UTF8");

$conn = oci_connect("DB계정","DB암호", "localhost/lecture");
if (!$conn) {
	$e = oci_error();
	print htmlentities($e['message']);
}

//$title = $_GET["title"];
//$year = $_GET["year"];

$stmt = oci_parse($conn,
	"select title, year, length, studioname as name from movie, studio ".
	" where studioname = name order by 1, 2 ");
if (!$stmt) {
	$e = oci_error($conn);
	print $e['message'];
	//print htmlentities($e['message']);
}

/* the define MUST be done BEFORE ociexecute! */
oci_define_by_name($stmt,"TITLE",$title);
oci_define_by_name($stmt,"YEAR",$year);
oci_define_by_name($stmt,"LENGTH",$length);
oci_define_by_name($stmt,"NAME",$studio);

$r = oci_execute($stmt);
if (!$r) {
	$e = oci_error();
	print htmlentities($e['message']);
}

print "<TABLE bgcolor=#abbcbabc border=1 cellspacing=2>\n";
print "<TR bgcolor=#1ebcbabf align=center><TH> 제목 <TH> 연도 <TH> 상영시간 <TH> 영화사</TR>\n";

while (oci_fetch($stmt)) {
    print "<TR> <TD> $title <TD> $year <TD> $length <TD> $studio </TR>\n";
}
print "</TABLE>\n";

oci_free_statement($stmt);
oci_close($conn);
?>
