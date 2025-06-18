<?php
include('../config.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8');
    $newPassword = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    if ($token && $newPassword) {
        $sql = "SELECT user_id FROM password_resets WHERE token = :token AND expiry > NOW()";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $userId = $result['user_id'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            $sql = "DELETE FROM password_resets WHERE token = :token";
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            echo "Tu contraseña ha sido actualizada exitosamente.";
        } else {
            echo "El enlace de recuperación es inválido o ha expirado.";
        }
    } else {
        echo "Por favor, completa todos los campos.";
    }
} else if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
    echo "<form method='POST' action=''>
            <input type='hidden' name='token' value='$token'>
            <label for='password'>Nueva Contraseña:</label>
            <input type='password' id='password' name='password' required>
            <button type='submit'>Restablecer Contraseña</button>
          </form>";
} else {
    echo "Acceso no autorizado.";
}