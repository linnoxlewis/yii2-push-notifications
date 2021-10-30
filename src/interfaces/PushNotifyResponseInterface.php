<?php

namespace linnoxlewis\pushNotifications\interfaces;

/**
 * Ответ от компонента
 *
 * Interface PushNotifyResponseInterface
 * @package linnoxlewis\pushNotifications\interfaces
 */
interface PushNotifyResponseInterface
{
    /**
     * Формирование ответа
     *
     * @return array
     */
    public function getResponse() : array;

    /**
     * Формирование ответа JSON
     *
     * @return string
     */
    public function getJsonResponse() : string ;
}
