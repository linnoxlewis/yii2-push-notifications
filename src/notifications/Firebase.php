<?php

namespace linnoxlewis\pushNotifications\notifications;

use linnoxlewis\pushNotifications\interfaces\NotificationInterfaces;
use linnoxlewis\pushNotifications\interfaces\PushNotifyResponseInterface;
use linnoxlewis\pushNotifications\PushNotificationException;
use linnoxlewis\pushNotifications\PushNotifyResponse;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use yii\base\Model;

/**
 * Firebase сервис отправки
 *
 * Class Firebase
 *
 * @package app\components\push\notifications
 */
class Firebase extends Model implements NotificationInterfaces
{
    /**
     * Тело нотификации
     *
     * @var string
     */
    public $body;

    /**
     * Токен устройства
     *
     * @var string
     */
    public $deviceToken;

    /**
     * Заголовок
     *
     * @var string
     */
    public $title;

    /**
     * Параметры
     *
     * @var array
     */
    public $data;

    /**
     * Ключ сервиса нотификаций
     *
     * @var string
     */
    public $apiKey;

    /**
     * Название сообщества
     *
     * @var string
     */
    public $topicName;

    /**
     * Правила Валидации
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            ['apiKey', 'required'],
            ['deviceToken', 'required', 'when' => function ($model) {
                return empty($model->topicName);
            }],
            ['topicName', 'required', 'when' => function ($model) {
                return empty($model->deviceToken);
            }],
            [['title', 'topicName', 'apiKey'], 'string', 'length' => [1, 255]],
            [['body', 'deviceToken'], 'string', 'length' => [1, 1000]],
            [['title', 'body', 'topicName', 'deviceToken'], 'trim'],
            ['data', 'checkIsArray'],
        ];
    }

    /**
     * Отправить уведомление
     *
     * @return array
     * @throws \Exception
     * @throws PushNotificationException
     */
    public function sendNotification()
    {
        try {
            $client = $this->getClient();
            $message = $this->getMessage();
            $response = $client->send($message);
            return $this->getResultForSendingDevice($response);
        } catch (\Throwable $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Отправить уведомление сообществу
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
    public function sendNotificationToTopic() : PushNotifyResponseInterface
    {
        try {
            $client = $this->getClient();
            $message = $this->getMessage(true);
            $response = $client->send($message);
            $code = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (!is_array($responseBody) || !isset($responseBody['message_id'])) {
                throw new PushNotificationException('Sending message tot topic failed', $code);
            }
            return new PushNotifyResponse(true,
                PushNotifyResponse::OK_RESPONSE,
                'COMPLIED'
            );

        } catch (\Exception $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Подписать пользователя на сообщество
     *
     * @return PushNotifyResponseInterface
     * @throws \Exception
     * @throws PushNotificationException
     */
    public function subscribeUserForTopic() : PushNotifyResponseInterface
    {
        try {
            $client = $this->getClient();
            $response = $client->addTopicSubscription($this->topicName, [$this->deviceToken]);
            $code = $response->getStatusCode();
            if ($code !== PushNotifyResponse::OK_RESPONSE) {
                throw new PushNotificationException('Subscribtion user to topic failed', $code);
            }
            return new PushNotifyResponse(true,
                PushNotifyResponse::OK_RESPONSE,
                'COMPLIED'
            );
        } catch (\Exception $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Описать пользователя от сообщества
     *
     * @return PushNotifyResponseInterface
     * @throws \Exception
     * @throws PushNotificationException
     */
    public function unsubscribeUserFromTopic() : PushNotifyResponseInterface
    {
        try {
            $client = $this->getClient();
            $response = $client->removeTopicSubscription($this->topicName, [$this->deviceToken]);
            $code = $response->getStatusCode();
            if ($code !== PushNotifyResponse::OK_RESPONSE) {
                throw new PushNotificationException('Unsubscribtion user from topic failed', $code);
            }
            return new PushNotifyResponse(true,
                PushNotifyResponse::OK_RESPONSE,
                'COMPLIED'
            );
        } catch (\Exception $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Создание и конфигурация объекта Сообщения
     *
     * @return Message
     * @see    Message
     */
    private function getMessage($notificationToTopic = false, string $priority = 'high'): Message
    {
        $instance = (!$notificationToTopic)
            ? $this->getDeviceInstance()
            : $this->getTopicInstance();
        $message = (new Message())->setPriority($priority)
            ->addRecipient($instance)
            ->setNotification($this->getNotificationInstance());
        if (!empty($this->data)) {
            $message->setData($this->data);
        }
        return $message;
    }

    /**
     * Создание и конфигурация объекта Сообщения
     *
     * @return Device
     * @see    Device
     */
    private function getDeviceInstance(): Device
    {
        return new Device($this->deviceToken);
    }

    /**
     * Создание и конфигурация объекта Сообщеcтва
     *
     * @return Topic
     * @see    Topic
     */
    private function getTopicInstance(): Topic
    {
        return new Topic($this->topicName);
    }

    /**
     * Создание и конфигурация объекта Сообщения
     *
     * @return Notification
     */
    private function getNotificationInstance(): Notification
    {
        return new Notification($this->title, $this->body);
    }

    /**
     * Создание и конфигурация объекта Клиент
     *
     * @return Client
     * @see    Client
     */
    private function getClient(): Client
    {
        $client = new Client();
        $client->setApiKey($this->apiKey);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());
        return $client;
    }

    /**
     * Формирование ответа
     *
     * @param mixed $response ответ сервиса
     *
     * @return PushNotifyResponseInterface
     * @throws \Exception
     */
    public function getResultForSendingDevice($response) :PushNotifyResponseInterface
    {
        $code = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        (isset($data['success']) && $data['success'] == 1
            && isset($data['failure']) && $data['failure'] == 0)
            ? $success = true
            : $success = false;
        if (!$success) {
            if (isset($data['results'][0]['error'])) {
                throw new PushNotificationException($data['results'][0]['error'], $code);
            } else {
                throw new PushNotificationException('Sending message  failed', $code);
            }
        }
        return new PushNotifyResponse($success,
            PushNotifyResponse::OK_RESPONSE,
            'COMPLIED'
        );
    }

    /**
     * Проверка входящих данных
     *
     * @param $attribute
     *
     * @return bool
     */
    public function checkIsArray($attribute)
    {
        if (!empty($this->$attribute) && !is_array($this->$attribute)) {
            $this->addError($attribute, 'Must be array');
        }
        return true;
    }
}
