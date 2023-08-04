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