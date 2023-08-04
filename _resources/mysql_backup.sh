#!/bin/bash
# Copias de seguridad MySQL

# ***** PASOS *****
# - hacer ejecutable
# chmod +x /var/www/html/swis.sarcc.pe/api/resources/mysql_backup.sh
# - editar archivo de crons
# crontab -e
# - crontab cada 3 dias a las 2am
# 2 0 */3 * * /bin/bash /var/www/html/ohl_live/api/resources/mysql_backup.sh

# - reiniciar servicio cron
# sudo service crond restart

# ajustes de sistema
BACKUP=/home/backups
NOW_DATE=$(date +"%Y-%m-%d")
NOW_TIME=$(date +"%H-%M-%S")

# ajustes MySQL

MYSQL_USR="root"
MYSQL_PWD="6bnmgyAS8bPLSvMp"
MYSQL_HST="localhost"
MYSQL="$(which mysql)"
MYSQLDUMP="$(which mysqldump)"
GZIP="$(which gzip)"

# nombre de la base de datos
DB_NAME="swis"

# ajustes FTP

[ ! -d $BACKUP ] && mkdir -p $BACKUP || :

# iniciar copia de seguridad MySQL
# obtener todos los nombres de base de datos, excepto los que vienen por defecto

SQL="SELECT schema_name FROM INFORMATION_SCHEMA.SCHEMATA WHERE schema_name = '"$DB_NAME"'"

DBS="$($MYSQL -u $MYSQL_USR -h $MYSQL_HST -p$MYSQL_PWD -Bse "$SQL")"
for db in $DBS; do

  # crear carpeta si no existe
  DB_DIR=$BACKUP/$db/$NOW_DATE
  mkdir -p $BACKUP/$db
  mkdir -p $BACKUP/$db/$NOW_DATE

  FILE=$DB_DIR/$db"_"$NOW_DATE"_"$NOW_TIME.gz

  echo "$FILE"

  $MYSQLDUMP --single-transaction -u $MYSQL_USR -h $MYSQL_HST -p$MYSQL_PWD $db | $GZIP -9 >$FILE
done