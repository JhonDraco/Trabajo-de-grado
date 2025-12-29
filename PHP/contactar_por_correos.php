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

    $mail->Body = "
        <p>Estimado/a <strong>{$destinatario}</strong>,</p>

        <p>
            Nos gustaría invitarte a una entrevista para conocerte mejor y conversar sobre
            tu experiencia, así como los detalles de la oportunidad.
        </p>

        <p>
            La entrevista puede realizarse de manera <strong>presencial o virtual</strong>,
            en la fecha y hora que te resulte más conveniente dentro de esta semana.
        </p>

        <p>
            Por favor, indícanos tu disponibilidad para coordinar el encuentro.
        </p>

        <p>
            Quedamos atentos a tu respuesta y agradecemos tu interés en formar parte de
            nuestro equipo.
        </p>

        <p>Atentamente,<br><strong>{$emisor}</strong></p>
    ";

    $mail->AltBody = "Estimado/a $destinatario,
Nos gustaría invitarte a una entrevista.
Indícanos tu disponibilidad.
Atentamente, $emisor";

    $mail->send();

    header('Location: contactar.php?enviado=1');

    exit;

} catch (Exception $e) {
    echo "Error en el envío: {$mail->ErrorInfo}";
}
