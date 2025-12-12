<?php
// [사진에는 안 보이지만 필수 함수] 에러 처리 함수
function p_error($msg, $id=null) {
    print "<font color=red>".$msg."</font><br>";
    if($id == null) $e = oci_error();
    else $e = oci_error($id);
    print htmlentities($e['message']);
    print "\n<pre>\n";
    print htmlentities($e['sqltext']);
    printf("\n%".($e['offset']+1)."s", "^");
    print  "\n</pre>\n";
    exit();
}

// 1. DB 연결
$conn = oci_connect("scott","tiger", "localhost/lecture");
if (!$conn) p_error("Connection Error");

// 2. 입력값 받아오기 (POST/GET 모두 수용)
// 사진 속 변수명($title, $year 등)과 HTML name 속성을 매칭합니다.
$title = isset($_REQUEST["title"]) ? $_REQUEST["title"] : "";
$year = isset($_REQUEST["year"]) ? $_REQUEST["year"] : "";
$length = isset($_REQUEST["length"]) ? $_REQUEST["length"] : "";
$pno = isset($_REQUEST["pno"]) ? $_REQUEST["pno"] : "";
$sname = isset($_REQUEST["sname"]) ? $_REQUEST["sname"] : "";
$submit = isset($_REQUEST["submit"]) ? $_REQUEST["submit"] : "";

// 버튼 동작 확인 (사진의 if($insert), if($update) 로직을 위한 준비)
$insert = ($submit == "삽입");
$update = ($submit == "갱신");

// 3. [사진 3번 내용] 삽입(Insert) 로직 구현
if($insert) {
    // 사진 3번 68라인: SQL 준비
    $stmt = oci_parse($conn, "insert into movie values (:tt, :yy, :len, 't', :sn, :pno)");
    if (!$stmt) p_error("Parsing Error", $conn);
    
    // 사진 3번 71~74라인: 변수 바인딩
    oci_bind_by_name($stmt, ":tt", $title);
    oci_bind_by_name($stmt, ":yy", $year);
    oci_bind_by_name($stmt, ":len", $length);
    oci_bind_by_name($stmt, ":sn", $sname);
    oci_bind_by_name($stmt, ":pno", $pno);

    // 사진 3번 75라인: 실행
    if (!oci_execute($stmt)) p_error("Insertion Error", $stmt);
    
    // 사진 3번 76라인: 성공 메시지
    print "- Movie($title, $year) 튜플 삽입됨. <br>";
    
    oci_free_statement($stmt);
}

// 4. [사진 2번, 3번 내용] 갱신(Update) 로직 구현
if($update) {
    // 사진 2번 79라인: Update 구문 (PK인 title, year를 조건으로 나머지 수정)
    $stmt = oci_parse($conn, "update movie set length = :len, studioname = :sn, producerno = :pno where title = :tt and year = :yy");
    if (!$stmt) p_error("Parsing Error", $conn);

    // 사진 2번 82~86라인: 변수 바인딩
    oci_bind_by_name($stmt, ":tt", $title);
    oci_bind_by_name($stmt, ":yy", $year);
    oci_bind_by_name($stmt, ":len", $length);
    oci_bind_by_name($stmt, ":sn", $sname);
    oci_bind_by_name($stmt, ":pno", $pno);

    // 실행
    if (!oci_execute($stmt)) p_error("Update Error", $stmt); // 사진엔 "Insertion Error"라 되어있지만 복붙 실수인듯 하여 수정함
    print "- Movie($title, $year) 튜플 갱신 됨. <br>";
    
    oci_free_statement($stmt);
}

// 5. [사진 2번 하단 내용] 검색(Select) 조건 동적 생성
// 사진 2번 90라인 이하: 입력된 값이 있는 경우에만 WHERE절에 추가
$where = "";

if (!empty($title)) {
    // 제목은 포함 검색 (LIKE)
    if (strlen($where) > 0) $where = $where . " and ";
    $where = $where . " title like '%" . $title . "%' "; 
}

if (!empty($year)) {
    if (strlen($where) > 0) $where = $where . " and ";
    $where = $where . " year = " . $year . " ";
}

if (!empty($sname)) {
    if (strlen($where) > 0) $where = $where . " and ";
    $where = $where . " studioname = '" . $sname . "' ";
}

// WHERE 절이 완성되었다면 SQL에 붙이기
$sql = "select title, year, length, studioname, name from movie left outer join movieexec on producerno = certno";

if (!empty($where)) {
    $sql = $sql . " where " . $where;
}

$sql = $sql . " order by 1, 2";

// 6. 결과 조회 및 출력 (기존 코드 유지)
$stmt = oci_parse($conn, $sql);
if (!$stmt) p_error("Parsing Error", $conn);
if (!oci_execute($stmt)) p_error ("Execution Error", $stmt);

print "<TABLE bgcolor=#abbcbabc border=1 cellspacing=2 width=90%>\n";
print "<TR bgcolor=#1ebcbabf align=center><TH> 제목 <TH> 연도 <TH> 상영시간 <TH> 영화사 <th> 제작자 </TR>\n";

$n = oci_fetch_all($stmt, $row);
// oci_fetch_all은 컬럼명 대문자로 키를 반환합니다. (TITLE, YEAR ...)
// 기존 코드의 루프 방식에 맞춰 출력
for ($i=0; $i < $n; $i++) {
    print "<tr> ";
    // $row['TITLE'][$i], $row['YEAR'][$i] 순서로 출력됨
    foreach ($row as $key => $val) {
        // null 값 처리 등을 위해 공백 추가
        print "<td> {$val[$i]} </td>"; 
    }
    print "</tr>\n ";
}
print "</TABLE>\n";

oci_free_statement($stmt);
oci_close($conn);
?>