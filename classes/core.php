<?php

class Core {
	protected static $levels = array(
		'error' => 1,
		'info' => 2,
		'warning' => 3,
		'debug' => 4,
	);

	protected static $config = array();

	public static function config($key, $default = FALSE) {
		// Break up the key passed in.
		list($file, $key) = explode('.', $key, 2);

		// If that config isn't loaded, attempt to.
		if (! array_key_exists($file, self::$config)) {
			$file_path = APPPATH.'config/'.$file.'.php';

			// No such file? Emulate an empty one.
			if (! file_exists($file_path)) {
				self::$config[$file] = array();
			} else {
				require_once($file_path);
				self::$config[$file] = isset($config)
					? $config
					: array();
			}
		}

		return array_key_exists($key, self::$config[$file])
			? self::$config[$file][$key]
			: $default;
	}

	public static function log($level, $message) {
		// Check it's a valid level.
		if (! array_key_exists($level, self::$levels))
			return;

		// Grab the level number.
		$level_num = self::$levels[$level];

		// Don't log if the level is above the configured one.
		if ($level_num > config::item('config.log_level', 1))
			return;

		// Do printf-style formatting if required.
		$args = array_slice(func_get_args(), 2);
		if ((bool) count($args)) {
			$message = vsprintf($message, $args);
		}

		// Build the mesage.
		$message = sprintf(
			'%s - [%s] %s',
			date('Y-m-d H:i:s'),
			$level,
			$message
		)."\n";

		// Output if we're debugging.
		if (config::item('config.debug_mode'))
			echo $message;

		// Finally write it to the log.
		static $log_file;
		if (! (bool) $log_file) {
			$log_file = fopen('log.txt', 'a');
		}
		fwrite($log_file, $message);
	}
}