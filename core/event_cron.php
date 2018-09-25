<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\core;

/**
 * @ignore
 */
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar's Event cron.
 */
class event_cron
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbbstudio\dtst\core\reputation_functions */
	protected $rep_func;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config							$config		Configuration object
	 * @param  \phpbb\log\log								$log		Log object
	 * @param  \phpbbstudio\dtst\core\reputation_functions	$rep_func	DTST Reputation functions
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\log\log $log, \phpbbstudio\dtst\core\reputation_functions $rep_func)
	{
		$this->config	= $config;
		$this->log		= $log;
		$this->rep_func	= $rep_func;
	}

	/**
	 * Run DTST cron.
	 *
	 * @return void
	 * @access public
	 */
	public function run()
	{
		/* Check if we have to run the cron */
		//			return;
		if (!$this->should_run())
		{
			return;
		}

		/* Check for hosts that have not replied */
		$hosts = $this->rep_func->no_replies($this->get_host_time());

		/* Check for events that have ended */
		$events_ended = $this->rep_func->event_ended($this->get_today());

		/* Check for events' reputation period that have ended */
		$events_reputation_ended = $this->rep_func->reputation_ended($this->get_reputation_time());

		/* Log hosts that have not replied */
		if ($hosts)
		{
			/* If there is a DTST Bot, we use that. Otherwise use the Anonymous user */
			$log_user_id = ((bool) $this->config['dtst_use_bot']) ? (int) $this->config['dtst_bot'] : ANONYMOUS;

			foreach ($hosts as $host)
			{
				$this->log->add('mod', $log_user_id, '', 'ACP_DTST_LOG_HOST_NO_REPLY', false, array(
					'topic_id'	=> (int) $host['topic_id'],
					'topic_title',
					'host',
					'user',
				));
			}
		}

		/* Log events that have ended */
		if ($events_ended)
		{
			foreach ($events_ended as $topic_id => $event)
			{
				$this->log_event('ACP_DTST_LOG_EVENT_ENDED', $topic_id, $event['title']);
			}
		}

		/* Log events' reputation period that have ended */
		if ($events_reputation_ended)
		{
			foreach ($events_reputation_ended as $topic_id => $event)
			{
				$this->log_event('ACP_DTST_LOG_REPUTATION_ENDED', $topic_id, $event['title']);
			}
		}

		$this->config->set('dtst_event_checked', $this->get_today(), false);

		return;
	}

	/**
	 * Check if DTST cron should run.
	 *
	 * @return bool
	 * @access private
	 */
	private function should_run()
	{
		return (bool) ($this->config['dtst_event_checked'] < $this->get_today());
	}

	/**
	 * Get the UNIX timestamp for midnight today (00:00:01 am).
	 *
	 * @return int
	 * @access private
	 */
	private function get_today()
	{
		return (int) strtotime('today', $this->get_time());
	}

	/**
	 * Get the UNIX timestamp for reputation period.
	 *
	 * @return int
	 * @access private
	 */
	private function get_reputation_time()
	{
		return (int) strtotime('-' . ($this->config['dtst_rep_time'] / ext::ONE_DAY) . ' days midnight', $this->get_time());
	}

	/**
	 * Get the UNIX timestamp for host reply period.
	 *
	 * @return int
	 * @access private
	 */
	private function get_host_time()
	{
		return (int) strtotime('-' . ($this->config['dtst_host_time'] / ext::ONE_DAY) . ' days midnight', $this->get_time());
	}

	/**
	 * Get the current UNIX timestamp with the board's default timezone.
	 *
	 * @return int
	 * @access private
	 */
	private function get_time()
	{
		date_default_timezone_set($this->config['board_timezone']);
		return (int) time();
	}

	/**
	 * Add a log entry for an event that has ended.
	 *
	 * @param  string		$operation		The log operation
	 * @param  int			$topic_id		The event identifier
	 * @param  string		$topic_title	The event title
	 * @return void
	 * @access private
	 */
	private function log_event($operation, $topic_id, $topic_title)
	{
		/* If there is a DTST Bot, we use that. Otherwise use the Anonymous user */
		$user_id = ((bool) $this->config['dtst_use_bot']) ? (int) $this->config['dtst_bot'] : ANONYMOUS;
		$user_ip = '';
		$data = array(
			'topic_id'	=> (int) $topic_id,
			$topic_title,
		);

		$this->log->add('mod', $user_id, $user_ip, $operation, false, $data);
	}
}
