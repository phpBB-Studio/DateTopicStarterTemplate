<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\migrations;

class install_configs extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return array(
			/* (BOOL) Topic locked withdrawal 1 = allow opting if locked - 0 = disallow */
			array('config.add', array('dtst_locked_withdrawal', 1)),
			/* (INT) notifications sent counter */
			array('config.add', array('dtst_notification_id', 0)),
			/* (INT) reputation notifications sent counter */
			array('config.add', array('dtst_rep_notification_id', 0)),
			/* (BOOL) Sidebar position 1 = left - 0 = right */
			array('config.add', array('dtst_sidebar', 1)),
			/* (INT) Bot user ID default - 2 founder */
			array('config.add', array('dtst_bot', 2)),
			/* (BOOL) Use of the Bot - 1 = yes - 0 = No */
			array('config.add', array('dtst_use_bot', 0)),

			// Core
			array('config.add', array('dtst_event_checked', 0, true)),			// (INT) Timestamp when 'dtst cron' last ran
			// Reputation settings
			array('config.add', array('dtst_rep_name', 'Reputation')),			// (STRING) The name for reputation
			array('config.add', array('dtst_rep_time', 604800)),				// 60 * 60 * 24 * 7 (1 week in seconds)
			array('config.add', array('dtst_host_time', 604800)),				// Time for the host to respond to an application (60 * 60 * 24 * 7 (1 week in seconds))
			array('config.add', array('dtst_rep_count_up', 5)),					// (INT) Amount of thumbs up per user per event
			array('config.add', array('dtst_rep_count_down', 5)),				// (INT) Amount of thumbs down per user per event
			array('config.add', array('dtst_rep_count_good', 5)),				// (INT) Amount of good conducts per host per event
			array('config.add', array('dtst_rep_count_bad', 5)),				// (INT) Amount of bad conducts per host per event
		//array('config.add', array('dtst_rep_rank_starter', 60)),								// NOT IN USE - (INT) Amount of Rank's percent as default
		//array('config.add', array('dtst_rep_points_min', -10000)),			// (INT) ¿?¿?¿?¿?
		//array('config.add', array('dtst_rep_points_max', 10000)),								// not in use 			// (INT) todo
			array('config.add', array('dtst_show_rep_points', 1)),				// (BOOL) Display reputation points
			array('config.add', array('dtst_show_rep_rank', 1)),				// (BOOL) Display reputation ranks
			array('config.add', array('dtst_show_mod_anon', 1)),				// (BOOL) Moderator anonimity (when set to yes the language string "Moderator" will be used instead of the moderator's username)
		//array('config.add', array('dtst_show_reason_anon', 1)),				// (BOOL) Author of reasons anonimity (when set to yes the language string "Attendee" will be used instead of the Author's username)
			array('config.add', array('dtst_rep_users_page', 12)),				// (INT) How many users to show per pagination
			// Reputation values
			array('config.add', array('dtst_rep_points_up', 5)),				// (INT) Points for thumbs up
			array('config.add', array('dtst_rep_points_down', -5)),				// (INT) Points for thumbs down
			array('config.add', array('dtst_rep_points_noshow', -10)),			// (INT) Points for no show
			array('config.add', array('dtst_rep_points_host', 10)),				// (INT) Points for hosting an event
			array('config.add', array('dtst_rep_points_attend', 5)),			// (INT) Points for attending an event
			array('config.add', array('dtst_rep_points_good', 5)),				// (INT) Points for good conduct
			array('config.add', array('dtst_rep_points_bad', -5)),				// (INT) Points for bad conduct
			array('config.add', array('dtst_rep_points_withdraw', -5)),			// (INT) Points for withdrawing from an event
			array('config.add', array('dtst_rep_points_noreply', -5)),			// (INT) Points for a host not responding to an application
			array('config.add', array('dtst_rep_points_cancel_event', -10)),	// (INT) Points for a host canceling an event
		);
	}
}
