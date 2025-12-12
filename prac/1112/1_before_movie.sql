create or replace trigger before_movie
before insert or update or delete on movie
for each row
declare
  pragma autonomous_transaction;
  cnt int;
begin
  if deleting then
    select count(*) into cnt from movie
    where studioname = :old.studioname;
    if cnt = 1 then
      update studio
      set address = '[DEL]'||address
      where name = :old.studioname;
    end if;
  else
    if :new.length is null then
      select avg(length) into :new.length
      from movie;
    elsif :new.length < :old.length then
      :new.length := :old.length;
    end if;
    if :new.studioname is null then
      select name into :new.studioname from 
        (select * from studio order by dbms_random.value)
      where rownum = 1;
    else
      select count(*) into cnt from studio
      where name = :new.studioname;
      if cnt = 0 then
        insert into studio(name) values (:new.studioname);
      end if;
    end if;
    if :new.producerno is null then
      select certno into :new.producerno
      from (select * from movieexec
       where certno not in (select producerno from movie)
       order by dbms_random.value)
      where rownum = 1;
    end if;
  end if;
  commit;
end;








