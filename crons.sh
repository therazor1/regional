
# Todos los dias a las 4 am: push a los conductores q aún no han sincronizado (+5) horaUTC
0 9 * * * curl https://primaxdreams.focusit.pe/api/api/crons/cron_dreams_6veces_diario
15 9 * * * curl https://primaxdreams.focusit.pe/api/api/crons/cron_dreams_6veces_diario
30 9 * * * curl https://primaxdreams.focusit.pe/api/api/crons/cron_dreams_6veces_diario

#test saber si funciona 2 min
*/2 * * * * curl https://primaxdreams.focusit.pe/api/api/crons/cron_dreams_6veces_diario

