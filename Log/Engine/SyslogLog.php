<?php
App::uses('BaseLog', 'Log/Engine');

/**
 * SyslogLog
 *
 */
class SyslogLog extends BaseLog {

/**
 * _defaultConfig
 *
 * By default messages are formatted as:
 * 	type: message
 *
 * To override the log format (e.g. to add a prefix) define the format key when configuring
 * this logger. E.g.
 *
 *	CakeLog::config('error', array(
 *		'engine' => 'SysLog.SyslogLog',
 *		'types' => array('emergency', 'alert', 'critical', 'error'),
 *		'format' => "my-log-prefix %s: %s"
 *	));
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'types' => array(),
		'format' => '%s: %s'
	);

/**
 * _priorityMap
 *
 * Used to map the string names back to their LOG_* values
 *
 * @var array
 */
	protected $_priorityMap = array(
		'emergency' => LOG_EMERG,
		'alert' => LOG_ALERT,
		'critical' => LOG_CRIT,
		'error' => LOG_ERR,
		'warning' => LOG_WARNING,
		'notice' => LOG_NOTICE,
		'info' => LOG_INFO,
		'debug' => LOG_DEBUG
	);

/**
 * __construct
 *
 * Make sure the config contains the format parameter, by default, log the error number and the
 * type as a prefix to the message
 *
 * @param array $config
 */
	public function __construct($config = array()) {
		$config += $this->_defaultConfig;
		parent::__construct($config);
	}

/**
 * write a message to sysllog
 *
 * Map the $type back to a LOG_ constant value, split multi-line messages into multiple
 * log messages, pass all messages through the format defined in the config
 *
 * @param string $type
 * @param mixed $message
 */
	public function write($type, $message) {
		if (!in_array($type, $this->_config['types'])) {
			return false;
		}
		if (!isset($this->_typeMap[$type])) {
			$priority = LOG_DEBUG;
		} else {
			$priority = $this->_typeMap[$type];
		}

		$messages = explode("\n", $message);
		foreach ($messages as $message) {
			$message = sprintf($this->_config['format'], $type, $message);
			$this->_write($priority, $message);
		}
	}

/**
 * _write
 *
 * Wrapper for syslog call - issolated to permit sub-classing and/or mocking for tests
 *
 * @param int $priority
 * @param sting $output
 * @return bool
 */
	protected function _write($priority, $output) {
		echo "\n$output\n";
		return syslog($priority, $output);
	}
}
