<?php

/**
 * This example shows making an SMTP connection with authentication.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
// SMTP::DEBUG_CLIENT = client messages
// SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_OFF;
//Set the hostname of the mail server
$mail->Host = 'a2plcpnl0820.prod.iad2.secureserver.net';
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 465;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
// OR use TLS
$mail->SMTPSecure = 'ssl';
//Username to use for SMTP authentication
$mail->Username = 'sistemas@grupobarboza.com';
//Password to use for SMTP authentication
$mail->Password = 'sistemasBARBOZA';
//Set who the message is to be sent from
$mail->setFrom('sistemas@grupobarboza.com', 'Area Sistemas');
//Set an alternative reply-to address
$mail->addReplyTo('sistemas@grupobarboza.com', 'Area Sistemas');
//Set who the message is to be sent to
$mail->addAddress('brianreyesg@hotmail.com', 'Brian Reyes');
//Set the subject line
$mail->Subject = 'Correo SMTP test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'Mensaje alternativo';


//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}