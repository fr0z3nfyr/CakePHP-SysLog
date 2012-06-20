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
		$this->_defaultConfig['formatter'] = 'basic';
		$config += $this->_defaultConfig;
		if (!is_callable($config['formatter'])) {
			$method = "_{$config['formatter']}Formatter";
			if (is_callable(array($this, $method))) {
				$config['formatter'] = array($this, $method);
			} else {
				trigger_error(__CLASS__ . "::$method doesn't exist, using _basicFormatter instead");
				$config['formatter'] = array($this, '_basicFormatter');
			}
		}
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

		$messages = call_user_func($this->_config['formatter'], $message, $this->_config['format'], $type);
		foreach ($messages as $message) {
			$this->_write($priority, $message);
		}
	}

/**
 * _basicFormatter
 *
 * Default formatter - adds a prefix only
 *
 * @param string $messages
 * @param string $format
 * @param string $type
 * @return array
 */
	protected function _basicFormatter($messages, $format, $type) {
		return array(sprintf($format, $type, $messages));
	}

/**
 * _prefixFormatter
 *
 * Splits all messages into an array, and adds a prefix to all lines
 *
 * @param string $messages
 * @param string $format
 * @param string $type
 * @return array
 */
	protected function _prefixFormatter($messages, $format, $type) {
		if (!is_array($messages)) {
			$messages = explode("\n", $messages);
		}

		foreach ($messages as &$message) {
			$message = sprintf($format, $type, $message);
		}
		return $messages;
	}

/**
 * _detailsFormatter
 *
 * Splits all messages into an array, and adds a prefix to all lines
 *
 * @param string $messages
 * @param string $format
 * @param string $type
 * @return array
 */
	protected function _detailsFormatter($messages, $format, $type) {
		$messages = explode("\n", $messages);
		if (!empty($this->_config['request'])) {
			$request = $this->_config['request'];
		} else {
			$request = "url: " . env('REQUEST_URI') . ' ip: ' . env('REMOTE_IP');
			$_POST = array('foo' => 'bar');
			if (!empty($_POST)) {
				$request .= 'post = ' . json_encode($_POST);
			}
		}
		array_splice($messages, 1, 0, $request);

		return $this->_prefixFormatter($messages, $format, $type);
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
		echo $output ."\n";

		return syslog($priority, $output);
	}
}
