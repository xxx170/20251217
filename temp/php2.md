## PHP 관련 (HTML + PHP 연동)

### DB 연결
**설명:**  
Oracle DB에 연결하는 기본 코드입니다. 연결 실패 시 에러 메시지를 출력합니다.  

```php
$conn = oci_connect("scott","tiger","localhost/lecture");
if (!$conn) { echo "Connection Error"; }
```

---

### Insert / Update
**설명:**  
HTML 폼에서 입력받은 값을 Movie 테이블에 삽입하거나 갱신하는 코드입니다. `oci_bind_by_name`으로 변수 바인딩을 합니다.  

```php
if ($submit == "삽입") {
    $stmt = oci_parse($conn, "insert into movie values (:tt,:yy,:len,'t',:sn,:pno)");
    oci_bind_by_name($stmt, ":tt", $title);
    oci_bind_by_name($stmt, ":yy", $year);
    oci_bind_by_name($stmt, ":len", $length);
    oci_bind_by_name($stmt, ":sn", $sname);
    oci_bind_by_name($stmt, ":pno", $pno);
    oci_execute($stmt);
    echo "삽입 완료!";
}

if ($submit == "갱신") {
    $stmt = oci_parse($conn, "update movie set length=:len, studioname=:sn, producerno=:pno where title=:tt and year=:yy");
    oci_bind_by_name($stmt, ":tt", $title);
    oci_bind_by_name($stmt, ":yy", $year);
    oci_bind_by_name($stmt, ":len", $length);
    oci_bind_by_name($stmt, ":sn", $sname);
    oci_bind_by_name($stmt, ":pno", $pno);
    oci_execute($stmt);
    echo "갱신 완료!";
}
```

---

### Select + 출력
**설명:**  
Movie 테이블과 MovieExec 테이블을 조인하여 영화 제목, 연도, 상영시간, 영화사, 제작자 정보를 출력하는 코드입니다. `oci_fetch_all`을 사용합니다.  

```php
$sql = "select title, year, length, studioname, name 
        from movie left join movieexec on producerno=certno";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$n = oci_fetch_all($stmt, $row);
for ($i=0; $i<$n; $i++) {
    echo $row['TITLE'][$i] . " / " . $row['YEAR'][$i] . "<br>";
}
```

---
