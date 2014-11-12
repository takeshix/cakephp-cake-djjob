<?php
App::uses('ConnectionManager', 'Model');
App::uses('CakeJob', 'CakeDjjob.Job');
if (!class_exists('DJJob')) {
	App::uses('DJJob', 'Djjob.Vendor');
}

class CakeDjjobBridge {
	protected static $_instance = null;
	public $settings = array();

	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new static();
		}
		return self::$_instance;
	}

	protected function __construct() {
	}

	public function setup($settings, $config = array()) {
		$settings = array_merge($settings, $config);
		$this->settings = $settings;
		$connection = ConnectionManager::getDataSource($settings['connection']);

		if ($settings['type'] == 'mysql' || !empty($connection)) {
			if ($connection->getConnection() == null) {
				$connection->connect();
			}
			DJJob::setConnection($connection->getConnection());
		} else {
			DJJob::configure(
				implode(';', array(
					"{$settings['type']}:host={$connection->config['host']}",
					"dbname={$connection->config['database']}",
					"port={$connection->config['port']}",
					"user={$connection->config['login']}",
					"password={$connection->config['password']}"
				))
			);
		}
	}

	public function callLoad($args) {
		return call_user_func_array(array($this, 'load'), $args);
	}

	public function load() {
		$args = func_get_args();
		array_shift($args);

		if (empty($args) || !is_string($args[0])) {
			return null;
		}

		$jobName = array_shift($args);
		list($plugin, $className) = pluginSplit($jobName);
		if ($plugin) {
			$plugin = "{$plugin}.";
		}

		if (!class_exists($className)) {
			App::uses($className, "{$plugin}Job");
		}

		if (empty($args)) {
			return new $className();
		}

		if (!class_exists('ReflectionClass')) {
			return null;
		}

		$ref = new ReflectionClass($className);
		return $ref->newInstanceArgs($args);
	}

	/**
	 * Enqueues Jobs using DJJob
	 *
	 * Note that all Jobs enqueued using this system must extend the base CakeJob
	 * class which is included in this plugin
	 *
	 * @param Job $job
	 * @param string $queue
	 * @param string $run_at
	 * @return boolean True if enqueue is successful, false on failure
	 */
		public function enqueue($job, $queue = "default", $run_at = null) {
			return DJJob::enqueue($job, $queue, $run_at);
		}

	/**
	 * Bulk Enqueues Jobs using DJJob
	 *
	 * @param array $jobs
	 * @param string $queue
	 * @param string $run_at
	 * @return boolean True if bulk enqueue is successful, false on failure
	 */
		public function bulkEnqueue($jobs, $queue = "default", $run_at = null) {
			return DJJob::bulkEnqueue($jobs, $queue, $run_at);
		}

	/**
	 * Returns an array containing the status of a given queue
	 *
	 * @param string $queue
	 * @return array
	 **/
		public function status($queue = "default") {
			return DJJob::status($queue);
		}

}