create or replace function get_addr(ty char) return varchar2
is
    type s_ty is table of varchar2(100);
    city_tab    s_ty := s_ty('부산','대전','울산','경주','서울','인천','양산');
    gu_tab      s_ty := s_ty('동','서','남','북','중','수영','동작');
    dong_tab    s_ty := s_ty('대연','대방','용당','감만','용호','대잠','연제');
begin
    case
        when ty = 'city' then return city_tab(dbms_random.value(1,city_tab.count))||'시';
        when ty = 'gu' then return gu_tab(dbms_random.value(1,gu_tab.count))||'구';
        when ty = 'dong' then return dong_tab(dbms_random.value(1,dong_tab.count))||'동';
    end case;
exception
    when case_not_found then
        return 
            city_tab(dbms_random.value(1,city_tab.count))||'시 '||
            gu_tab(dbms_random.value(1,gu_tab.count))||'구 '||
            dong_tab(dbms_random.value(1,dong_tab.count))||'동';
end;