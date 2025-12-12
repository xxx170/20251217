SET SERVEROUTPUT ON;

DECLARE
    -- Studio 테이블의 모든 스튜디오 정보
    CURSOR studio_cursor IS
        SELECT s.name AS studio_name, s.address, e.name AS pres_name
        FROM Studio s
        JOIN MovieExec e ON s.presno = e.certno;

    v_budget      NUMBER;
    v_salary      NUMBER;
    v_cont_period NUMBER;
    v_num_actors  NUMBER;
    v_actor_name  MovieStar.name%TYPE;

BEGIN
    FOR studio_rec IN studio_cursor 
    LOOP
        -- StudioInfo 테이블에 기본 정보 삽입
        INSERT INTO StudioInfo (name, address, president, movies, stars)
        VALUES (studio_rec.studio_name, studio_rec.address, studio_rec.pres_name, 
                movie_tab(), star_tab());

        -- 해당 스튜디오가 제작한 영화 삽입
        FOR movie_rec IN (SELECT m.title, m.year, e.name AS producer_name
                          FROM Movie m
                          JOIN MovieExec e ON m.producerno = e.certno
                          WHERE m.studioname = studio_rec.studio_name)
        LOOP
            v_budget := TRUNC(DBMS_RANDOM.VALUE(10000000, 1000000000));
            
            INSERT INTO TABLE(SELECT movies FROM StudioInfo WHERE name = studio_rec.studio_name)
            VALUES (mv_ty(movie_rec.title, movie_rec.year, v_budget, movie_rec.producer_name));
        END LOOP;

        -- 랜덤한 수의 배우 선택 (5~15명)
        v_num_actors := TRUNC(DBMS_RANDOM.VALUE(5, 16));
        
        FOR i IN 1..v_num_actors 
        LOOP
            BEGIN
                -- MovieStar 테이블에서 랜덤 배우 선택
                SELECT name INTO v_actor_name
                FROM (SELECT name FROM MovieStar ORDER BY DBMS_RANDOM.VALUE)
                WHERE ROWNUM = 1;

                v_salary := TRUNC(DBMS_RANDOM.VALUE(500000, 50000000));
                v_cont_period := TRUNC(DBMS_RANDOM.VALUE(1, 11));

                INSERT INTO TABLE(SELECT stars FROM StudioInfo WHERE name = studio_rec.studio_name)
                VALUES (star_ty(v_actor_name, v_salary, v_cont_period));
            
            EXCEPTION
                WHEN DUP_VAL_ON_INDEX THEN
                    NULL;
            END;
        END LOOP;
    END LOOP;

    COMMIT;
    DBMS_OUTPUT.PUT_LINE('StudioInfo 데이터 삽입 완료');

EXCEPTION
    WHEN OTHERS THEN
        DBMS_OUTPUT.PUT_LINE('오류 발생: ' || SQLCODE || ' - ' || SQLERRM);
        ROLLBACK;
END;
/