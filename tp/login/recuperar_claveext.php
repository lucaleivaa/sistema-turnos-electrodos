<?php 
    include("con_db.php");

    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = mysqli_real_escape_string($conex, $_POST['email']);
        $consulta = "SELECT *, IFNULL(email, 'usuarios') as email FROM usuarios WHERE email='$email' LIMIT 1";
        $resultado = mysqli_query($conex, $consulta);
        $a = mysqli_fetch_assoc($resultado);
        
        if (!$a) {
            $_SESSION['error'] = 'Usuario inexistente';
            die();
        }

        $token = md5($a['email'] . time() . rand(1000, 9999));
        $clave_nueva = rand(10000000, 99999999);

        $consulta2 = "INSERT INTO recuperar SET email='$email', token='$token', fechaalta=NOW(), clavenueva='$clave_nueva' 
        ON DUPLICATE KEY UPDATE token='$token', clavenueva='$clave_nueva'";
        mysqli_query($conex, $consulta2);

        $link = "http://192.168.101.92:8084/tp/login/recuperar_clave_confirmar.php?e=$email&t=$token";

        $mensaje = <<<EMAIL
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Recuperar Contraseña</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <p>Hola {$a['username']}</p>
                <p>Has solicitado recuperar tu contraseña. El sistema te ha generado una nueva clave que es: <code class='success'>$clave_nueva</code></p>
                <p>Pero antes de poder usarla, deberás hacer <a href='$link'>clic en este vínculo</a> o copiar este código en la URL de tu navegador</p>
                <code class='alert alert-warning'>$link</code><br>
                <br><p>Si tú no has hecho esta solicitud, ignora el presente mensaje</p>
            </body>
        </html>
        EMAIL;

        echo $mensaje;
    }   
?>