#extraer conductores con los dias de trabajo q le toca
select *
from users
where id_role = 5
  and id_type_user = 2
  and state_laboral = 1;

/*select ud.id_dia,di.name,di.short_name,us. from usuario_dias ud
LEFT JOIN users us ON us.id=ud.id_conductor JOIN dias di
ON di.id=ud.id_dia*/

#SABER SI A UN CONDUCTOR LE TOCA TRABAJAR HOY
select ud.id_dia, di.name, di.short_name, us.*
from usuario_dias ud
         LEFT JOIN users us ON us.id = ud.id_conductor
         JOIN dias di ON di.id = ud.id_dia
where di.short_name = 'Mon'
  and us.id_role = 5
  and us.state_laboral = 1
  and us.state = 1


#SABER ULTIMO DIA DE SINCRONIZACION DE CONDUCTO PARA SABER SI YA SINCRONIZÓ
#SABAER SI EL DIA DE HOY: EL CONDUCTOR TIENE REGSITRO DE SUEÑO


#00-> CONDUCTORES QUE YA SINCRONIZARON EL DIA DE HOY -->"""PERFECTOOOOO"""
SELECT *
from users us
         left join dreams dr ON dr.id_user = us.id
where date(dr.date_created) = current_date
  and us.id_role = 5
  and dr.exists_data = 1

#00-> only id
Select us.id
from users us
         left join dreams dr ON dr.id_user = us.id
where date(dr.date_created) = current_date
  and us.id_role = 5
  and dr.exists_data = 1

#01-> CONDUCTORES QUE AÚN NO HAN SINCRONIZADO EL DIA DE HOY QUE ANTES YA HABIAN TENIDO REGISTRO EN LA TABLA DREAMS
Select *
from users us
         left join dreams dr ON dr.id_user = us.id
where (us.id_role = 5 and us.id_type_user = 2 and date(dr.date_created) != current_date)

#02-> CONDUCTORES NUEVOS QUE NUNCA REGISTRARON DATOS
SELECT us.*
from users us
         left join dreams dr ON dr.id_user = us.id
where dr.id_user is null
  and us.id_role = 5

#QUITAR LOS CONDUCTORES Q SYNC HOY A LA TALA USER


#POR FIN LO LOGRÉ -> HIJO DE PERRA
# #POR FIN LO LOGRÉ -> HIJO DE PERRA
# #POR FIN LO LOGRÉ -> HIJO DE PERRA
SELECT *
FROM users
WHERE id NOT IN (SELECT us.id
                 FROM users us
                          LEFT JOIN dreams dr ON dr.id_user = us.id
                 WHERE date(dr.date_created) = current_date
                   AND us.id_role = 5
                   AND dr.exists_data = 1)
  AND id_role = 5
  AND id_type_user = 2
  AND token_fcm != '';

# #POR FIN LO LOGRÉ -> HIJO DE PERRA
# #POR FIN LO LOGRÉ -> HIJO DE PERRA
# #POR FIN LO LOGRÉ -> HIJO DE PERRA

#---------------CONDUCTOR NO SINCRONIZÓ + DIAS DE TRABAJO-------------
SELECT ud.id_dia,di.name dia,di.short_name,us.*
FROM users us
LEFT JOIN usuario_dias ud on ud.id_conductor=us.id
LEFT JOIN dias di on di.id=ud.id_dia
WHERE us.id NOT IN (SELECT us.id
FROM users us
LEFT JOIN dreams dr ON dr.id_user = us.id
WHERE date(dr.date_created) = current_date
AND us.id_role = 5
AND dr.exists_data = 1)
AND us.id_role = 5
AND us.id_type_user = 2
AND us.token_fcm != ''
AND di.short_name='Wed';


#-------------------------
select *
from users
where id NOT IN (31, 61)
  and id_role = 5

/*OR OR OR = ES MEJOR USAR "WHERE IN"
WHERE '$currentArtist' IN (e.event_artist1,e.event_artist2,e.event_artist3);*/


#ESTO ES VALIDO
SELECT id, id_role, name
FROM (SELECT us.*
      from users us
               left join dreams dr ON dr.id_user = us.id
      where date(dr.date_created) != current_date
         OR dr.id_user IS NULL)
         AS tpm
WHERE id_role = 5;


#USUARIOS Q NO ESTEN EN LA TABLA DREAMS Y QUE ES CONDUCTOR
Select *
from users us
         left join dreams dr ON dr.id_user = us.id
where dr.id_user is null
  and us.id_role = 5

Select *
from users us
         left join dreams dr ON dr.id_user = us.id


#fecha hoy mysql
select now();
SELECT DATE_FORMAT("2017-06-15", "%Y");

#only date now CONVERTER
SELECT DATE(now());
#only date now
SELECT current_date;

select ud.id_dia, di.name, di.short_name, us.*
from usuario_dias ud
         LEFT JOIN users us ON us.id = ud.id_conductor
         JOIN dreams dr on dr.id_user = us.id
         JOIN dias di ON di.id = ud.id_dia
where di.short_name = 'Mon'
  and us.id_role = 5
  and us.state_laboral = 1
  and us.state = 1
/*ver los dias de trabajo de un conductor*/

/*HOME ULTIMA SESION DE SUEÑO*/
SELECT * FROM users order by id desc limit 1

select current_timestamp()
select now()

SELECT DATE_SUB(current_timestamp(), INTERVAL 1 HOUR);
