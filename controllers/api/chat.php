<?php namespace Controllers\api;

use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Inc\Req;
use Inc\Rsp;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class chat extends _controller
{

    const TWILIO_ACCOUNT_SID = 'AC36cf3f9a73fe665058f786db28431013';
    const TWILIO_AUTH_TOKEN = 'bb0683951e527db0cf3b24da76743d65';

    const GOOGLE_PROJECT_ID = 'pruebafocus-htin';
    const GOOGLE_LANGUAGE_CODE = 'es-ES';

    public function index(Req $req)
    {

        $sessionId = 'sesionprueba002';



        $from = $req->any('From');
        $to = $req->any('To');
        $Body = $req->any('Body');

        $respuesta_text = $this->detect_intent_texts($Body, $sessionId);

        $client = new Client(self::TWILIO_ACCOUNT_SID, self::TWILIO_AUTH_TOKEN);

        try {
            $client->messages->create(
                $from, # el numero del cliente a responder
                [
                    'from' => $to, # el numero que tenemos en twilio
                    'body' => $respuesta_text,
                ]
            );
        } catch (TwilioException $e) {
        }

        return Rsp::ok();
    }

    function detect_intent_texts($text, $sessionId)
    {

        # asignar la ruta de la credencial de servicio googl (autenticar)
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . _PATH_ . '/assets/files/service_account.json');

        # nueva sesion
        $sessionsClient = new SessionsClient();
        $session = $sessionsClient->sessionName(self::GOOGLE_PROJECT_ID, $sessionId);
        printf('Session path: %s' . PHP_EOL, $session);

        # crear texto de entrada
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode(self::GOOGLE_LANGUAGE_CODE);

        # crear consulta de entrada
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        # obtener respuesta e información relevante
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $queryResult = $response->getQueryResult();
        $queryText = $queryResult->getQueryText();
        $intent = $queryResult->getIntent();
        $displayName = $intent->getDisplayName();
        $confidence = $queryResult->getIntentDetectionConfidence();
        $fulfilmentText = $queryResult->getFulfillmentText();

        // output relevant info
        print(str_repeat("=", 20) . PHP_EOL);
        printf('Query text: %s' . PHP_EOL, $queryText);
        printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
            $confidence);
        print(PHP_EOL);
        printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);

        $sessionsClient->close();

        return $fulfilmentText;
    }

}