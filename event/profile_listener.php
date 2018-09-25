<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar Profile(s) listener.
 */
class profile_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $dtst_utils;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	protected $dtst_ranks;
	protected $dtst_reputation;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth				Authentication object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param  \phpbb\controller\helper				$helper				Controller helper object
	 * @param  \phpbb\config\config					$config				Configuration	object
	 * @param  \phpbb\language\language				$lang				Language object
	 * @param  \phpbb\log\log						$log				phpBB log
	 * @param  \phpbb\request\request				$request			Request object
	 * @param  \phpbb\template\template				$template			Template object
	 * @param  \phpbb\user							$user				User Object
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils			Functions to be used by Classes
	 * @param  string								$root_path			phpBB root path
	 * @param  string								$php_ext			php	File extension
	 * @param  string								$dtst_ranks			The	DTST ranks table
	 * @param  string								$dtst_reputation	The	DTST reputation table
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\config\config $config, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbbstudio\dtst\core\operator $dtst_utils, $root_path, $php_ext, $dtst_ranks, $dtst_reputation)
	{
		$this->auth				= $auth;
		$this->db				= $db;
		$this->helper			= $helper;
		$this->config			= $config;
		$this->lang				= $lang;
		$this->log				= $log;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
		$this->dtst_utils		= $dtst_utils;
		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->dtst_ranks		= $dtst_ranks;
		$this->dtst_reputation	= $dtst_reputation;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			/* ACP */
			'core.acp_users_modify_profile'				=>	'dtst_acp_users_modify_profile',
			'core.acp_users_profile_validate'			=>	'dtst_acp_users_profile_validate',
			'core.acp_users_profile_modify_sql_ary'		=>	'dtst_acp_users_profile_modify_sql_ary',
			/* Viewtopic */
			'core.viewtopic_cache_user_data'			=>	'dtst_viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'			=>	'dtst_viewtopic_modify_post_row',
			/* Profile view */
			'core.memberlist_view_profile'				=>	'dtst_view_profile',
			'core.memberlist_prepare_profile_data'		=>	'dtst_prepare_profile_data',
		);
	}

	/* ACP user profile */

	/**
	 * Modify user data on editing profile in ACP.
	 *
	 * @event  core.acp_users_modify_profile
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_users_modify_profile($event)
	{
		if ($this->auth->acl_get('a_dtst_admin'))
		{
			/* Includes specified language only in ACP */
			$this->lang->add_lang('acp_dtst', 'phpbbstudio/dtst');

			/* Request the form variables to work with */
			$dtst_reputation = $this->request->variable('dtst_reputation', 0);
			$action = $this->request->variable('dtst_action', '+');
			$reason = $this->request->variable('dtst_reason', '', true);

			/* No Emojis */
			$reason = $this->dtst_utils->dtst_strip_emojis($reason);

			$event['data'] = array_merge($event['data'], array(
				'dtst_reputation'	=> $dtst_reputation,
				'dtst_action'		=> $action,
				'dtst_reason'		=> $reason,
			));

			$this->template->assign_vars(array(
					'S_DTST_ACP'				=> true,
					'DTST_U_REP'				=> $event['user_row']['dtst_reputation'],
					'DTST_REP_ADJUST'			=> $dtst_reputation,
					'DTST_ACTION'				=> $action,
					'DTST_REASON'				=> $reason,
				)
			);
		}
	}

	/**
	 * Validate profile data in ACP before submitting to the database.
	 *
	 * @event  core.acp_users_profile_validate
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_users_profile_validate($event)
	{
		if (!function_exists('validate_data'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		/* Only do error checking when reputation was set */
		if ($event['data']['dtst_reputation'])
		{
			$validating = array(
				'dtst_reputation'	=> array('num', false, 1, ext::DTST_MAX_REP),
				'dtst_reason'		=> array('string', false, 1, 255),
			);

			$response = validate_data($event['data'], $validating);

			$event['error'] = array_merge($event['error'], $response);
		}

	}

	/**
	 * Modify profile data in ACP before submitting to the database.
	 *
	 * @event  core.acp_users_profile_modify_sql_ary
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_users_profile_modify_sql_ary($event)
	{
		if ($event['data']['dtst_reputation'])
		{
			$reputation = $event['user_row']['dtst_reputation'];

			switch ($event['data']['dtst_action'])
			{
				case '+':
					$reputation = $reputation + $event['data']['dtst_reputation'];
				break;
				case '-':
					$reputation = $reputation - $event['data']['dtst_reputation'];
				break;
			}

			$event['sql_ary'] = array_merge($event['sql_ary'], array(
				'dtst_reputation' => $reputation,
			));

			/* Add it to the DTST Reputation table */
			$sql = 'INSERT INTO ' . $this->dtst_reputation . ' ' . $this->db->sql_build_array('INSERT', array(
				'user_id'			=> (int) $this->user->data['user_id'],
				'recipient_id'		=> (int) $event['user_row']['user_id'],
				'reputation_action'	=> ext::DTST_REP_MOD,
				'reputation_points'	=> $reputation,
				'reputation_reason'	=> $event['data']['dtst_reason'],
				'reputation_time'	=> time(),
			));
			$this->db->sql_query($sql);

			/* Log it */
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_DTST_LOG_REPUTATION_UPDATED', false, array(
					$event['user_row']['username'],
					$event['user_row']['dtst_reputation'],
					$reputation,
			));

			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'ACP_DTST_LOG_REPUTATION_UPDATED_USER', false, array(
					'reportee_id' => $event['user_row']['user_id'],
					$event['user_row']['dtst_reputation'],
					$reputation,
			));
		}
	}

	/* Viewtopic */

	/**
	 * Modify the users' data displayed within their posts.
	 *
	 * @event  core.viewtopic_cache_user_data
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_viewtopic_cache_user_data($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['row']['forum_id']) )
		{
			/**
			 * Check permissions prior to run the code
			 */
			if ( (bool) $this->dtst_utils->is_authed() )
			{
				$array = $event['user_cache_data'];
				$array['dtst_reputation'] = $event['row']['dtst_reputation'];
				$array['dtst_rank_value'] = $event['row']['dtst_rank_value'];
				$array['user_lang'] = $event['row']['user_lang'];

				/**
				 * The migration created a field in the users table: dtst_reputation
				 * Sat as default to be empty for everyone
				 */
				$dtst_reputation = array();
				$dtst_reputation[] = ($array['dtst_reputation']) ? (int) $array['dtst_reputation'] : 0;
				$array = array_merge($array, $dtst_reputation);

				$dtst_rank_value = array();
				$dtst_rank_value[] = ($array['dtst_rank_value']) ? (int) $array['dtst_rank_value'] : 0;
				$array = array_merge($array, $dtst_rank_value);

				$user_lang = array();
				$user_lang[] = ($array['user_lang']) ? $array['user_lang'] : '';
				$array = array_merge($array, $user_lang);

				$event['user_cache_data'] = $array;
			}
		}
	}

	/**
	 * Modify the posts template block.
	 *
	 * @event  core.viewtopic_modify_post_row
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_viewtopic_modify_post_row($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['topic_data']['forum_id']) )
		{
			/**
			 * Check permissions prior to run the code
			 */
			if ( (bool) $this->dtst_utils->is_authed() )
			{
				/* Display the HTML only where the forum is enabled */
				$event['post_row'] = array_merge($event['post_row'], ['S_DTST_VT' => true]);

				$dtst_reputation	= (!empty($event['user_poster_data']['dtst_reputation'])) ? (int) $event['user_poster_data']['dtst_reputation'] : 0;
				$event['post_row']	= array_merge($event['post_row'], ['DTST_REPUTATION' => $dtst_reputation]);

				$dtst_rank_value	= ((int) $event['user_poster_data']['dtst_rank_value'] >= ext::DTST_RANK_MIN) ? (int) $event['user_poster_data']['dtst_rank_value'] : 0;
				$event['post_row']	= array_merge($event['post_row'], ['DTST_RANK_VALUE' => $dtst_rank_value]);

				$event['post_row']	= array_merge($event['post_row'], ['DTST_RANK_MAX' => ext::DTST_RANK_TEN]);
				$event['post_row']	= array_merge($event['post_row'], ['DTST_MAX_REP_POINTS' => ext::DTST_MAX_REP]);

				$event['post_row']	= array_merge($event['post_row'], ['U_DTST_REP_VIEW' => $this->helper->route('dtst_reputation_view', array('user_id' => (int) $event['poster_id']))]);

				/* Grab the sync'ed values for this */
				$percent_rank = $this->dtst_utils->percentage($event['user_poster_data']['dtst_reputation']);

				/* Get Ranks in their localised form or default to EN */
				list($dtst_rank_title, $dtst_rank_desc, $dtst_rank_bckg, $dtst_rank_text) = $this->dtst_utils->dtst_ranks_vars($percent_rank);

				/* Obtain a float value for rateYo */
				$percent = $this->dtst_utils->dtst_percent($dtst_reputation);

				/* Assign ranks to the template */
				$event['post_row'] = array_merge($event['post_row'], ['DTST_RANK_TITLE' => $dtst_rank_title]);
				$event['post_row'] = array_merge($event['post_row'], ['DTST_RANK_DESC' => $dtst_rank_desc]);
				$event['post_row'] = array_merge($event['post_row'], ['DTST_RANK_BCKG' => $dtst_rank_bckg]);
				$event['post_row'] = array_merge($event['post_row'], ['DTST_RANK_TEXT' => $dtst_rank_text]);
				/* Assign stars to the template */
				$event['post_row'] = array_merge($event['post_row'], ['PERCENT_RATEYO'	=> $this->dtst_utils->dtst_percent_rateyo($percent)]);
			}
		}
	}

	/* Viewprofile */

	/**
	 * Add DTST data to view profile, if available and allowed.
	 *
	 * @event  core.memberlist_view_profile
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_view_profile($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ( (bool) $this->dtst_utils->is_authed() )
		{
			/* Includes specified language only in UCP */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			/* Add DTST data to the already existing user data */
			$member = $event['member'];

			$dtst_reputation[] = (!empty($member['dtst_reputation'])) ? (int) $member['dtst_reputation'] : 0;
			$dtst_rank_value[] = ((int) $member['dtst_rank_value'] >= ext::DTST_RANK_MIN) ? (int) $member['dtst_rank_value'] : 0;

			$event['member'] = array_merge($member, $dtst_reputation);
		}
	}

	/**
	 * Add DTST template data to view profile.
	 *
	 * @event  core.memberlist_prepare_profile_data
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_prepare_profile_data($event)
	{
		$data = $event['data'];
		$template_data = $event['template_data'];

		$template_data['DTST_REPUTATION']		= (!empty($data['dtst_reputation'])) ? (int) $data['dtst_reputation'] : 0;
		$template_data['U_DTST_REP_VIEW']		= $this->helper->route('dtst_reputation_view', array('user_id' => (int) $data['user_id']));
		$template_data['DTST_MAX_REP_POINTS']	= ext::DTST_MAX_REP;

		// Not in use yet, testing purposes.
		$template_data['DTST_RANK_VALUE']		= ((int) $data['dtst_rank_value'] >= ext::DTST_RANK_MIN) ? (int) $data['dtst_rank_value'] : 0;// note: check what's that for as of now..
		$template_data['DTST_RANK_MAX']			= ext::DTST_RANK_TEN;

		/**
		 * Percentages for styling and rateYo
		 */
		$percent = $this->dtst_utils->dtst_percent((int) $data['dtst_reputation']);
		$degrees = (360 * $percent) / ext::DTST_MAX_REP_MULTIPLIER;
		$start = 90;

		$template_data['PERCENT']			= number_format((float) $percent, 2, '.', ',');
		$template_data['DEGREE']			= $percent > 50 ? $degrees - $start : $degrees + $start;
		$template_data['S_REP_AVAILABLE']	= ((int) $data['dtst_reputation'] == 0) ? false : true;

		/* Assign stars to the template */
		$template_data['PERCENT_RATEYO']	= $this->dtst_utils->dtst_percent_rateyo($percent);

		/* Grab the sync'ed values for ranks */
		$percent_rank = $this->dtst_utils->percentage($data['dtst_reputation']);
		/* Get Ranks in their localised form or default to EN */
		list($dtst_rank_title, $dtst_rank_desc, $dtst_rank_bckg, $dtst_rank_text) = $this->dtst_utils->dtst_ranks_vars($percent_rank);

		/* Assign ranks to the template */
		$template_data['DTST_RANK_TITLE']	= $dtst_rank_title;
		$template_data['DTST_RANK_DESC']	= $dtst_rank_desc;
		$template_data['DTST_RANK_BCKG']	= $dtst_rank_bckg;
		$template_data['DTST_RANK_TEXT']	= $dtst_rank_text;

		$event['template_data'] = $template_data;
	}
}
