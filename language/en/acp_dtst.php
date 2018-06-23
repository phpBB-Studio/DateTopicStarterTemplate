<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, array(
	// Locations
	'ACP_LOCATIONS_MANAGEMENT'				=> 'Here you can add/remove custom locations, each form works per se.',

	'ACP_DTST_LOCATION'						=> 'Preset location',
	'ACP_ADD_PRESET_LOCATION'				=> 'Add a new location',
	'ACP_ADD_PRESET_LOCATION_EXPLAIN'		=> 'No Emojis please, those will be automatically stripped.<br>Min. 1 max 86 chars allowed.',

	'ACP_REMOVE_PRESET_LOCATIONS'			=> 'Remove all locations',
	'ACP_REMOVE_PRESET_LOCATION_ALL'		=> 'Remove all preset locations at once',
	'ACP_REMOVE_PRESET_LOCATIONS_EXPLAIN'	=> 'This operation can not be undone!',
	'ACP_REMOVE_PRESET_LOCATIONS_CONFIRM'	=> 'Are you sure you wish to delete all locations?<br>This operation can not be undone!',

	'ACP_REMOVE_PRESET_LOCATION'			=> 'Remove a location',
	'ACP_REMOVE_PRESET_LOCATION_CONFIRM'	=> 'Are you sure you wish to delete this location?',
	'ACP_REMOVE_PRESET_LOCATION_EXPLAIN'	=> 'Choose a location to remove.',

	// Settings
	'ACP_SETTINGS_MANAGEMENT'				=> 'General settings',

	'ACP_SETTINGS_LOCKED_WITHDRAWAL'		=> 'Opting if locked',
	'ACP_SETTINGS_LOCKED_WITHDRAWAL_EXP'	=> 'Allow users to opting the event also if the Topic is locked.',

	'ACP_DTST_SIDEBAR'						=>	'Filters position',
	'ACP_DTST_SIDEBAR_EXPLAIN'				=>	'Where do you want the sidebar to be shown?',
	'ACP_DTST_SIDEBAR_LEFT'					=>	'Left',
	'ACP_DTST_SIDEBAR_RIGHT'				=>	'Right',

	'ACP_DTST_BOT'							=>	'PMs Bot',
	'ACP_DTST_BOT_TITLE'					=>	'Select an User/Bot',
	'ACP_DTST_USE_BOT'						=>	'Use PMs Bot',
	'ACP_DTST_USE_BOT_EXPLAIN'				=>	'If <strong>No</strong> been chosen then the Sender of the PMs will be the Host itself and the Poster in the Topics will be the same who has been accepted. - And the selection will be ignored.<br><br>Otherwise please choose an user with the dropdown box, which will be the Sender of the PMs and the Poster in the Topics, then select <strong>Yes</strong>.<br><br><strong>Note</strong>: the system will not overload the PMs inbox/sentbox folders of the Sender/Bot. Would be a good idea to create a fake-user for that, though.',

	// PMs
	'ACP_PRIVMSG_MANAGEMENT'				=> 'PMs settings',
	// Drop down
	'ACP_DTST_PM_MODE'						=> 'Select the status for the PM to edit',
	'ACP_DTST_PM_LANG'						=> 'Select the language for the PM to edit',
	'ACP_DTST_APPLICATION'					=> '1 - Application',
	'ACP_DTST_CANCELLATION'					=> '2 - Cancellation',
	'ACP_DTST_WITHDRAWAL'					=> '3 - Withdrawal',

	'dtst_tokens'	=> array(
		'SENDER_NAME'						=> 'The plain username of the Sender<br><em>The sender/Bot</em>',
		'RECIP_NAME'						=> 'The plain username of the Recipient<br><em>The Host</em>',
		'SENDER_NAME_FULL'					=> 'The username of the Sender with its color and clickable profile link.<br>(<em>Will be replaced by {SENDER_NAME} if used in PM Title</em>)',
		'RECIP_NAME_FULL'					=> 'The username of the Recipient with its color and clickable profile link.<br>(<em>Will be replaced by {RECIP_NAME} if used in PM Title</em>)',
		'P_LINK'							=> 'The clickable link to the Topic.<br>(<em>Not available in PM Title, replaced by nothing if used there</em>)',
		'P_TITLE'							=> 'The title of the Topic.<br>(<em>Also available in PM Title</em>)',
	),

	'ACP_DTST_PM_SETTINGS_EXPLAIN'			=>	'Changing any of the settings below will disregard any changes made to the current private message.',
	'ACP_DTST_PM_TOKENS_TITLE'				=>	'Tokens',
	'ACP_DTST_PM_TOKENS'					=>	'Understanding Tokens',
	'ACP_DTST_PM_TOKENS_EXPLAIN'			=>	'Here below you may copy & paste some <strong>tokens</strong> which should help you to compose the PM.',
	'ACP_DTST_PM_TOKEN'						=>	'Token',
	'ACP_DTST_PM_TOKEN_DEFINITION'			=>	'Definition and availability',
	'ACP_DTST_PM_INPUT'						=>	'Input your message and title',
	'ACP_DTST_PM_PREVIEW'					=>	'PM preview',
	'ACP_DTST_PM_EMOJIS'					=>	'Note, Emojis will be stripped.',

	'ACP_DTST_PM_TITLE_HOLDER'				=>	'No private message yet…',
	'ACP_DTST_PM_MESSAGE_HOLDER'			=>	'There is no private message yet for this language and status combination. Feel free to create one…',

	// errors
	'ACP_DTST_PM_STATUS_NONE'				=> 'You must specify a “Status” when editing a PM.',
	'ACP_DTST_PM_ISOCODE_NONE'				=> 'You must specify a “Language” when editing a PM.',
	'ACP_DTST_PM_TITLE_EMPTY'				=> 'You must specify a “Title” when editing a PM.',
	'ACP_DTST_PM_TITLE_LONG'				=> 'The “Title” you entered is too long.',
	'ACP_DTST_PM_MESSAGE_EMPTY'				=> 'You must specify a “Message” when editing a PM.',

	// preview
	'ACP_DTST_PM_TOKENS_PREVIEW'			=>	'Tokens (<em>{RECIP_NAME}, {RECIP_NAME_FULL}, {P_LINK}, {P_TITLE}</em>) are not parsed in some parts of the preview.',
));
