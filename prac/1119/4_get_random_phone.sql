create or replace function get_phone return varchar2
is
begin
    return '010-'||trunc(dbms_random.value(1000,9999))||'-'||trunc(dbms_random.value(1000,9999));
end;