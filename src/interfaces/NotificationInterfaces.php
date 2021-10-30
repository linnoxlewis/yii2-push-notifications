<?php

namespace linnoxlewis\pushNotifications\interfaces;

use linnoxlewis\pushNotifications\PushNotificationException;

/**
 * Интерфейс сервиса уведомлений
 *
 * Interface NotificationInterfaces
 *
 * @package app\components\push\interfaces
 */
interface NotificationInterfaces
{
    /**
     * Отправить уведомление
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
   public function sendNotification();

    /**
     * Отправить уведомление сообществу
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
   public function sendNotificationToTopic();

    /**
     * Подписать пользователя на сообщество
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
   public function subscribeUserForTopic();

    /**
     * Описать пользователя от сообщества
     *
     * @return PushNotifyResponseInterface
     * @throws PushNotificationException
     */
   public function unsubscribeUserFromTopic();

    /**
     * Валидация входящих параметров
     *
     * @return bool
     */
   public function validate();

    /**
     * Формирование ответа
     *
     * @param mixed $response ответ сервиса
     *
     * @return PushNotifyResponseInterface
     * @throws \Exception
     */
    public function getResultForSendingDevice($response);
}
