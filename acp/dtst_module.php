<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\acp;

/**
 * Date Topic Event Calendar ACP module.
 */
class dtst_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * @param $id
	 * @param $mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		$config_text = $phpbb_container->get('config_text');
		$language = $phpbb_container->get('language');
		$phpbb_log = $phpbb_container->get('log');
		$request = $phpbb_container->get('request');
		$template = $phpbb_container->get('template');
		$user = $phpbb_container->get('user');

		$dtst_utils = $phpbb_container->get('phpbbstudio.dtst.dtst_utils');

		/* Add our lang file */
		$language->add_lang('acp_dtst', 'phpbbstudio/dtst');

		/* Set the template file */
		$this->tpl_name = 'dtst_body';

		/* Set the page title */
		$this->page_title = $language->lang('ACP_DTST_TITLE');

		/* Request the action */
		$action = $request->variable('action', '');

		/* Do this now and forget */
		$errors = array();

		/* Add a form key for security */
		add_form_key('phpbbstudio_dtst');

		switch ($action)
		{
			case 'add':
				if ($submit = $request->is_set_post('forum_dtst_preset_location_addition'))
				{
					/* Pull the array from the DB */
					$forum_dtst_preset_location = $dtst_utils->dtst_json_decode_locations();

					/* Get locations values from the form */
					$forum_dtst_preset_location_add[] = trim(htmlspecialchars($request->variable('forum_dtst_preset_location_add', '', true), ENT_COMPAT, 'UTF-8'));

					if (!check_form_key('phpbbstudio_dtst'))
					{
						trigger_error('FORM_INVALID');
					}

					/* Convert to string the array first */
					$string = implode(" ", $forum_dtst_preset_location_add);

					/* No Emojis */
					$string = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $string);

					/**
					 * The first element of the array can not be overwritten despite is empty
					 * Search through the array if the user input already exist
					 */
					if (in_array($string, $forum_dtst_preset_location))
					{
						$errors[] = $language->lang('ACP_DTST_ERR_LOCATION_EXISTS');
					}

					/**
					 * Check the number of max chars allowed.
					 * https://en.wikipedia.org/wiki/List_of_long_place_names
					 */
					if (utf8_strlen($string) >= 86)
					{
						$errors[] = $language->lang('ACP_DTST_ERR_LONG_LOCATION');
					}

					/**
					 * Check the number of min chars allowed. 
					 * https://en.wikipedia.org/wiki/List_of_short_place_names
					 */
					if (utf8_strlen($string) < 1)
					{
						$errors[] = $language->lang('ACP_DTST_ERR_SHORT_LOCATION');
					}

					/* No errors? Great, let's go. */
					if (!count($errors))
					{
						/* No Emojis for Logs */
						$forum_dtst_preset_location_add = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $forum_dtst_preset_location_add);

						/* Correctly adds the new location at the end of the list, to be sorted later */
						$config_text->set('dtst_locations', json_encode(array_merge($forum_dtst_preset_location, $forum_dtst_preset_location_add)));

						/* Log the action */
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_LOCATION_ADDED', time(), $forum_dtst_preset_location_add);

						/* Show success message */
						trigger_error($language->lang('ACP_DTST_LOCATION_ADDED') . adm_back_link($this->u_action));
					}
				}
			break;

			case 'remove':
				if ($submit = $request->is_set_post('forum_dtst_preset_location_remove'))
				{
					/* Pull the array from the DB */
					$forum_dtst_preset_location = $dtst_utils->dtst_json_decode_locations();

					/* Get locations values from the form */
					$forum_dtst_preset_location_rem[] = trim($request->variable('forum_dtst_preset_location_rem', '', true));

					if (!check_form_key('phpbbstudio_dtst'))
					{
						trigger_error('FORM_INVALID');
					}

					/* Convert to string the array first */
					$string = implode(" ", $forum_dtst_preset_location_rem);

					/* The first element of the array can not be deleted */
					if (empty($string))
					{
						$errors[] = $language->lang('ACP_DTST_ERR_LOCATION_EMPTY');
					}

					/* No errors? Great, let's go. */
					if (!count($errors))
					{
						/* No Emojis for Logs */
						$forum_dtst_preset_location_rem = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $forum_dtst_preset_location_rem);

						/* Search through the array if the user input already exist */
						if (in_array($string, $forum_dtst_preset_location))
						{
							/* Remove the element and reindex the numerical array as it should be */
							$forum_dtst_preset_location = array_merge(array_diff($forum_dtst_preset_location, $forum_dtst_preset_location_rem));
						}

						/* Correctly encode back our array and store it */
						$config_text->set('dtst_locations', json_encode($forum_dtst_preset_location));

						/* Log the action */
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_LOCATION_REMOVED', time(), $forum_dtst_preset_location_rem);

						/* Show success message */
						trigger_error($language->lang('ACP_DTST_LOCATION_REMOVED') . adm_back_link($this->u_action));
					}
				}
			break;
		}

		$template->assign_vars([
			'S_ERRORS'				=> ($errors) ? true : false,
			'ERRORS_MSG'			=> implode('<br /><br />', $errors),

			'U_ACTION_ADD'			=> $this->u_action . '&action=add',
			'U_ACTION_REMOVE'		=> $this->u_action . '&action=remove',

			/* The function is already "htmlspecialchars_decode"'d */
			'DTST_LOCATION'			=> $dtst_utils->dtst_location_preset_select(),
		]);
	}
}
