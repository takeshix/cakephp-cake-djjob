<?php
App::uses('AppShell', 'Console/Command');
App::uses('CakeDjjobBridge', 'CakeDjjob.Lib');

/**
 * Convenience method to unserialize CakeDjjob classes properly
 *
 * Uses _ (underscore) to denote plugins, since php classnames
 * cannot use periods
 *
 * @package default
 */
function unserialize_jobs($className) {
	$plugin = null;
	if (strstr($className, '_')) {
		list($plugin, $className) = explode('_', $className);
	}

	if (empty($className)) {
		$className = $plugin;
		$plugin = null;
	}

	if (!empty($plugin)) {
		$plugin = "{$plugin}.";
	}

	if (!class_exists($className)) {
		App::uses($className, "{$plugin}Job");
	}
}

/**
 * Worker runs jobs created by the DJJob system.
 *
 * @package job_workers
 */
class WorkerShell extends AppShell {

	public $tasks = array(
		'CakeDjjob.Cleanup', 'CakeDjjob.Run', 'CakeDjjob.Status',
		'CakeDjjob.LockStatus', 'CakeDjjob.ForceUnlockAll',
		"CakeDjjob.FailedStatus",
	);

/**
 * Override startup
 *
 * @access public
 */
	public function startup() {
		parent::startup();
		ini_set('unserialize_callback_func', 'unserialize_jobs');
		CakeDjjobBridge::getInstance()->setup($this->params);
	}

/**
 * Override main() for help message hook
 *
 * @return void
 */
	public function main() {
		$this->out(__d('cake_djjob', '<info>CakeDjjob Worker Shell</info>'));
		$this->hr();
		$this->out(__d('cake_djjob', '[R]un jobs in the system'));
		$this->out(__d('cake_djjob', '[S]tatus of system'));
		$this->out(__d('cake_djjob', '[C]leans a job queue'));
		$this->out(__d('cake_djjob', '[L]ocked queue list'));
		$this->out(__d('cake_djjob', '[U]pdate job queues from locked state to unlocked state'));
		$this->out(__d('cake_djjob', '[F]ailed queue list'));
		$this->out(__d('cake_djjob', '[Q]uit'));

		$choice = strtolower($this->in(__d('cake_djjob', 'What would you like to do?'), array('R', 'S', 'C', 'L', 'U', 'F', 'Q')));
		switch ($choice) {
			case 'r':
				$this->Run->execute();
			break;
			case 's':
				$this->Status->execute();
			break;
			case 'c':
				$this->Cleanup->params["action"] = "clean";
				$this->Cleanup->params["save"] = true;
				$this->Cleanup->execute();
			break;
			case 'l':
				$this->LockStatus->execute();
			break;
			case 'u':
				$choice = strtolower($this->in(__d('cake_djjob', 'Really ?'), array('Y', 'N')));
				if ($choice == "y") {
					$this->ForceUnlockAll->execute();
				}
			break;
			case 'f':
				$this->FailedStatus->execute();
			break;
			case 'q':
				exit(0);
			break;
			default:
				$this->out(__d('cake_djjob', 'You have made an invalid selection. Please choose a command to execute by entering R, S, C, or Q.'));
		}
		$this->hr();
		$this->main();
	}

/**
 * Get and configure the Option parser
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(
			__d('cake_djjob', 'The Worker Shell runs jobs created by the DJJob system.')
		)->addOptions(array(
			'connection' => array(
				'help' => __('Set db config'),
				'default' => 'default',
			),
			'type' => array(
				'help' => __('PDO name for connection <type>'),
				'default' => 'mysql',
			),
			'debug' => array(
				'help' => __('Set debug level dynamically for running jobs'),
				'default' => 0,
				'choices' => array(0, 1, 2)
			),
			'queue' => array(
				'help' => __('Queue <name> to pul jobs from'),
				'default' => 'default',
			),
		))->addSubcommand('run', array(
			'help' => __d('cake_djjob', 'runs jobs in the system'),
			'parser' => $this->Run->getOptionParser()
		))->addSubcommand('status', array(
			'help' => __d('cake_djjob', 'returns the status of a job queue'),
			'parser' => $this->Status->getOptionParser()
		))->addSubcommand('cleanup', array(
			'help' => __d('cake_djjob', 'cleans a job queue'),
			'parser' => $this->Cleanup->getOptionParser()
		))->addSubcommand('lock_status', array(
			'help' => __d('cake_djjob', 'return list of locked job queues'),
			'parser' => $this->LockStatus->getOptionParser()
		))->addSubcommand('force_unlock_all', array(
			'help' => __d('cake_djjob', 'reset a locked job queue'),
			'parser' => $this->ForceUnlockAll->getOptionParser()
		))->addSubcommand('failed_status', array(
			'help' => __d('cake_djjob', 'return list of locked job queues'),
			'parser' => $this->FailedStatus->getOptionParser()
		));
	}

}
