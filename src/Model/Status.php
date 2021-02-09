<?php
declare(strict_types=1);

namespace App\Model;
/**
 * Static Class Status
 * Constants used for status
 */
class Status
{
    const OK = 1;
    const TEMPORARY = 2;
    const WARNING = 3;
    const ERROR = 4;
    const DUPLICATE = 5;

    /**
     * Return bootstrap equivalent for status const
     *
     * @param Status $const
     * @return string
     */
    public static function toBootstrap(int $const) : string
    {
        switch ($const) {
            case Status::OK:
            case Status::TEMPORARY:
                return 'success';
            case Status::WARNING:
                return 'warning';
            case Status::ERROR:
                return 'danger';
            default:
                return '';
        }
    }
}
