<?php

namespace linnoxlewis\pushNotifications;

use GuzzleHttp\Client;
use linnoxlewis\pushNotifications\interfaces\NotificationInterfaces;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Сервис отправки пуш уведомлений
 *
 * Class PushNotificationService
 *
 * @package app\components\push
 */
class PushNotificationService extends Component
{
    /**
     * IOS система
     *
     * @var string
     */
    public const IOS_SYSTEM = 'ios';

    /**
     * Android система
     *
     * @var string
     */
    public const ANDROID_SYSTEM = 'android';

    /**
     * Ключ api пуш-сервиса
     *
     * @var string
     */
    public $apiKey;

    /**
     * Класс реализации сервиса отправки пуш уведомлений andorid
     *
     * @var string
     */
    public $notificationAndroidClass;

    /**
     * Класс реализации сервиса отправки пуш уведомлений ios
     *
     * @var string
     */
    public $notificationIosClass;

    /**
     * Класс реализации сервиса отправки пуш уведомлений
     *
     * @var string
     */
    public $topicClass;

    /**
     * Система
     *
     * @var string
     */
    protected $mobileSystem;

    /**
     * Путь до сертификата
     *
     * @var string
     */
    public $certificateFilePath;

    /**
     * Пароль от сертификата
     *
     * @var string
     */
    public $certificatePassPhrase;

    /**
     * Установка версии моб. приложения
     *
     * @param string $value api ключ
     *
     * @return $this
     */
    public function setApiKey(string $value)
    {
        $this->apiKey = $value;
        return $this;
    }

    /**
     * Установка версии моб. приложения
     *
     * @param string $value api ключ
     *
     * @return $this
     */
    public function setMobileSystem(string $value)
    {
        $this->mobileSystem = $value;
        return $this;
    }

    /**
     * Отправить уведомление на устройство
     *
     * @param string $deviceToken токен отправки.
     * @param string $title Заголовок
     * @param string $body Тело
     * @param array|null data Данные пуш-уведомления
     *
     * @return array
     */
    public function sendNotificationToDevice(string $deviceToken, string $title, $body = null, $data = []): array
    {
        try {
            $params = [
                'body' => $body,
                'deviceToken' => $deviceToken,
                'title' => $title,
                'data' => $data
            ];
             return $this->getNotificationClass($params)
                ->sendNotification()
                ->getResponse();
        } catch (\Throwable $exception) {
            $resp = new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                $exception->getMessage()
            );
            return $resp->getResponse();
        }
    }

    /**
     * Отправить уведомление сообществу
     *
     * @param string $topicName название сообщества.
     * @param string $title Заголовок
     * @param string $body Тело
     *
     * @return array
     */
    public function sendNotificationToTopic(string $topicName, string $title, $body = null, $data = []): array
    {
        try {
            $params =[
                'body' => $body,
                'topicName' => $topicName,
                'title' => $title,
                'data' => $data
            ];
            return $this->getNotificationClass($params)
                ->sendNotificationToTopic()
                ->getResponse();
        } catch (\Throwable $exception) {
            $resp = new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                $exception->getMessage()
            );
            return $resp->getResponse();
        }
    }

    /**
     * Подписать пользователя на сообщество
     *
     * @param string $topicName название сообщества.
     * @param string $deviceToken токен отправки.
     *
     * @throws PushNotificationException
     *
     * @return array
     */
    public function subscribeUserForTopic(string $topicName,string $deviceToken): array
    {
        try {
            $params =  [
                'topicName' => $topicName,
                'deviceToken' => $deviceToken,
            ];
            return $this->getNotificationClass($params)
                ->subscribeUserForTopic()
                ->getResponse();
        } catch (\Throwable $exception) {
            $resp = new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                $exception->getMessage()
            );
            return $resp->getResponse();
        }
    }

    /**
     * Подписать пользователя на сообщества
     *
     * @param array $topics название сообществ.
     * @param string $deviceToken токен отправки.
     *
     * @return void
     */
    public function subscribeUserForTopics(array $topics, string $deviceToken): void
    {
        foreach ($topics as $topic) {
            $this->subscribeUserForTopic($topic, $deviceToken);
        }
    }

    /**
     * Отписать пользователя от сообщества
     *
     * @param string $topicName название сообщества.
     * @param string $deviceToken токен отправки.
     *
     * @return mixed
     */
    public function unsubscribeUserFromTopic($topicName,$deviceToken)
    {
        try {
            $params = [
                'topicName' => $topicName,
                'deviceToken' => $deviceToken,
            ];
            return $this->getNotificationClass($params)
                ->unsubscribeUserFromTopic()
                ->getResponse();
        } catch (\Throwable $exception) {
            $resp = new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                $exception->getMessage()
            );
            return $resp->getResponse();
        }
    }

    /**
     * Отписать пользователя от сообществ
     *
     * @param array $topics название сообществ.
     * @param string $deviceToken токен отправки.
     *
     */
    public function unsubscribeUserFromTopics(array $topics, string $deviceToken): void
    {
        foreach ($topics as $topic) {
            $this->unsubscribeUserFromTopic($topic, $deviceToken);
        }
    }

    /**
     * Получение сообществ пользователя
     *
     * @param string $deviceToken токен отправки.
     * @param bool   $short полный или укороченный ответ
     *
     * @return array
     */
    public function getTopics(string $deviceToken, bool $short = true): array
    {
        try {
            $topicsObject = new $this->topicClass();
            $topicsObject->deviceToken = $deviceToken;
            $topicsObject->guzzleClient = new Client();
            $topicsObject->apiKey = $this->apiKey;
            return $topicsObject->getTopics($short);
        } catch (\Throwable $exception) {
            $resp = new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                $exception->getMessage()
            );
            return $resp->getResponse();
        }
    }

    /**
     * Создание класса пуша
     *
     * @param array $params параметры пуша
     *
     * @return NotificationInterfaces
     * @throws PushNotificationException
     */
    protected function getNotificationClass(array $params) : NotificationInterfaces
    {
        try {
            switch (strtolower($this->mobileSystem)) {
                case static::IOS_SYSTEM :
                    $notificationClass = $this->notificationIosClass;
                    $params['certificateFilePath'] = $this->certificateFilePath;
                    $params['certificatePassPhrase'] = $this->certificatePassPhrase;
                    break;
                case static::ANDROID_SYSTEM :
                    $notificationClass = $this->notificationAndroidClass;
                    $params['apiKey'] = $this->apiKey;
                    break;
                default:
                    throw new PushNotificationException('Undefined mobile system',500);
            }

            if (!class_exists($notificationClass)) {
                Throw new PushNotificationException("Notification class not found",404);
            }
            return static::createObject($notificationClass,$params);
        } catch (\Throwable $ex) {
            throw new PushNotificationException($ex->getMessage(),$ex->getCode());
        }
    }

    /**
     * Создание объекта
     *
     * @param string $class класс сообщества
     * @param array $params параметры
     *
     * @throws PushNotificationException
     * @return NotificationInterfaces
     */
    protected static function createObject(string $class, array $params) : NotificationInterfaces
    {
        $params['class'] = $class;
        $object = \Yii::createObject($params);
        if (!$object || !$object->validate()) {
            throw new PushNotificationException('Can`t create push notification service');
        }
        return $object;
    }
}
