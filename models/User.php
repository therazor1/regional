<?php namespace Models;

use Inc\Bases\BaseModel;
use Inc\Mailer;
use Inc\STG;
use Inc\Util;
use Libs\Pixie\QB;
use Libs\Pixie\Raw;

class User extends BaseModel
{

    const TYPE_OPERATOR = 1;
    const TYPE_COLABORADOR = 2;

    const TYPE_PROVIDER = '2'; # usuario de proveedor

    //constantes globales roles del sistema
    const ROLE_SUPERVISOR = 4;
    const ROLE_CONDUCTOR = 5;

    const _STATE_UNVERIFIED = '3';

    const CLAVE_GENERICA = 'adem:1n5';

    public $id = 0;
    public $id_role;
    public $id_type_user;
    public $id_tipo_documento;
    public $id_emp_transporte;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $document;
    public $id_fitbit;
    public $token_fitbit;
    public $rtoken_fitbit;
    protected $password; // Protegido, no se imprime

    public $id_tipo_salida;
    public $state_laboral;
    public $escolta;
    public $token;
    public $token_fcm;
    public $pic;
    public $state;
    public $date_created;
    public $date_updated;
    public $date_deleted;
    public $recover_app;
    public $emal_huami;
    public $password_huami;
    public $so_celular;
    public $marca_modelo_celular;

    public $ro_name;
    public $ro_menu_collapsed;
    public $ro_id_module;
    public $id_proveedor;

    //protected $hidden = ['password'];

    public function root()
    {
        return $this->ro_name === 'Root';
    }

    public function password()
    {
        return $this->password;
    }

    public function fullName()
    {
        return $this->name . ' ' . $this->surname;
    }

    public function delete($forever = false)
    {
        $this->data('email', 'deleted:' . $this->email);
        return parent::delete($forever);
    }

    public function baseDir()
    {
        //return $this->id_type_user == User::TYPE_PROVIDER ? '/provider' : '';
        return '';
    }

    public function tieneClaveGenerica()
    {
        return $this->password() == md5(self::CLAVE_GENERICA);
    }

    /**
     * Obteneer la url del cms segun tipo de usuario
     * @return string
     */
    public function url()
    {
        /*if ($this->id_type_user == User::TYPE_PROVIDER) {
            return STG::ins()->url_proveedores;
        } else {
            return URL_WEB;
        }*/
        return URL_WEB;
    }

    public function correoUsuarioCreado()
    {
        # si es nuevo enviamos un correo para crear una contraseña
        $token = new Token();
        $token->data('id_user', $this->id);
        $token->data('type', Token::TYPE_RECOVER);
        $token->data('token', Util::token($this->email));
        $token->data('date_expiration', Raw::dateAdd(999, 'HOUR'));
        if ($token->create()) {
            $url = $this->url() . '/recover_password/' . $token->token;
            if (Mailer::usuarioCreado($this->name, $this->email, $url)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return static
     */
    public static function get($id)
    {
        $qb = QB::table('users us');
        $qb->select(
            'ud.*',
            'tu.name tu_name');
        $qb->where(self::ID, '=', $id);
        $qb->join('type_users tu', 'tu.id', '=', 'us.id_type_user');

        $qb->asObject(User::class);

        if ($item = $qb->first()) {
            return $item;
        } else {
            return new static;
        }
    }

    public static function typeText($type)
    {
        switch ($type) {
            case self::TYPE_OPERATOR:
                return 'Operador';
            case self::TYPE_PROVIDER:
                return 'Proveedor';
            default:
                return '';
        }
    }

    public static function has($id_type_user, $id, $column, $value)
    {
        return QB::table('users')
            ->where('id_type_user', $id_type_user)
            ->where('id', '!=', $id)
            ->where($column, $value)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }

    public static function existEmail($email, $id = '0')
    {
        return QB::table('users')
            ->where('id', '!=', $id)
            ->where('email', $email)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }

    public static function existNameSurname($name, $surname, $id = '0')
    {
        return QB::table('users')
            ->where('id', '!=', $id)
            ->where('name', $name)
            ->where('surname', $surname)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }

    public static function existIdFitbit($id_fitbit, $id = '0'){
        return QB::table('users')
            ->where('id', "!=" , $id)
            ->where('id_fitbit', $id_fitbit)
            ->where('state', '!=', self::_STATE_DELETED)
            ->first();
    }

    public static function escoltaObj($escolta)
    {
        if ($escolta ==1) {
            return new State(self::COLOR_VERDE, 'SI');
        } else if ($escolta ==0) {
            return new State(self::COLOR_ROJO, 'NO');
        }
    }

    public static function state_laboraObj($state)
    {
        if ($state ==1) {
            return new State(self::COLOR_VERDE, 'ACTIVO');
        } else if ($state ==0) {
            return new State(self::COLOR_ROJO, 'INACTIVO');
        }
    }

    //GET COLABORADORES
    public static function getColabApp()
    {
        $qb = QB::table('users')
            ->where('id_role', User::ROLE_CONDUCTOR)
            ->where('state', User::_STATE_ENABLED)
            ->get();

        return $qb;
    }


}
