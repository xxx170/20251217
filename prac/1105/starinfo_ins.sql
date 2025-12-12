declare
    cursor csr is select * from moviestar;
    cursor s_csr(n moviestar.name%type) is
        select * from starsin where starname = n;
    cursor p_csr(n moviestar.name%type) is
        select title, year from movie, movieexec
        where producerno = certno and name = n;
    st_ins varchar2(200) := 'insert into star_info values (
        :1, :2, :3, smov_tab(),pmov_tab())';
    smov_ins varchar2(200) := 'insert into 
        table(select s_movies from star_info 
        where name = :1)
        values (smov_ty(:2,:3,:4))';
    pmov_ins varchar2(200) := 'insert into 
        table(select p_movies from star_info 
        where name = :1)
        values (pmov_ty(:2,:3,:4))';        
begin
    
end;