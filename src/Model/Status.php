<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Static Class Status
 * Constants used for status
 */
class Status
{
    public const OK = 1;
    public const TEMPORARY = 2;
    public const WARNING = 3;
    public const ERROR = 4;
    public const DUPLICATE = 5;
    public const PROCESSING_UPLOAD = 6;
    public const PROCESSING_VALIDATION = 7;
    public const BANNED = 8;

    /**
     * Return bootstrap equivalent for status const
     */
    public static function toBootstrap(int $const): string
    {
        switch ($const) {
            case Status::OK:
            case Status::TEMPORARY:
                return 'success';
            case Status::WARNING:
                return 'warning';
            case Status::ERROR:
            case Status::BANNED:
                return 'danger';
            default:
                return '';
        }
    }

	public static function toName(int $const): string
	{
		return match($const) {
			1 => 'OK',
			2 => 'Temporary',
			3 => 'Warning',
			4 => 'Error',
			5 => 'Duplicate',
			6 => 'Processing upload',
			7 => 'Processing validation',
			8 => 'Banned',
			default => throw new \UnexpectedValueException(sprintf('Value %d is not in Status enum', $const))
		};
	}
}
