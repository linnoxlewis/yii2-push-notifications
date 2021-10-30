<?php
namespace linnoxlewis\pushNotifications;

use linnoxlewis\pushNotifications\interfaces\PushNotifyResponseInterface;

/**
 * Ответ от компонента
 *
 * Class PushNotifyResponse
 *
 * @package linnoxlewis\pushNotifications
 */
class PushNotifyResponse implements PushNotifyResponseInterface
{
    /**
     * Негативный код ответа
     *
     * @var int
     */
    const BAD_REQUEST_RESPONSE = 400;

    /**
     * Позитивный код ответа
     *
     * @var int
     */
    const OK_RESPONSE = 200;

    /**
     * Положительный|Отрицательный ответ
     *
     * @var bool
     */
    public $success;

    /**
     * Код ответа
     *
     * @var int
     */
    public $code;

    /**
     * Сообщение ответа
     *
     * @var string
     */
    public $message;

    /**
     * PushNotifyResponse constructor.
     *
     * @param bool $success
     * @param int $code
     * @param $message
     */
    public function __construct(bool $success, int $code, $message)
    {
        $this->success = $success;
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * Формирование ответа
     *
     * @return array
     */
    public function getResponse() : array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'code' => $this->code
        ];
    }

    /**
     * Формирование ответа JSON
     *
     * @return string
     */
    public function getJsonResponse() : string
    {
        return json_encode($this->getResponse());
    }
}
