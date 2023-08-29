<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();
    

$mail->isSMTP();
//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
// SMTP::DEBUG_CLIENT = client messages
// SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
//Set the hostname of the mail server
$mail->Host = 'mail.grupobarboza.com';
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 587;
//Whether to use SMTP authentication
$mail->SMTPAuth   = true;
//Username to use for SMTP authentication
$mail->Username = 'sistemas@grupobarboza.com';
//Password to use for SMTP authentication
$mail->Password = 'sistemasBARBOZA';
//Set who the message is to be sent from
$mail->setFrom('sistemas@grupobarboza.com', 'Departamento Sistemas');
//Set an alternative reply-to address


$mail->addReplyTo('sistemas@grupobarboza.com', 'Departamento Sistemas');


//Set who the message is to be sent to
$mail->addAddress('brianreyesg@hotmail.com', 'Brian Reyes');
//Set the subject line
$mail->Subject = 'Nuevo requerimiento';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}

?>