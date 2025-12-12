<?php
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
print "<TR bgcolor=#1ebcbabf align=center><TH> 이름 <TH> 출연 영화 <TH> 주소 <TH> 생년월일  </TR>\n";
$nrows = oci_fetch_all($stmt, $r, null, null, OCI_FETCHSTATEMENT_BY_ROW);
for($i=0;$i < $nrows;$i++) {
    $row = $r[$i];
    $name = $row['NAME'];
    $b = explode("-", $row['BIRTH']);
    if(!strcasecmp($row['GEN'], "male")) $gender = "남";
    else $gender = "여";
    $name2 = str_replace("'", "''", $name);
    $mvs_s = oci_parse($conn,  "select * from starsin where starname = :sn ");
    if (!$mvs_s)    p_error($conn);

    oci_bind_by_name($mvs_s, ":sn", $name2);
    
    $mvs_r = oci_execute($mvs_s);
    if (!$mvs_r) p_error ($mvs_s);
    $cnt = oci_fetch_all($mvs_s, $mv, null, null, OCI_FETCHSTATEMENT_BY_ROW);
    
    if($cnt > 0) $cnt_opt = $cnt;
    else $cnt_opt = 1;
    
    print "<TR> <TD rowspan=$cnt_opt> $name($gender) ";
    if($cnt > 0) {
        $first = true;
        for($j=0;$j < $cnt;$j++) {
            if(!$first)  print "<tr>";
            $title = htmlentities($mv[$j]["MOVIETITLE"], ENT_QUOTES);
            print "<td> <a target=_blank href='movies_array.php?title=$title&year={$mv[$j]["MOVIEYEAR"]}'>"
                            ."{$mv[$j]["MOVIETITLE"]}({$mv[$j]["MOVIEYEAR"]})</a> ";
            if($first)  {
                $first = false;
                print " <TD rowspan=$cnt_opt> {$row['ADDR']} <TD rowspan=$cnt_opt> $b[0]년 $b[1]월 $b[2]일  ";
            }
            print "</tr>\n";
        }
        oci_free_statement($mvs_s);
    } else print "<td> 출연영화 정보 없음 <TD rowspan=$cnt_opt> {$row['ADDR']} <TD rowspan=$cnt_opt> $b[0]년 $b[1]월 $b[2]일 </tr>\n";
}
print "</TABLE>\n";

oci_free_statement($stmt);
oci_close($conn);
?>
