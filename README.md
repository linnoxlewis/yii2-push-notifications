# yii2-push-notifications
Push notifications components for yii2

'pushNotification' => [
            
	    'class' => \app\components\push\PushNotificationService::class,
            
	    'apiKey' => 'FIREBASE_API_KEY_STRING',
       
	    'notificationAndroidClass' => linnoxlewis\pushNotifications\notifications\Firebase::class,
            
	    'topicClass' => linnoxlewis\pushNotifications\topics\Topics::class

	    'notificationAndroidClass' => linnoxlewis\pushNotifications\notifications\ApnsService::class
	    
	    'certificateFilePath' => 'PEM_CERITFICATE_FULL_PATH',
	    
	    'certificatePassPhrase' => 'PASS_PHRASE',
        ],
	

