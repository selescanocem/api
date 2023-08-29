<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    require '../PHPMailer/src/Exception.php';

    class MailSender{

        private $mail = null;

        public function __construct(){
            //Create a new PHPMailer instance
            $this->mail = new PHPMailer();
            //Tell PHPMailer to use SMTP
            $this->mail->isSMTP();
            //Enable SMTP debugging
            // SMTP::DEBUG_OFF = off (for production use)
            // SMTP::DEBUG_CLIENT = client messages
            // SMTP::DEBUG_SERVER = client and server messages
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
            //Set the hostname of the mail server
            $this->mail->Host = 'a2plcpnl0820.prod.iad2.secureserver.net';
            //Set the SMTP port number - likely to be 25, 465 or 587
            $this->mail->Port = 465;
            //Whether to use SMTP authentication
            $this->mail->SMTPAuth = true;
            // OR use TLS
            $this->mail->SMTPSecure = 'ssl';
            //Username to use for SMTP authentication
            $this->mail->Username = 'sistemas@grupobarboza.com';
            //Password to use for SMTP authentication
            $this->mail->Password = 'sistemasBARBOZA';
            //Set who the message is to be sent from
            $this->mail->setFrom('sistemas@grupobarboza.com', 'Area Sistemas');
            //Set an alternative reply-to address
            $this->mail->addReplyTo('sistemas@grupobarboza.com', 'Area Sistemas');
        }

        public function enviarCorreoContratacion($correos){

            foreach($correos as $nombre => $correo){
                $this->mail->addAddress($correo, $nombre);
                //echo $nombre."=>".$correo."\n";
            }

            /*foreach($datosRequerimiento as $v){
                //echo $v."\n";
            }*/
            $this->mail->Subject = 'Nuevos pagos pendientes';

            $body = file_get_contents('ordenpago.html', FILE_USE_INCLUDE_PATH);
            //echo $body;
            $body = str_replace('%fecha%',date("d-m-Y"), $body);

            $this->mail->msgHTML($body, __DIR__);
            $this->mail->AltBody = 'Mensaje alternativo';

            //send the message, check for errors
            if (!$this->mail->send()) {
                throw new Exception($this->mail->ErrorInfo); 
                return False;
            } else {
                return True;
            }
        }

        public function enviarCorreoRequerimiento($correos, $datosRequerimiento){

            foreach($correos as $nombre => $correo){
                $this->mail->addAddress($correo, $nombre);
                //echo $nombre."=>".$correo."\n";
            }

            /*foreach($datosRequerimiento as $v){
                //echo $v."\n";
            }*/
            $this->mail->Subject = 'Nuevo requerimiento pendiente';

            $body = file_get_contents('requerimiento.html', FILE_USE_INCLUDE_PATH);
            //echo $body;


            $body = str_replace('%residente%',$datosRequerimiento[0], $body);
            $body = str_replace('%fecha%',$datosRequerimiento[1], $body);

            $this->mail->msgHTML($body, __DIR__);
            $this->mail->AltBody = 'Mensaje alternativo';

            //send the message, check for errors
            if (!$this->mail->send()) {
                throw new Exception($this->mail->ErrorInfo); 
                return False;
            } else {
                return True;
            }
        }

    }
?>