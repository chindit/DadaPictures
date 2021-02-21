<?php

namespace App\Model;

class Languages
{
	public const FR = 'fr';
	public const EN = 'en';


	public static function all()
	{
		return [self::EN, self::FR];
	}
}
