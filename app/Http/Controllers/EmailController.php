<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function sendEmail()
    {
        Log::info("Email function triggered!"); // Add logging to check if function runs

        // Brevo API Setup
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', env('BREVO_API_KEY'));

        $apiInstance = new TransactionalEmailsApi(new Client(), $config);

        $sendSmtpEmail = new SendSmtpEmail([
            'sender' => ['name' => 'Mikuthi Team', 'email' => 'entekambolam@gmail.com'],
            'to' => [['email' => 'vasudevan.r@krossark.com', 'name' => 'Vasudevan']],
            'subject' => 'Test Email from Brevo API',
            'htmlContent' => '<h1>Hello!</h1><p>This is a test email sent using Brevo.</p>',
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            Log::info("Email sent successfully!", ['response' => $result]);
            return response()->json(['message' => 'Email sent successfully!', 'response' => $result]);
        } catch (\Exception $e) {
            Log::error("Email failed!", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }
}
