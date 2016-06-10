<?php

namespace Report\Api;

class Authenticator
{
	private static $credentailsPath = __DIR__ . '/../../../config/auth.ini';

	static public function getUser() {
		$user = new \DfpUser(self::$credentailsPath);

		return $user;
	}
}