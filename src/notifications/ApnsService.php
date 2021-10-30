<?php

namespace linnoxlewis\pushNotifications\notifications;

use linnoxlewis\pushNotifications\interfaces\NotificationInterfaces;
use linnoxlewis\pushNotifications\interfaces\PushNotifyResponseInterface;
use linnoxlewis\pushNotifications\PushNotificationException;
use linnoxlewis\pushNotifications\PushNotifyResponse;
use yii\base\Model;

use
    Sly\NotificationPusher\Adapter\Apns,
    Sly\NotificationPusher\PushManager,
    Sly\NotificationPusher\Collection\DeviceCollection,
    Sly\NotificationPusher\Model\Device,
    Sly\NotificationPusher\Model\Message,
    Sly\NotificationPusher\Model\Push;

/**
 * Ios сервис пушей
 *
 * Class ApnsService
 *
 * @package app\components\push\notifications
 */
class ApnsService extends Model implements NotificationInterfaces
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
     * Правила валидации
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
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
    public function sendNotification() : PushNotifyResponseInterface
    {
        try {
            $pushManagerEnvironment = (YII_ENV_DEV)
                ? PushManager::ENVIRONMENT_DEV
                : PushManager::ENVIRONMENT_PROD;

            if(!file_exists($this->certificateFilePath)) {
                throw new PushNotificationException('Certificate does not exits');
            }
            $pushManager = new PushManager($pushManagerEnvironment);
            $adapter = new Apns([
                'certificate' => $this->certificateFilePath,
                'passPhrase' => $this->certificatePassPhrase,
            ]);
            $instance =  $this->getDeviceInstance();
            $message = $this->getMessage();
            $push = new Push($adapter,$instance,$message);
            $pushManager->add($push);
            $pushManager->push();
            return $this->getResultForSendingDevice($push);
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
            return new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                'Sending message to topic not supported yet'
            );
        } catch (\Throwable $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Подписать пользователя на сообщество
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
    public function subscribeUserForTopic() : PushNotifyResponseInterface
    {
        try {
            return new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                'Subscribtion user to topic not supported yet'
            );
        } catch (\Throwable $e) {
            throw new PushNotificationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Описать пользователя от сообщества
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
    public function unsubscribeUserFromTopic() : PushNotifyResponseInterface
    {
        try {
            return new PushNotifyResponse(false,
                PushNotifyResponse::BAD_REQUEST_RESPONSE,
                'Unsubscribtion user to topic not supported yet'
            );
        } catch ( \Throwable $ex) {
            throw new PushNotificationException($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Формирование ответа
     *
     * @param mixed $response ответ сервиса
     *
     * @return PushNotifyResponseInterface
     * @throws \Exception
     */
    public function getResultForSendingDevice($response) : PushNotifyResponseInterface
    {
        try {
           if ($response->isPushed()) {
               return new PushNotifyResponse(true,
                   PushNotifyResponse::OK_RESPONSE,
                   'COMPLIED'
               );
           } else {
               throw new PushNotificationException('Sending message  failed', PushNotifyResponse::BAD_REQUEST_RESPONSE);
           }
        } catch (\Throwable $ex) {
            throw new PushNotificationException($ex->getMessage(), PushNotifyResponse::BAD_REQUEST_RESPONSE);
        }
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

    /**
     * Создание и конфигурация объекта Сообщения
     *
     * @return Message
     * @see    Message
     */
    private function getMessage($notificationToTopic = false): Message
    {
        return new Message($this->body,[
            'title' => $this->title,
            'sound' => 'default',
            'custom' => $this->data
        ]);
    }

    /**
     * Формирование устройств отправки
     *
     * @return DeviceCollection
     * @see    DeviceCollection
     */
    private function getDeviceInstance() : DeviceCollection
    {
        return new DeviceCollection([
            new Device($this->deviceToken)
        ]);
    }
}
