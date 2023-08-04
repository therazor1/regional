<?php namespace Inc\Bases;

use Inc\Mailer;
use Inc\Rsp;
use Inc\STG;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Smarty;

class BaseMailer
{
    public static $show = false; # no se envia, se muestra

    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    protected $attach = [];
    protected $subject;
    protected $content;
    protected $from_email;
    protected $from_name;

    protected $error_message = '';

    protected $enabled = true; # enviar correo

    const SEND_TO_ALL = !DEBUG; # si envia a todos o solo a interno de prubas, debe estar en true en produccion

    public function __construct()
    {
    }

    public function to($email, $name = '')
    {
        $this->to[] = [
            'email' => $email,
            'name'  => $name,
        ];
        return $this;
    }

    public function tos($emails)
    {
        foreach ($emails as $email) {
            $this->to($email);
        }
        return $this;
    }

    public function cc($email, $name = '')
    {
        $this->cc[] = [
            'email' => $email,
            'name'  => $name,
        ];
        return $this;
    }

    public function bcc($email, $name = '')
    {
        $this->bcc[] = [
            'email' => $email,
            'name'  => $name,
        ];
        return $this;
    }

    public function attach($path, $name = '')
    {
        $this->attach[] = [
            'path' => $path,
            'name' => $name,
        ];
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function content($content)
    {
        $this->content = $content;
        return $this;
    }

    public function from($email, $name = '')
    {
        $this->from_email = $email;
        $this->from_name = $name;
        return $this;
    }

    public function view($view, $data)
    {
        $smarty = self::getBaseSmarty($data);

        $this->content($smarty->fetch('emails/' . $view . '.tpl'));

        return $this;
    }

    private function emailHuman($items)
    {
        return implode(',', array_map(function ($a) {
            return $a['email'] . ($a['name'] ? ' (' . $a['name'] . ')' : '');
        }, $items));
    }

    public function send($email = null, $name = '')
    {
        if ($email)
            $this->to($email, $name);

        if (!$this->from_email) {
            $this->from(stg('mail_sender'), stg('brand'));
        }

        if (static::$show)
            exit(
                '<style>html,body{margin: 0;padding: 0;border:0}</style>'
                . '<div style="background:#34363A;padding:4px;color:white;font:12px \'Courier New\'">'
                . '<span style="color:gray">Subject:</span> ' . $this->subject
                . '<br><span style="color:gray">From:</span> ' . $this->from_email . ' (' . $this->from_name . ')'
                . '<br><span style="color:gray">To:</span> ' . $this->emailHuman($this->to)
                . (empty($this->cc) ? '' : '<br><span style="color:gray">CC:</span> ' . $this->emailHuman($this->cc))
                . '<br><span style="color:gray">BCC:</span> ' . $this->emailHuman($this->bcc)
                . (empty($this->attach) ? '' :
                    '<br><span style="color:gray">Attachments:</span> '
                    . implode(',', array_column($this->attach, 'name')))
                . ($this->enabled ? '' : '
                    <br><span style="background:red;color:white;padding:0 4px">Deshabilitado</span>
                ')
                . '</div>'
                . $this->content
            );

        if (!$this->enabled) {
            $this->error_message = 'Correo deshabilitado';
            return false;
        }

        $mail = self::get();

        if ($this->from_email) {
            try {
                $mail->setFrom($this->from_email, $this->from_name);
            } catch (Exception $e) {
            }
        }

        if ($mail_bcc = stg('mail_bcc')) {
            $email_bccs = explod($mail_bcc);
            foreach ($email_bccs as $email) {
                $mail->addBCC($email);
                #$this->bcc($email);
            }
        }

        # solo en produccion enviar a los destinos reales
        if (static::SEND_TO_ALL) {

            if ($this->to) {
                foreach ($this->to as $item) {
                    $mail->addAddress($item['email'], $item['name']);
                }
            }

            if ($this->cc) {
                foreach ($this->cc as $item) {
                    $mail->addCC($item['email'], $item['name']);
                }
            }

            if ($this->bcc) {
                foreach ($this->bcc as $item) {
                    $mail->addBCC($item['email'], $item['name']);
                }
            }

        }

        if ($this->attach) {
            foreach ($this->attach as $item) {
                try {
                    $mail->addAttachment($item['path'], $item['name']);
                } catch (Exception $e) {
                }
            }
        }

        $mail->Subject = (DEBUG ? '[BETA] ' : '') . $this->subject;
        $mail->msgHTML($this->content);

        try {
            return $mail->send();
        } catch (Exception $e) {
            $this->error_message = $e->errorMessage();
            return false;
        }
    }


    # HELPERS
    public static function ins($email = null, $name = '')
    {
        $ins = new Mailer();

        if ($email)
            $ins->to($email, $name);

        return $ins;
    }

    static public function v($view, $data)
    {
        return self::ins()->view($view, $data);
    }

    public static function getBaseSmarty($vars)
    {
        $smarty = new Smarty;
        $smarty->setCompileDir('libs/smarty/templates_c');
        $smarty->setCacheDir('libs/smarty/cache');
        $smarty->setTemplateDir('views');
        foreach ($vars as $k => $v) {
            $smarty->assign($k, $v);
        }
        $smarty->assign('stg', STG::all());
        return $smarty;
    }

    public function sendRSP()
    {
        if ($this->send()) {
            return Rsp::ok();
        } else {
            return rsp($this->error_message);
        }
    }

    # Configurar Mailer

    public static function get()
    {
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        #$mail->SMTPDebug = 2;
        #$mail->Debugoutput = 'html';

        if (STG::bool('mail_auth')) {
            $mail->isSMTP();
            $mail->Host = stg('mail_host');
            $mail->SMTPAuth = true;
            $mail->Username = stg('mail_username');
            $mail->Password = stg('mail_password');
        }

        return $mail;
    }

}