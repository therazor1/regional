<?php
$titulo = $_GET['titulo'];
$mensaje = $_GET['mensaje'];
////////ENVÍO DE NOTIFICACIÓN/////////
$fcmUrl = 'https://fcm.googleapis.com/fcm/send';
/**
 *SUSTITUIR AQUÍ POR EL TOKEN O UN ARRAY DE TOKENS EN CASO DE QUE QUIERAS QUE LA NOTIFICACIÓN LA RECIBA MÁS DE UN DISPOSITIVO
 * ENPOINT: BASE_URL + ?titulo="Holatest"&mensaje="Test noti"
 */
$token = 'fFMEfBIqRJOWBNa-ChlWtv:APA91bFEKlUIQc46a7sN-vlymEVqwVj6p1rarNDQfsCC_hwgI9j5-PXePI84Ol2wmfQvN3wD2dcj01JKUiEpZ0dURncHtuwP25CEYsf916Ul3pELYcQKo43sQJph3c5HVbEMrpa2ovGB';

$apiKey = 'AAAAaTSzSlQ:APA91bG0rfQadeqCgCRGdyouLRGqFa_yCXfnKp-SDF-8kk2vQWjXBnZgLA_TovP0nYRjCHjpv0kYTkFvEF-kcRwYZFgC0Vdd_nwRmzVp68VXboSRQ0WOEQgBakC6nhTOIRqhLdw6FmkG';
$notification = ['title' => $titulo, 'body' => $mensaje, 'icon' => 'myIcon', 'sound' => 'mySound'];
$extraNotificationData = ["message" => $notification, "moredata" => 'dd'];

$fcmNotification = [
    'to' => $token,
    'notification' => $notification, 'data' => $extraNotificationData
];

$headers = ['Authorization: key=' . $apiKey, 'Content-Type: application/json'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fcmUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
$result = curl_exec($ch);
curl_close($ch);
echo $result;




