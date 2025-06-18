<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/config/ConfigUrl.php';
require_once dirname(__DIR__) . '/config/EmailConfig.php';
// phpmailer
use PHPMailer\PHPMailer\PHPMailer;

$baseUrl = ConfigUrl::get();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if ($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $userId = $result['id'];
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $sql = "INSERT INTO password_resets (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiry);
            $stmt->execute();

            $resetLink = $baseUrl . "/login/restablecer_contrasena.php?token=$token";
            EmailConfig::init();
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = EmailConfig::$SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = EmailConfig::$SMTP_USER;
                $mail->Password = EmailConfig::$SMTP_PASS;
                if ($_ENV["APP_ENV"] == 'local') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ],
                    ];
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                }

                $mail->setFrom(EmailConfig::$FROM_EMAIL, EmailConfig::$FROM_NAME);
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Recuperación de contraseña";
                $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='$resetLink'>$resetLink</a>";

                $mail->send();
                echo "Se ha enviado un enlace de recuperación a tu correo electrónico.";
            } catch (Exception $e) {
                echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "El correo electrónico no está registrado.";
        }
    } else {
        echo "Por favor, ingresa un correo electrónico válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
</head>

<body>
    <h1>Recuperar Contraseña</h1>
    <form method="POST" action="">
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Enviar</button>
    </form>
</body>

</html>