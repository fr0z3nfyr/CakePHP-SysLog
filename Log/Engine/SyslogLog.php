<?php
App::uses('BaseLog', 'SysLog.Log/Engine');

/**
 * SyslogLog
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
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
 * Whether there was any information logged during the request or not
 *
 * @var boolean
 **/
	protected $_written = false;

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
 * @param string $message
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
 * Wrapper for syslog call - isolated to permit sub-classing and/or mocking for tests
 *
 * @param int $priority
 * @param sting $output
 * @return bool
 */
	protected function _write($priority, $output) {
		$this->_written = true;
		return syslog($priority, $output);
	}

/**
 * Waits 200ms in order to give time syslog to deliver any data
 * on cli environment
 *
 * @return void
 **/
	public function __destruct() {
		if (PHP_SAPI === 'cli' && $this->_written) {
			usleep(200000);
		}
	}

}
