<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../list/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acceso no permitido');
}

$destinatario = trim($_POST['destinatario'] ?? '');
$emisor       = trim($_POST['emisor'] ?? '');
$correo       = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$correo || empty($destinatario) || empty($emisor)) {
    exit('Datos incompletos o correo inválido');
}

$mail = new PHPMailer(true);

// Adjuntar archivo si existe
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {

    $ruta_tmp = $_FILES['archivo']['tmp_name'];
    $nombre_archivo = $_FILES['archivo']['name'];

    $mail->addAttachment($ruta_tmp, $nombre_archivo);
}

try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'jhondraco.18@gmail.com';
    $mail->Password   = 'cwiwwnzdulpdwmxu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Remitente y destinatario
    $mail->setFrom('jhondraco.18@gmail.com', 'RRHH');
    $mail->addAddress($correo, $destinatario);

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = "Invitación a entrevista - $emisor";

 $mail->Subject = "Documento enviado por $emisor";

$mail->Body = "
<p>Estimado/a <strong>{$destinatario}</strong>,</p>

<p>
Se le envía el documento adjunto para su revisión.
</p>

<p>
Si tiene alguna duda puede responder a este correo.
</p>

<p>Atentamente,<br><strong>{$emisor}</strong></p>
";

    $mail->send();

    header('Location: contactar.php?enviado=1');

    exit;

} catch (Exception $e) {
    echo "Error en el envío: {$mail->ErrorInfo}";
}
