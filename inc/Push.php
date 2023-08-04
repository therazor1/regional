<?php namespace Inc;

class Push
{
    private $key;
    private $tokens = []; // Array de identificadores
    private $topic = ''; // si vamos a enviar por tema
    private $type = ''; // Tipo de la notificacion (para enviar a la pantalla correcta)
    private $title = ''; // Titulo de la notificacion
    private $body = ''; // Contenido de notificacion
    private $data = [];
    public $status_message = '';

    /**
     * Push constructor.
     */
    public function __construct()
    {
        //TODO: La clave del server: viene de DB (table settings)
        $this->key = stg('key_firebase');
    }

    /**
     * @param array|string $token
     * @return $this
     */
    public function token($token)
    {
        if (is_array($token)) {
            $this->tokens = $token;
        } else {
            $this->tokens[] = $token;;
        }
        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param $body
     * @return $this
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Enviar
     * @return bool
     */
    /*FLUTTER*/
    public function send2()
    {
        if (empty($this->tokens) && empty($this->topic)) {
            $this->status_message = 'No hay tokens FCM';
            return false;
        } else {

            $post = [];

            if (empty($this->topic)) {
                $post['registration_ids'] = $this->tokens;
            } else {
                $post['to'] = '/topics/' . $this->topic;
            }

            if ($this->body) {
                $post['notification'] = [
                    'body'  => $this->body,
                    'title' => $this->title,
                ];
            }

            if ($this->type) {
                $this->data['type'] = $this->type;
            }

            if ($this->data) {
                $post['data'] = $this->data;
            }

            $post['data']['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            $headers = [
                'Authorization: key=' . $this->key,
                'Content-Type: application/json'
            ];

            $ch = curl_init(); // Inicializar curl
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send'); // URL de peticion
            curl_setopt($ch, CURLOPT_POST, true); // Metodo POST
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Data en formato JSON
            $result = curl_exec($ch); // Enviar peticon actual

            if (curl_errno($ch)) {
                $this->status_message = curl_error($ch); // Error
            } else {
                $this->status_message = $result;
            }

            curl_close($ch); // Cerrar curl

            $this->status_message = $result; // Respuesta Firebase

            $result_obj = @json_decode($this->status_message);

            return @$result_obj->success >= 1;
        }
    }

    /*ANDROID*/
    public function send(?array $message = null)
    {
        // Al no haber tokens
        if (count($this->tokens) == 0) {
            $this->status_txt = 'No Tokens';
            return;
        }

        $msg = [
            'type'    => $this->type,
            'title'   => $this->title,
        ];

        // Post DATA
        $post = [];
        $post['registration_ids'] = $this->tokens;
        $post['data'] = [
            'message' => json_encode($msg)
        ];

        /*aquí esta la clave*/
        if ($this->body) {
            $post['notification'] = [
               'body'  => $this->body,
               'title' => $this->title
            ];
        }

        if ($message) {
            $post = array_merge($post, $message);
        }

        // CURL Headers
        $headers = array(
            'Authorization: key=' . $this->key,
            'Content-Type: application/json'
        );

        $ch = curl_init(); // Inicializar curl
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send'); // URL de peticion
        curl_setopt($ch, CURLOPT_POST, true); // Metodo POST
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //ACEPTAR HTTP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post)); // Data en formato JSON
        $result = curl_exec($ch); // Enviar peticon actual

        if (curl_errno($ch)) {
            $this->status_txt = curl_error($ch); // Error
        }

        curl_close($ch); // Cerrar curl

        $this->status = true;
        $this->status_txt = $result; // Respuesta Firebase

        //exit($result);
    }


    # HELPERS
    /**
     * @return Rsp
     */
    public function sendRSP()
    {
        if ($this->send()) {
            return rsp(true);
        } else {
            return rsp('Error al enviar');
        }
    }

    /**
     * @param string|array $token
     * @return Push
     */
    public static function to($token)
    {
        $ins = new Push();
        $ins->token($token);
        return $ins;
    }

    /**
     * Envío rápido
     * @param $token
     * @param $body
     * @param string $type
     * @return bool
     */
    public static function go($token, $body, $type = '')
    {
        $ins = new Push();
        $ins->token($token);
        $ins->body($body);
        $ins->type($type);
        return $ins->send();
    }
}