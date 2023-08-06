# Sistema

Guía para la configuracion de procesos y/o servicios

## Instalación

- Configurar variables de entorno
    - Hay un ejemplo de este archivo en `/inc/__ENV.sample.php`, sacar copia y nombrarlo `/inc/__ENV.php` y
      configurarlo.
    - Completar las constantes de base de datos y otros
    - la constante `DEBUG` dejarlo en **true** si estamos trabajando en modo depuración o entorno de pruebas, esto
      evita que se ejecuten ciertos procesos como el envío de correos a usuarios reales
- Importar estructura de base de datos
    - La última base de datos se encuentra en **/resources/database/[nombre_y_timestamp].sql**
- Ajustes
    - Verificar la casilla `MySQL Backups` para crear un archivo de copia de seguridad automática de base de datos,
      pero esto no es suficiente, en el siguiente paso habilitaremos el cron job para la ejecución periódica
    - Habilitar todas las casillas de **Cron Jobs**, estos se configuran en el archivo `/inc/installer/Installer.php`
    - Desde la tabla `settings` configuramos la zona horaria en el campo `timezone`


-Links:
  Video : https://onedrive.live.com/?authkey=%21ADJbXPP6lqjbHm4&id=47D2B0B62DA49D26%21780508&cid=47D2B0B62DA49D26&parId=root&parQt=sharedby&o=OneUp

  Excel: https://invitrope-my.sharepoint.com/:x:/g/personal/e_quispe_invitro_pe/Edvy3FCVMetMi_N_0x5He7YB4D1epOWhDdquZ46nIceWcw?rtime=hvkTEY6W20g

  XD: https://xd.adobe.com/view/73208b31-3f3d-4372-8913-d03097e7e391-2673/screen/fe3668a8-82cd-44ef-8290-6709df03c6dd/

  const DB_HOST = '45.132.157.52';
  const DB_USER = 'u954616314_regional_game';
  const DB_PASS = 'P&km5rs3';
  const DB_NAME = 'u954616314_regional_game';
