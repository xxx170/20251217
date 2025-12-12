DROP TABLE temp CASCADE CONSTRAINTS;
/
DROP TABLE test CASCADE CONSTRAINTS;
/

CREATE TABLE test (
    name    VARCHAR(100) PRIMARY KEY,
    age     NUMBER(3) NOT NULL,
    address VARCHAR(200),
    CHECK(age > 10 AND age < 110)
);
/

CREATE TABLE temp (
    num     NUMBER(3) PRIMARY KEY,
    name    VARCHAR(100) REFERENCES test(name)
);
/

INSERT INTO test VALUES ('H0', 23, '부산시 남구');
INSERT INTO temp VALUES (0, 'H0');

SET SERVEROUTPUT ON;

DECLARE
    TYPE n_type IS TABLE OF test.name%TYPE;
    TYPE a_type IS TABLE OF test.age%TYPE;
    
    test_n  n_type := n_type('H1', 'H2', 'H3', 'H3', 'H4');
    test_a  a_type := a_type(30, NULL, 28, 40, 5);
    temp_n  n_type := n_type();
    
    sql_str  VARCHAR(200) := 'INSERT INTO test VALUES (:1, :2, :3)';
    sql_str1 VARCHAR(200) := 'INSERT INTO temp VALUES (:1, :2)';
    
    -- 사용자 정의 예외 선언
    e_not_null              EXCEPTION;
    e_unique_constraint     EXCEPTION;
    e_check_constraint      EXCEPTION;
    e_parent_key_not_found  EXCEPTION;
    e_child_record_found    EXCEPTION;

    -- Oracle 오류 코드와 예외 연결
    PRAGMA EXCEPTION_INIT(e_not_null, -1400);
    PRAGMA EXCEPTION_INIT(e_unique_constraint, -1);
    PRAGMA EXCEPTION_INIT(e_check_constraint, -2290);
    PRAGMA EXCEPTION_INIT(e_parent_key_not_found, -2291);
    PRAGMA EXCEPTION_INIT(e_child_record_found, -2292);

BEGIN
    temp_n := test_n;
    
    FOR i IN test_n.FIRST..test_n.LAST LOOP
        BEGIN
            EXECUTE IMMEDIATE sql_str 
                USING test_n(i), test_a(i), 
                      DBMS_RANDOM.STRING('x', 5) || ' ' || DBMS_RANDOM.STRING('a', 10);
            EXECUTE IMMEDIATE sql_str1 USING i, temp_n(i);
            
            IF i = test_n.FIRST THEN
                DELETE FROM test WHERE name = test_n(i);
            ELSIF i = 3 THEN
                UPDATE temp SET name = 'H5' WHERE num = 3;
            END IF;
            
            DBMS_OUTPUT.PUT_LINE(i || ' : 작업 성공');

        EXCEPTION
            WHEN e_not_null THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : NOT NULL 제약조건 위반 - age에 NULL 삽입 시도 (에러코드: ' || SQLCODE || ')');
            
            WHEN e_unique_constraint THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : 고유 제약조건 위반 - name이 중복됨 (에러코드: ' || SQLCODE || ')');
            
            WHEN e_check_constraint THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : 체크 제약조건 위반 - age가 10~110 범위를 벗어남 (에러코드: ' || SQLCODE || ')');

            WHEN e_parent_key_not_found THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : 외래키 제약조건 위반 - 참조하는 부모 키가 존재하지 않음 (에러코드: ' || SQLCODE || ')');
            
            WHEN e_child_record_found THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : 자식 레코드 존재 - 참조되는 레코드는 삭제 불가 (에러코드: ' || SQLCODE || ')');
                
            WHEN OTHERS THEN
                DBMS_OUTPUT.PUT_LINE(i || ' : 알 수 없는 오라클 에러 발생 - ' || SQLCODE || ': ' || SQLERRM);
        END;
    END LOOP;
    
    DBMS_OUTPUT.PUT_LINE(LPAD(' ', 50, '*'));
    DBMS_OUTPUT.PUT_LINE('--- TEST 테이블 최종 결과 ---');
    FOR t IN (SELECT * FROM test) LOOP
        DBMS_OUTPUT.PUT_LINE(t.name || ', ' || t.age || ', ' || t.address);
    END LOOP;
    
    DBMS_OUTPUT.PUT_LINE(LPAD(' ', 50, '*'));
    DBMS_OUTPUT.PUT_LINE('--- TEMP 테이블 최종 결과 ---');
    FOR t IN (SELECT * FROM temp) LOOP
        DBMS_OUTPUT.PUT_LINE(t.num || ', ' || t.name);
    END LOOP;
END;
/