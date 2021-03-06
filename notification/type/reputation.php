<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\notification\type;

/**
 * @ignore
 */
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar's Reputation notification class.
 */
class reputation extends \phpbb\notification\type\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var string DTST Slots table */
	protected $dtst_slots;

	/**
	 * Set the user loader
	 * @param \phpbb\user_loader $user_loader
	 * @return void
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Set the DTST Slots table
	 * @param  string	DTST Slots table
	 * @return void
	 */
	public function set_slots_table($dtst_slots)
	{
		$this->dtst_slots = $dtst_slots;
	}

	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'phpbbstudio.dtst.notification.type.reputation';
	}

	/**
	 * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool True/False whether or not this is available to the user
	 */
	public function is_available()
	{
		return false;
	}

	/**
	 * Get the id of the notification
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the notification
	 */
	public static function get_item_id($data)
	{
		return $data['notification_id'];
	}

	/**
	 * Get the id of the parent
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the parent
	 */
	public static function get_item_parent_id($data)
	{
		return $data['topic_id'];
	}

	/**
	 * Find the users who want to receive notifications
	 *
	 * @param array $data		The type specific data
	 * @param array $options	Options for finding users for notification
	 * 							ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
	 * 							e.g.: array(2 => array(''), 3 => array('', 'email'), ...)
	 *
	 * @return array
	 */
	public function find_users_for_notification($data, $options = array())
	{
		// Grab all registered users (excluding bots and guests)
		$sql = 'SELECT u.user_id
				FROM ' . $this->dtst_slots . ' s
				JOIN ' . USERS_TABLE . ' u
					ON s.user_id = u.user_id
				WHERE u.user_type <> ' . USER_IGNORE . '
					AND s.topic_id = ' . (int) $data['topic_id'] . '
					AND s.dtst_status = ' . ext::DTST_STATUS_ACCEPTED;
		$result = $this->db->sql_query($sql);

		$users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[$row['user_id']] = $this->notification_manager->get_default_methods();
		}
		$this->db->sql_freeresult($result);

		return $users;
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		return array($this->get_data('user_id'));
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_id'), false, true);
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		$action = $this->get_data('mode');
		$name = $this->get_data('reputation_name');

		return $this->language->lang('DTST_NOTIFICATION_REPUTATION_' . utf8_strtoupper($action), $name);
	}

	/**
	* Get the HTML formatted reference of the notification
	*
	* @return string
	*/
	public function get_reference()
	{
		return $this->language->lang(
			'NOTIFICATION_REFERENCE',
			censor_text($this->get_data('topic_title'))
		);
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "f={$this->get_data('forum_id')}&t={$this->get_data('topic_id')}");
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	 * Function for preparing the data for insertion in an SQL query
	 * (The service handles insertion)
	 *
	 * @param array $data				The type specific data
	 * @param array $pre_create_data	Data from pre_create_insert_array()
	 *
	 * @return void|array				Array of data ready to be inserted into the database
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('mode', $data['mode']);
		$this->set_data('user_id', $data['user_id']);
		$this->set_data('topic_id', $data['topic_id']);
		$this->set_data('forum_id', $data['forum_id']);
		$this->set_data('topic_title', $data['topic_title']);
		$this->set_data('reputation_name', $data['reputation_name']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
