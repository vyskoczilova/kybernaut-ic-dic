<?php

/**
 * Logger
 *
 * @package   KybernautMailstep
 *
 */

namespace KybernautIcDic;

/**
 * Logger
 *
 * @package KBNT\Mailstep\Logger
 */
class Logger
{

	/**
     * The single instance of the class.
     *
     * @var Logger
     */
    protected static $instance = null;

    /**
     * Log source
     *
     * @var string
     */
    protected string $source;

	/**
	 * Log all
	 *
	 * @var bool
	 */
	protected bool $log_all;

    /**
     * Main Logger Instance.
     *
     * Ensures only one instance of Logger is loaded or can be loaded.
     *
     * @return Logger - Main instance.
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	/**
	 * Setup
	 *
	 * @return void
	 */
	private function __construct()
	{
		// Load plugin name.
		$this->source = 'kybernaut-ic-dic';

		// Log all events, or just errors.
		$this->log_all = apply_filters('woolab_icdic_logger_log_all', true);
	}

	/**
	 * Log simple message
	 *
	 * @param string $message Message.
	 * @param string  $level Error level (emergency|alert|critical|error|warning|notice|info|debug).
	 * @return void
	 */
	public function log($message, $level = 'info')
	{

		// Make sure the level is valid.
		if (!in_array($level, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
			$level = 'info';
		}

		if (!$this->log_all && 'error' !== $level) {
			return;
		}

		$message_to_log = (!is_string($message) && !is_numeric($message) ) ? \json_encode($message) : $message;
		$wc_logger = \wc_get_logger();

		// Log message.
		$wc_logger->{$level}(
			strip_tags($message_to_log), // No HTML in logs.
			['source' => $this->source]
		);

	}

	/**
	 * Log error
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public function logError(string $message)
	{
		$this->log($message, 'error');
	}

	/**
	 * Log info
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public function logInfo(string $message)
	{
		$this->log($message, 'info');
	}

}
