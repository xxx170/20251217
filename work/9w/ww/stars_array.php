<?php
// for correct error message outputs
//putenv("NLS_LANG=KOREAN_KOREA.AL32UTF8");

function p_error($id=null) {
    if($id == null) $e = oci_error();
    else $e = oci_error($id);
    print htmlentities($e['message']);
    exit();
}

$conn = oci_connect("scott","tiger", "localhost/lecture");
if (!$conn)    p_error();

//$title = $_GET["title"];
//$year = $_GET["year"];

$stmt = oci_parse($conn,
        "select name, address addr, trim(gender) gen, to_char(birthdate, 'YYYY-MM-DD') birth from moviestar ".
	"order by 1, 2 ");
if (!$stmt)    p_error($conn);

$r = oci_execute($stmt);
if (!$r) p_error ($stmt);

print "<TABLE bgcolor=#abbcbabc border=1 cellspacing=2>\n";
print "<TR bgcolor=#1ebcbabf align=center><TH> 이름 <TH> 주소 <TH> 생년월일 <TH> 출연 영화 </TR>\n";

while ($row = oci_fetch_array($stmt)) {
    $name = $row['NAME'];
    $b = explode("-", $row['BIRTH']);
    if(!strcasecmp($row['GEN'], "male")) $gender = "남";
    else $gender = "여";
    $name2 = str_replace("'", "''", $name);
    $cnt_s = oci_parse($conn,  "select count(*) from starsin where starname = '$name2' ");
    if (!$cnt_s)    p_error($conn);

    $cnt_r = oci_execute($cnt_s);
    if (!$cnt_r) p_error ($cnt_s);
    $cnt = oci_fetch_array($cnt_s)[0];
    if($cnt > 0) $cnt_opt = $cnt;
    else $cnt_opt = 1;
    print "<TR> <TD rowspan=$cnt_opt> $name($gender) <TD rowspan=$cnt_opt> {$row['ADDR']} <TD rowspan=$cnt_opt> $b[0]년 $b[1]월 $b[2]일  ";
    
    if($cnt > 0) {
        $mvs_s = oci_parse($conn,  "select * from starsin where starname = '$name2' ");
        if (!$mvs_s)    p_error($conn);

        $mvs_r = oci_execute($mvs_s);
        if (!$mvs_r) p_error ($mvs_s);
        
        $first = true;
        while ($mv = oci_fetch_array($mvs_s)) {
            if($first) $first = false;
            else print "<tr>";
            $title = htmlentities($mv[0], ENT_QUOTES);
            print "<td> <a target=_blank href='movies_array.php?title=$title&year=$mv[1]'>$mv[0]($mv[1])</a> </tr>";
        } 
    } else print "<td> 출연영화 정보 없음 </tr>\n";
}
print "</TABLE>\n";

oci_free_statement($stmt);
oci_close($conn);
?>
