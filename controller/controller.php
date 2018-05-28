<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\user;
use phpbb\request\request;
use phpbb\language\language;
use phpbbstudio\dtst\core\operator;

class controller
{
	protected $auth;
	protected $config;
	protected $db;
	protected $user;
	protected $request;
	protected $dtst_slots;
	protected $lang;
	protected $dtst_utils;

	/**
	* Constructor
	*/
	public function __construct(auth $auth, config $config, driver_interface $db, user $user, request $request, $dtst_slots, language $lang, operator $dtst_utils)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db 			= $db;
		$this->user			= $user;
		$this->request		= $request;
		$this->dtst_slots	= $dtst_slots;
		$this->lang			= $lang;
		$this->dtst_utils	= $dtst_utils;
	}

	/**
	 * Date Topic Event Calendar controller handler
	 *
	 * @param $topic_id
	 * @access public
	 */
	public function handle($topic_id)
	{
		/* First check if the user is even authed */
		if (!$this->dtst_utils->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* Check if a topic was found */
		if (!$topic_id)
		{
			throw new \phpbb\exception\http_exception(404, 'DTST_TOPIC_NOT_FOUND');
		}

		/* Enforce a data type */
		$topic_id = (int) $topic_id;

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Set up a JSON response */
		$json_response = new \phpbb\json_response();

		/* Set up variables */
		$participants_count = $withdrawals_count = 0;
		$user_attending = $user_active = false;
		$users = array();

		$sql = $this->dtst_utils->dtst_slots_query($topic_id);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Increment active user count */
			if ($row['active'])
			{
				$participants_count++;
			}
			else
			{
				$withdrawals_count++;
			}

			/* Check current user status */
			if ($row['user_id'] == $this->user->data['user_id'])
			{
				$user_attending = true;
				$user_active = (bool) $row['active'];
			}

			/* Add this row to the users array, indexed by the cleaned username */
			$users[$row['username_clean']] = $row;
		}

		$this->db->sql_freeresult($result);

		/**
		 * There are 3 scenarios.
		 *
		 * 1 user is already attending: withdrawing
		 * 2 user is already attending but inactive: reattending
		 * 3 user is not yet attending: attending
		 */
		$scenario = $user_attending ? ($user_active ? 'withdrawing' : 'reattending') : 'attending';

		switch ($scenario)
		{
			case 'withdrawing':
				$opt_sql = 'UPDATE ' . $this->dtst_slots . ' SET active = 0, inactive = 1, post_time = ' . time() . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND topic_id = ' . $topic_id;

				$message = $this->lang->lang('DTST_OPTED_OUT');
				$button_text = $this->lang->lang('DTST_OPT_REATTEND');
				$user_status = $this->lang->lang('DTST_STATUS_WITHDRAWN');

				$participants_count--;
				$withdrawals_count++;
			break;

			case 'reattending':
				$opt_sql = 'UPDATE ' . $this->dtst_slots . ' SET active = 1, inactive = 0, post_time = ' . time() . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND topic_id = ' . $topic_id;

				$message = $this->lang->lang('DTST_OPTED_IN');
				$button_text = $this->lang->lang('DTST_OPT_WITHDRAW');
				$user_status = $this->lang->lang('DTST_STATUS_ATTENDING');

				$participants_count++;
				$withdrawals_count--;
			break;

			case 'attending':
				$data = array(
					'user_id'	=> (int) $this->user->data['user_id'],
					'topic_id'	=> $topic_id,
					'active'	=> true,
					'inactive'	=> false,
					'post_time'	=> time(),
				);

				$opt_sql = 'INSERT INTO ' . $this->dtst_slots . ' ' . $this->db->sql_build_array('INSERT', $data);

				$message = $this->lang->lang('DTST_OPTED_IN');

				$button_text = $this->lang->lang('DTST_OPT_WITHDRAW');
				$user_status = $this->lang->lang('DTST_STATUS_ATTENDING');

				$participants_count++;
			break;
		}

		/* Execute the SQL */
		$this->db->sql_query($opt_sql);

		/* Grab the new user lists */
		$lists = $this->build_user_list($users, $scenario);

		$json_response->send(array(
			'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
			'MESSAGE_TEXT'		=> $message,

			'DTST_BUTTON_TEXT'		=> $button_text,
			'DTST_USER_STATUS'		=> $user_status,

			'DTST_PARTICIPANTS_LIST'	=> $lists['participants'],
			'DTST_PARTICIPANTS_COUNT'	=> $participants_count,
			'DTST_WITHDRAWALS_LIST'		=> $lists['withdrawals'],
			'DTST_WITHDRAWALS_COUNT'	=> $withdrawals_count,
		));
	}

	/**
	 * Yeah, builds the user list
	 *
	 * @param $users
	 * @param $scenario		switch
	 * @access private
	 */
	private function build_user_list($users, $scenario)
	{
		switch ($scenario)
		{
			case 'withdrawing':
				/* Set this user to inactive */
				$users[$this->user->data['username_clean']]['active'] = false;
			break;

			case 'reattending':
				/* Set this user as active */
				$users[$this->user->data['username_clean']]['active'] = true;
			break;

			case 'attending':
				/* Add this user to the list */
				$users = array_merge($users, array(
					$this->user->data['username_clean'] => array(
						'user_id'		=> $this->user->data['user_id'],
						'username'		=> $this->user->data['username'],
						'user_colour'	=> $this->user->data['user_colour'],
						'active'		=> true,
						'post_time'		=> time(),
					)
				));

				/* Resort the users list per keys */
				ksort($users, SORT_STRING);
			break;
		}

		$participants_list = $withdrawals_list = '';

		foreach ($users as $row)
		{
			$username = $this->auth->acl_get('u_viewprofile') ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);
			$user_time = $this->user->format_date((int) $row['post_time'], $this->config['default_dateformat']);

			if ($row['active'])
			{
				$participants_list .= '<span title="' . $this->lang->lang('DTST_STATUS_ATTENDING') . $this->lang->lang('COLON') . ' ' . $user_time . '">';
				$participants_list .= $username;
				$participants_list .= '</span>';
				$participants_list .= ', ';
			}
			else
			{
				$withdrawals_list .= '<span title="' . $this->lang->lang('DTST_STATUS_WITHDRAWN') . $this->lang->lang('COLON') . ' ' . $user_time . '">';
				$withdrawals_list .= $username;
				$withdrawals_list .= '</span>';
				$withdrawals_list .= ', ';
			}
		}

		/* Remove the last comma from the lists */
		$participants_list = rtrim($participants_list, ', ');
		$withdrawals_list = rtrim($withdrawals_list, ', ');

		/* Check if any list is empty, if so, return the empty language string */
		$participants_list = $participants_list !== '' ? $participants_list : $this->lang->lang('DTST_NO_ATTENDEES');
		$withdrawals_list = $withdrawals_list !== '' ? $withdrawals_list : $this->lang->lang('DTST_NO_WITHDRAWALS');

		return array(
			'participants'	=> $participants_list,
			'withdrawals'	=> $withdrawals_list
		);
	}
}
