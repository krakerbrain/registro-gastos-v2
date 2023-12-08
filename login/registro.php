<?php
include('../config.php');
$creado = "false";
$error = "";

if (isset($_POST['usuario']) && isset($_POST['correo']) && isset($_POST['password']) && isset($_POST['password2'])) {
    $usuario_registro = $_POST['usuario'];
    $correo = $_POST['correo'];
    $pass = $_POST['password'];
    $pass2 = $_POST['password2'];

    if (!empty($usuario_registro) && !empty($correo) && !empty($pass) && !empty($pass2)) {
        // Los campos no están vacíos, proceder con la validación y la inserción en la base de datos

        try {

            $query = $con->prepare("CALL validar_registro(:usuario, :correo, :pass, :pass2, @error)");
            $query->bindParam(':usuario', $usuario_registro);
            $query->bindParam(':correo', $correo);
            $query->bindParam(':pass', $pass);
            $query->bindParam(':pass2', $pass2);
            $query->execute();
        } catch (PDOException $e) {
            echo "Error en la ejecución de la consulta: " . $e->getMessage();
        }

        // Obtener el mensaje de error desde la variable de sesión de MySQL
        $errorQuery = $con->query("SELECT @error")->fetch(PDO::FETCH_ASSOC);
        $error = $errorQuery['@error'];

        if ($error) {
            // Mostrar el mensaje de error
            $error = '<p class="alert alert-primary">' . $error . '</p>';
        } else {
            // El registro es válido, continuar con la inserción en la base de datos
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 7]);
            $query = $con->prepare("INSERT INTO users(name, email, password) VALUES (:nombre, :correo, :clave)");
            $query->bindParam(':nombre', $usuario_registro);
            $query->bindParam(':correo', $correo);
            $query->bindParam(':clave', $hash);
            $query->execute();
            $count2 = $query->rowCount();
            // $idusuario = $con->lastInsertId();

            if ($count2) {
                header("location:index.php?creado=true");
            } else {
                $error = "conex";
            }
        }
    } else {
        $error = '<p class="alert alert-primary">Todos los campos son obligatorios</p>';
    }
}


include "../partials/header.php";
?>

<body class="bg-primary d-flex justify-content-center align-items-center vh-100">
    <div class="bg-white p-5 rounded">
        <div class="justify-content-center">
            <form action="" method="post" class="form-group">
                <div class="text-center">
                    <h4>REGISTRO DE USUARIOS</h4>
                </div>
                <div class="input-group">
                    <div class="input-group-text bg-primary text-light">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Ingrese un nombre de usuario" ">
                </div>
                <div class=" input-group mt-2">
                    <div class="input-group-text bg-primary text-light">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <input type="mail" name="correo" id="correo" class="form-control" placeholder="Ingrese un correo">
                </div>
                <div class="input-group mt-2">
                    <div class="input-group-text bg-primary text-light">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese una clave">
                    <div class="input-group-text bg-light">
                        <a href="#" class="pe-auto text-primary">
                            <i class="fa-solid fa-eye" onclick="verpass(1)"></i>
                        </a>
                    </div>
                </div>
                <div class="input-group mt-2">
                    <div class="input-group-text bg-primary text-light">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <input type="password" name="password2" id="password2" class="form-control" placeholder="Ingrese otra vez">
                    <div class="input-group-text bg-light">
                        <a href="#" class="pe-auto text-primary">
                            <i class="fa-solid fa-eye" onclick="verpass(2)"></i>
                        </a>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <input type="submit" value="Registrar" class="btn btn-primary w-100">
                </div>
                <div class="mt-3 text-center">
                    <?php echo $error ?>
                </div>
                <div>
                    <a href="index.php">Ir al inicio</a>
                </div>
            </form>
            <script>
                function verpass(param) {
                    var pass1 = document.getElementById('password');
                    var pass2 = document.getElementById('password2');
                    if (param == 1) {
                        pass1.type = pass1.type == "password" ? "text" : "password"
                    } else {
                        pass2.type = pass2.type == "password" ? "text" : "password"
                    }
                }

                <?php if ($error == "correo") { ?>
                    document.getElementById('correo').focus();
                <?php } else if ($error == "vacio") { ?>
                    document.getElementById('usuario').focus();
                <?php } ?>
            </script>
            <?php
            include "../partials/footer.php";
            ?>