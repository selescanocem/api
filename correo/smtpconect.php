<?php
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require '../PHPMailer/src/SMTP.php';
    require '../PHPMailer/src/Exception.php';

    $smtp = new SMTP();

    //Enable connection-level debug output
    $smtp->do_debug = SMTP::DEBUG_CONNECTION;

    try {
        //Connect to an SMTP server
        if (!$smtp->connect('mail.grupobarboza.com', 587)) {
            throw new Exception('Connect failed');
        }
        //Say hello
        if (!$smtp->hello(gethostname())) {
            throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
        }
        //Get the list of ESMTP services the server offers
        $e = $smtp->getServerExtList();
        //If server can do TLS encryption, use it
        /*if (is_array($e) && array_key_exists('STARTTLS', $e)) {
            $tlsok = $smtp->startTLS();
            if (!$tlsok) {
                throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
            }
            //Repeat EHLO after STARTTLS
            if (!$smtp->hello(gethostname())) {
                throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
            }
            //Get new capabilities list, which will usually now include AUTH if it didn't before
            $e = $smtp->getServerExtList();
        }*/
        //If server supports authentication, do it (even if no encryption)
        if (is_array($e) && array_key_exists('AUTH', $e)) {
            if ($smtp->authenticate('sistemas@grupobarboza.com', 'sistemasBARBOZA')) {
                echo 'Connected ok!';
            } else {
                throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
            }
        }
    } catch (Exception $e) {
        echo 'SMTP error: ' . $e->getMessage(), "\n";
    }
    //Whatever happened, close the connection.
    $smtp->quit();

?>