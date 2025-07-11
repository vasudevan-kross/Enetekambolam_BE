<?php

namespace App\Helpers;

use App\Models\SpecificNotificationModel;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class notificationHelper
{
    public static function buyOnceConfirmNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Order Confirmed!";
        $body = "Your Buy Once order $orderNumber is confirmed and being processed. We’ll notify you once it’s on the way! Thank you for shopping with us!";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }

    public static function buyOnceDeliveredNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Order Delivered!";
        $body = "Your Buy Once order $orderNumber has been successfully delivered! Enjoy your purchase!";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }

    public static function subscriptionConfirmNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Subscription Confirmed!";
        $body = "Your subscription $orderNumber is successfully confirmed! Enjoy our services, and we’ll keep you updated on your upcoming deliveries. Thank you for subscribing!";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }

    public static function subscriptionDeliveredNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Order Delivered!";
        $body = "Your subscription order $orderNumber has been successfully delivered! Enjoy your items, and we’ll be back with your next delivery soon!";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }

    public static function subscriptionPausedNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Subscription Paused!";
        $body = "Your subscription order $orderNumber has been successfully paused. Resume it anytime you’re ready for your next delivery!\n\nNOTE: RESUMING BEFORE THE CUTOFF TIME (I.E., 09:00 PM) IS REQUIRED FOR NEXT-DAY DELIVERY.";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }


    public static function subscriptionResumeNotify($orderId, $orderNumber, $userId)
    {
        // Prepare notification title and body
        $title = "Subscription Resumed!";
        $body = "Your subscription order $orderNumber has been successfully resumed! Your next delivery will be on its way shortly.";

        // Call the sendNotification method to send the notification
        return self::sendNotification($userId, $title, $body, "", "order");
    }


    public static function sendNotification($userId, $title, $body, $imageUrl = "", $notificationType = '')
    {
        try {
            // Fetch the user's FCM token
            $user = User::find($userId);
            if (!$user) {
                return [
                    'response' => 404,
                    'status' => false,
                    'message' => 'User not found',
                ];
            }

            if (!$user->fcm) {
                return [
                    'response' => 400,
                    'status' => false,
                    'message' => 'No FCM token found for the user',
                ];
            }

            // Save the notification to the database
            $notification = new SpecificNotificationModel();
            $notification->user_id = $userId;
            $notification->title = $title;
            $notification->body = $body;
            $notification->notification_type = $notificationType;
            $notification->created_at = now();
            $notification->updated_at = now();

            // Attempt to save the notification
            if (!$notification->save()) {
                return [
                    'response' => 500,
                    'status' => false,
                    'message' => 'Failed to save notification to the database',
                ];
            }

            // Send the notification via Firebase
            $firebaseResponse = self::sendFirebaseNotification($title, $body, $imageUrl, $user->fcm);
            if ($firebaseResponse['response'] !== 200) {
                return [
                    'response' => 500,
                    'status' => false,
                    'message' => 'Failed to send Firebase notification',
                    'firebase_error' => $firebaseResponse['message'] ?? 'Unknown error',
                ];
            }

            return [
                'response' => 200,
                'status' => true,
                'message' => 'Notification sent successfully',
            ];
        } catch (\Exception $e) {
            // Catch any exceptions and return a general error message
            return [
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while processing the notification',
                'error_details' => $e->getMessage(), // Optionally include error details
            ];
        }
    }

    private static function sendFirebaseNotification($title, $body, $imageUrl, $token)
    {
        // Path to your Firebase service account JSON file
        $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');

        // Ensure the file exists
        if (!file_exists(base_path($serviceAccountPath))) {
            return [
                'response' => 500,
                'status' => false,
                'message' => 'Firebase credentials file not found at the specified path',
            ];
        }

        try {
            // Initialize Firebase
            $factory = (new Factory)->withServiceAccount(base_path($serviceAccountPath));
            $messaging = $factory->createMessaging();

            // Create a notification payload
            $notification = Notification::create($title, $body);

            // Create a message
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData([
                    'is_specific' => true,
                    'sound' => 'default',
                    'priority' => 'high',
                    'imageUrl' => $imageUrl ?: '',
                ]);

            // Send the message
            $response = $messaging->send($message);

            return [
                'response' => 200,
                'status' => true,
                'message' => 'Notification sent successfully',
                'firebase_response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'response' => 500,
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
