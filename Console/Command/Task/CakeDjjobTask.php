<?php
App::uses('AppShell', 'Console/Command');
App::uses('CakeDjjobBridge', 'CakeDjjob.Lib');

/**
 * CakeDjjob Task
 * 
 * Wrapper around DJJob library for shells
 *
 * @copyright     Copyright 2011, Jose Diaz-Gonzalez. (http://josediazgonzalez.com)
 * @link          http://github.com/josegonzalez/cake_djjob
 * @package       cake_djjob
 * @subpackage    cake_djjob.shells.tasks
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/
class CakeDjjobTask extends AppShell {

/**
 * Contains configuration settings for use with individual model objects.
 * Individual model settings should be stored as an associative array,
 * keyed off of the model name.
 *
 * @var array
 * @access public
 * @see Model::$alias
 */
	var $settings = array(
		'connection'=> 'default',
		'type' => 'mysql',
	);

/**
 * Initiate CakeDjjob Task
 *
 * @param object $model
 * @param array $config
 * @return void
 * @access public
 */
	function configure($config) {
		CakeDjjobBridge::getInstance()->setup($this->settings, $config);
	}

/**
 * Returns a job
 * 
 * Auto imports and passes through the constructor parameters to newly created job
 * Note: (PHP 5 >= 5.1.3) - requires ReflectionClass if passing arguments
 *
 * @param string $jobName Name of job being loaded
 * @param mixed $argument Some argument to pass to the job
 * @param mixed ... etc.
 * @return mixed Job instance if available, null otherwise
 */
	function load() {
		$args = func_get_args();
		return CakeDjjobBridge::getInstance()->callLoad($args);

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
	function enqueue($job, $queue = "default", $run_at = null) {
		return CakeDjjobBridge::getInstance()->enqueue($job, $queue, $run_at);
	}

/**
 * Bulk Enqueues Jobs using DJJob
 *
 * @param array $jobs
 * @param string $queue
 * @param string $run_at
 * @return boolean True if bulk enqueue is successful, false on failure
 */
	function bulkEnqueue($jobs, $queue = "default", $run_at = null) {
		return CakeDjjobBridge::getInstance()->bulkEnqueue($job, $queue, $run_at);
	}

/**
 * Returns an array containing the status of a given queue
 *
 * @param string $queue
 * @return array
 **/
	function status($queue = "default") {
		return CakeDjjobBridge::getInstance()->status($queue);
	}
}
