<?php namespace Inc\installer;

use Inc\Util;
use phpseclib\Net\SSH2;

class Installer
{
    private SSH2 $ssh;

    private static Installer $_ins;

    public static function ins()
    {
        if (!isset(self::$_ins))
            self::$_ins = new Installer();
        return self::$_ins;
    }

    private function ssh(): SSH2
    {
        if (!isset($this->ssh)) {
            $this->ssh = new SSH2(SSH_IP);
            $this->ssh->login(SSH_USER, SSH_PASS);
        }
        return $this->ssh;
    }

    public function verificar()
    {
        $crons = $this->crons();
        $crons_instalados = true;

        foreach ($crons as $cron) {
            if (!$this->existeCron($cron)) {
                $crons_instalados = false;
                break;
            }
        }

        # verificar crons
        return [
            'copias_de_seguridad_creado' => file_exists($this->pathCodeBackupsMysql()),
            'crons_instalados'           => $crons_instalados,
        ];
    }

    public function verificarCrons()
    {
        $crons = $this->crons();

        foreach ($crons as $i => $cron) {
            $cron->id = $i;
            $cron->instalado = $this->existeCron($cron);
        }

        return $crons;
    }

    private function existeCron(Cron $cron)
    {
        $crons = $this->leerCrons();
        return in_array($cron->build(), $crons);
    }

    # crons para instalar
    public function crons()
    {
        return [
            # copias de seguridad mysql, cada 3 dias a las 11 pm hora peruana (-4)
            new Cron('0', '3', '*/3', '*', '*', '/bin/bash ' . $this->pathCodeBackupsMysql()),
            # todos los dias a las 9h (-4)
            new Cron('0', '13', '*', '*', '*', 'curl ' . URL_API . '/crons/daily'),
            # todos los dias a las 18h (-4)
            new Cron('0', '22', '*', '*', '*', 'curl ' . URL_API . '/crons/daily'),
        ];
    }

    public function instalarCrons()
    {
        $crons = $this->crons();

        $instalados = [];

        $crons_instalados = $this->leerCrons();

        foreach ($crons as $cron) {
            $cron_line = $cron->build();
            if (!in_array($cron_line, $crons_instalados)) {
                $crons_instalados[] = $cron_line;
                $instalados[] = $cron;
            }
        }

        $str_nuevos_crons = implode(PHP_EOL, $crons_instalados);

        $this->ssh()->exec('echo -e "' . $str_nuevos_crons . '" | crontab -');

        return $instalados;
    }

    public function instalarCron(Cron $cron)
    {
        $crons_instalados = $this->leerCrons();

        if (in_array($cron->build(), $crons_instalados)) {
            return false;
        } else {
            $crons_instalados[] = $cron->build();
            $str_nuevos_crons = implode(PHP_EOL, $crons_instalados);
            $this->ssh()->exec('echo -e "' . $str_nuevos_crons . '" | crontab -');
            return true;
        }
    }

    public function eliminarCron(Cron $cron)
    {
        $crons_instalados = $this->leerCrons();

        $crons_nuevos = array_filter($crons_instalados, fn($item) => $item != $cron->build());
        $str_nuevos_crons = implode(PHP_EOL, $crons_nuevos);

        $this->ssh()->exec('echo -e "' . $str_nuevos_crons . '" | crontab -');
    }

    public function pathCodeBackupsMysql()
    {
        return uploads() . '/mysql_backups.sh';
    }

    public function pathMysqlBackups()
    {
        return '/var/www/backups';
    }

    public function leerCrons()
    {
        $crontab = $this->ssh()->exec('crontab -l');
        $items = explod($crontab, PHP_EOL);
        $items = array_filter($items, fn($line) => !empty($line));
        return array_values($items);
    }

    public function codigoBackupsConfigurado()
    {
        $path = $this->pathCodeBackupsMysql();
        return file_exists($path);
    }

    public function crearCopiasDeSeguridad()
    {
        $path = $this->pathCodeBackupsMysql();

        $content = '#!/bin/bash

# ajustes de sistema
BACKUP=' . $this->pathMysqlBackups() . '
NOW_DATE=$(date +"%Y-%m-%d")
NOW_TIME=$(date +"%H-%M-%S")
WEEK_OF_MONTH=$((($(date +%-d)-1)/7+1))

# ajustes MySQL

MYSQL_USR="' . DB_USER . '"
MYSQL_PWD="' . DB_PASS . '"
MYSQL_HST="' . DB_HOST . '"
MYSQL="$(which mysql)"
MYSQLDUMP="$(which mysqldump)"
GZIP="$(which gzip)"

# nombre de la base de datos
DB_NAME="' . DB_NAME . '"

# ajustes FTP

[ ! -d $BACKUP ] && mkdir -p $BACKUP || :

# iniciar copia de seguridad MySQL
# obtener todos los nombres de base de datos, excepto los que vienen por defecto

SQL="SELECT schema_name FROM INFORMATION_SCHEMA.SCHEMATA WHERE schema_name = \'"$DB_NAME"\'"

DBS="$($MYSQL -u $MYSQL_USR -h $MYSQL_HST -p$MYSQL_PWD -Bse "$SQL")"
for db in $DBS; do

  # crear carpeta si no existe
  DB_DIR=$BACKUP/$db
  MONTH_DIR=$DB_DIR/$(date +"%Y-%m")
  WEEK_DIR=$MONTH_DIR/week-$WEEK_OF_MONTH
  mkdir -p $DB_DIR
  mkdir -p $MONTH_DIR
  mkdir -p $WEEK_DIR

  FILE=$WEEK_DIR/$db"_"$NOW_DATE"_"$NOW_TIME.gz

  echo "$FILE"

  $MYSQLDUMP --single-transaction -u $MYSQL_USR -h $MYSQL_HST -p$MYSQL_PWD $db | $GZIP -9 >$FILE
done';
        $fp = fopen($path, 'wb');
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }

    function getDirContents($dir, &$results = array(), Fildir $last_dir = null)
    {
        if (is_dir($dir)) {
            $files = scandir($dir, SCANDIR_SORT_NONE);

            foreach ($files as $value) {
                $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
                if (!is_dir($path)) {

                    $fildir = new Fildir(Fildir::TYPE_FILE);
                    $fildir->name = $value;
                    $fildir->path = $path;
                    $fildir->size = filesize($path);

                    if (!is_null($last_dir) && str_contains($path, '/' . $last_dir->name . '/')) {
                        $last_dir->size += $fildir->size;
                        $last_dir->items[] = $fildir;
                    } else {
                        $results[] = $fildir;
                    }

                } else if ($value != "." && $value != "..") {

                    $fildir = new Fildir(Fildir::TYPE_DIR);
                    $fildir->name = $value;
                    $fildir->path = $path;
                    $fildir->size = 0;
                    $fildir->items = [];

                    if (!is_null($last_dir) && str_contains($path, '/' . $last_dir->name . '/')) {
                        $last_dir->items[] = $fildir;
                    } else {
                        $results[] = $fildir;
                    }

                    $this->getDirContents($path, $results, $fildir);
                }
            }
        }

        return $results;
    }

    public function leerCopiasDeSeguridad()
    {
        $fildir = new Fildir(Fildir::TYPE_DIR);
        $fildir->name = DB_NAME;
        $fildir->path = $this->pathMysqlBackups() . '/' . DB_NAME;
        $fildir->size = 0;
        $fildir->items = [];

        $path = $this->pathMysqlBackups() . '/' . DB_NAME;
        return $this->getDirContents($path);
    }

}