SET SERVEROUTPUT ON;

DECLARE
    -- 모든 영화 임원 정보
    CURSOR exec_cursor IS
        SELECT certno, name, address, networth 
        FROM MovieExec;

    v_contract_date DATE;
    v_salary        NUMBER;
    v_emp_count     NUMBER;
    v_year_offset   NUMBER;
    v_month         NUMBER;
    v_day           NUMBER;

BEGIN
    FOR exec_rec IN exec_cursor 
    LOOP
        -- MovieExecInfo 테이블에 기본 정보 삽입
        INSERT INTO MovieExecInfo (name, address, networth, movies, studios)
        VALUES (exec_rec.name, exec_rec.address, exec_rec.networth, 
                movie_tab(), studio_tab());

        -- 임원이 제작한 영화 정보 삽입
        FOR movie_rec IN (SELECT title, year FROM Movie WHERE producerno = exec_rec.certno)
        LOOP
            v_salary := TRUNC(DBMS_RANDOM.VALUE(1000000, 100000000));
            
            -- 계약일자: 영화 개봉 1~3년 전의 랜덤 날짜
            v_year_offset := TRUNC(DBMS_RANDOM.VALUE(1, 4));
            v_month := TRUNC(DBMS_RANDOM.VALUE(1, 13));
            v_day := TRUNC(DBMS_RANDOM.VALUE(1, 29));
            
            v_contract_date := TO_DATE(
                (movie_rec.year - v_year_offset) || 
                LPAD(v_month, 2, '0') || 
                LPAD(v_day, 2, '0'), 
                'YYYYMMDD'
            );

            INSERT INTO TABLE(SELECT movies FROM MovieExecInfo WHERE name = exec_rec.name)
            VALUES (movie_ty(movie_rec.title, movie_rec.year, v_contract_date, v_salary));
        END LOOP;

        -- 임원이 운영하는 스튜디오 정보 삽입
        FOR studio_rec IN (SELECT name FROM Studio WHERE presno = exec_rec.certno)
        LOOP
            v_emp_count := TRUNC(DBMS_RANDOM.VALUE(50, 5001));
            
            INSERT INTO TABLE(SELECT studios FROM MovieExecInfo WHERE name = exec_rec.name)
            VALUES (studio_ty(studio_rec.name, v_emp_count));
        END LOOP;

    END LOOP;

    COMMIT;
    DBMS_OUTPUT.PUT_LINE('MovieExecInfo 데이터 삽입 완료');

EXCEPTION
    WHEN OTHERS THEN
        DBMS_OUTPUT.PUT_LINE('오류 발생: ' || SQLCODE || ' - ' || SQLERRM);
        ROLLBACK;
END;
/