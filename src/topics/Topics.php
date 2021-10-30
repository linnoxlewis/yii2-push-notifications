<?php

namespace linnoxlewis\pushNotifications\topics;

use linnoxlewis\pushNotifications\interfaces\TopicsInterfaces;
use yii\base\Model;

/**
 * Класс сообщества fcm
 *
 * Class Topics
 *
 * @package app\components\push\topics
 */
class Topics implements TopicsInterfaces
{
    const BASE_API_URL = 'https://iid.googleapis.com/iid';

    public function __construct(){}

    /**
     * Firebase API Key.
     *
     * @var string
     */
    public $apiKey;

    /**
     * Модель Guzzle клиента.
     *
     * @var
     */
    public $guzzleClient;

    /**
     * Токен девайса.
     *
     * @var string
     */
    public $deviceToken;

    /**
     * Полный адрес запроса.
     *
     * @var string
     */
    private $apiUrl;

    /**
     * Возвращает информацию по топикам.
     *
     * @param bool $short
     *
     * @return array
     */
    public function getTopics(bool $short = true): array
    {
        $this->setApiUrlToGetTopics();
        $topics = $this->send();
        if ($short){
            return array_keys($topics['rel']['topics']);
        }
        return $topics;
    }

    /**
     * Устанавливает полный адрес запроса для получения топиков, на которые подписан девайс.
     *
     * @return void
     */
    private function setApiUrlToGetTopics(): void
    {
        $this->apiUrl = self::BASE_API_URL . '/info/' . $this->deviceToken . '?details=true';
    }

    /**
     * Отправка запроса.
     *
     * @return array
     */
    private function send(): array
    {
        $response = $this->guzzleClient->get(
            $this->apiUrl,
            [
                'headers' => [
                    'Authorization' => sprintf('key=%s', $this->apiKey),
                    'Content-Type' => 'application/json'
                ],
            ]
        );
        $responseJson = $response->getBody()->getContents();
        return json_decode($responseJson, true);
    }
}