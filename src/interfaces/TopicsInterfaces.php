<?php

namespace linnoxlewis\pushNotifications\interfaces;

/**
 * Интерфейс сообщества
 *
 * Interface TopicsInterfaces
 *
 * @package app\components\push\interfaces
 */
interface TopicsInterfaces
{
    /**
     * Возвращает информацию по топикам.
     *
     * @param bool $short
     *
     * @return mixed
     */
    public function getTopics(bool $short = true);
}