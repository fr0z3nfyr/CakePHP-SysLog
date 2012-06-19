CakePHP Syslog
==============

A log engine for using syslog, designed for use with CakePHP 2.2+

To use add the following to your `app/Config/bootstrap.php` file

	CakeLog::config('error', array(
		'engine' => 'SysLog.SyslogLog',
		'types' => array('emergency', 'alert', 'critical', 'error'),
	));

Now whenever there is a log message of the configured types - it'll be sent to syslog

If you want to change the format of the messages, you can define a format parameters in the config call:

	CakeLog::config('error', array(
		'engine' => 'SysLog.SyslogLog',
		'types' => array('emergency', 'alert', 'critical', 'error'),
		'format' => 'my-prefix %s: %s'
	));

The two placehodlers in the format are replaced with the type of message (e.g. "error") and the message itself.
