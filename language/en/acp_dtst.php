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

/**
* Some characters you may want to copy&paste: ’ » “ ” …
*/

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

	// PMs explain
	'ACP_PRIVMSG_MANAGEMENT'				=> 'PMs settings',
	// PMs Drop down
	'ACP_DTST_PM_MODE'						=> 'Select the status for the PM to edit',
	'ACP_DTST_PM_LANG'						=> 'Select the language for the PM to edit',
	'ACP_DTST_APPLICATION'					=> '1 - Application',
	'ACP_DTST_CANCELLATION'					=> '2 - Cancellation',
	'ACP_DTST_WITHDRAWAL'					=> '3 - Withdrawal',
	// PMs tokens
	'dtst_tokens'	=> array(
		'SENDER_NAME'						=> 'The plain username of the Sender<br><em>The sender/Bot</em>',
		'RECIP_NAME'						=> 'The plain username of the Recipient<br><em>The Host</em>',
		'SENDER_NAME_FULL'					=> 'The username of the Sender with its color and clickable profile link.<br>(<em>Will be replaced by {SENDER_NAME} if used in PM Title</em>)',
		'RECIP_NAME_FULL'					=> 'The username of the Recipient with its color and clickable profile link.<br>(<em>Will be replaced by {RECIP_NAME} if used in PM Title</em>)',
		'P_LINK'							=> 'The clickable link to the Topic.<br>(<em>Not available in PM Title, replaced by nothing if used there</em>)',
		'P_TITLE'							=> 'The title of the Topic.<br>(<em>Also available in PM Title</em>)',
	),
	// PMs settings
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
	// Pms errors
	'ACP_DTST_PM_STATUS_NONE'				=> 'You must specify a “Status” when editing a PM.',
	'ACP_DTST_PM_ISOCODE_NONE'				=> 'You must specify a “Language” when editing a PM.',
	'ACP_DTST_PM_TITLE_EMPTY'				=> 'You must specify a “Title” when editing a PM.',
	'ACP_DTST_PM_TITLE_LONG'				=> 'The “Title” you entered is too long.',
	'ACP_DTST_PM_MESSAGE_EMPTY'				=> 'You must specify a “Message” when editing a PM.',
	// Pms preview
	'ACP_DTST_PM_TOKENS_PREVIEW'			=>	'Tokens (<em>{RECIP_NAME}, {RECIP_NAME_FULL}, {P_LINK}, {P_TITLE}</em>) are not parsed in some parts of the preview.',

	// Reputation Settings explain
	'ACP_REP_SETTINGS_MANAGEMENT'			=>	'General Reputation Settings',
	// Reputation Settings legends
	'ACP_DTST_REP_TIMELINES'				=>	'Timelines',
	'ACP_DTST_REP_REP_POINTS'				=>	'Range of reputation’s points',
	'ACP_DTST_REP_AMOUNTS'					=>	'Amounts',
	'ACP_DTST_REP_DISPLAY'					=>	'Reputation visibility',
	'ACP_DTST_REP_VARIOUSES'				=>	'Extra settings',
	// Reputation Settings timelines's drop down(s)
	'ACP_DTST_ONE_DAY'						=>	'one day',
	'ACP_DTST_TWO_DAYS'						=>	'two days',
	'ACP_DTST_THREE_DAYS'					=>	'three days',
	'ACP_DTST_ONE_WEEK'						=>	'one week',
	'ACP_DTST_TWO_WEEKS'					=>	'two weeks',
	'ACP_DTST_ONE_MONTH'					=>	'one month',
	// Reputation Settings
	'ACP_DTST_REP_HOST_TIME'				=>	'The Host for reply to an application',
	'ACP_DTST_REP_HOST_TIME_EXPLAIN'		=>	'Days since.',
	'ACP_DTST_REP_TIME'						=>	'The attendees for giving out reputation',
	'ACP_DTST_REP_TIME_EXPLAIN'				=>	'Days after the event.',
	'ACP_DTST_REP_NAME'						=>	'Reputation name',
	'ACP_DTST_REP_NAME_EXPLAIN'				=>	'The name used for the reputation points.',
	//'ACP_DTST_REP_RANK_STARTER'				=>	'Starting ranks percent per user',
	//'ACP_DTST_REP_RANK_STARTER_EXPLAIN'		=>	'Range 0 to 120.',
	'ACP_DTST_REP_COUNT_UP'					=>	'Thumbs-up per user per event',
	'ACP_DTST_REP_COUNT_UP_EXPLAIN'			=>	'Range 1 to unlimited.',
	'ACP_DTST_REP_COUNT_DOWN'				=>	'Thumbs-down per user per event',
	'ACP_DTST_REP_COUNT_DOWN_EXPLAIN'		=>	'Range 0 to unlimited negatives.',
	'ACP_DTST_REP_COUNT_GOOD'				=>	'Good conducts per host per event',
	'ACP_DTST_REP_COUNT_GOOD_EXPLAIN'		=>	'Range 1 to unlimited.',
	'ACP_DTST_REP_COUNT_BAD'				=>	'Bad conducts per host per event',
	'ACP_DTST_REP_COUNT_BAD_EXPLAIN'		=>	'Range 0 to unlimited negatives.',
	//'ACP_DTST_REP_POINTS_MIN'				=>	'Lowest reputation possible',
	//'ACP_DTST_REP_POINTS_MIN_EXPLAIN'		=>	'<em>(Example: -100)</em> - Range 0 to unlimited negatives.',
	//'ACP_DTST_REP_POINTS_MAX'				=>	'Highest reputation possible',
	//'ACP_DTST_REP_POINTS_MAX_EXPLAIN'		=>	'<em>(Example: +100)</em> - Range 1 to unlimited.',
	'ACP_DTST_SHOW_REP_POINTS'				=>	'Display reputation Points',
	'ACP_DTST_SHOW_REP_POINTS_EXPLAIN'		=>	'When set to yes the reputation Points will be shown where is allowed.',
	'ACP_DTST_SHOW_REP_RANK'				=>	'Display reputation Rank',
	'ACP_DTST_SHOW_REP_RANK_EXPLAIN'		=>	'When set to yes the custom Rank will be shown where is allowed.',
	'ACP_DTST_SHOW_MOD_ANON'				=>	'Moderator anonimity',
	'ACP_DTST_SHOW_MOD_ANON_EXPLAIN'		=>	'When set to yes the language string “<strong>Moderator</strong>” will be used instead of the moderator’s username',
	//'ACP_DTST_SHOW_REASON_ANON'				=>	'Author of reasons anonimity',
	//'ACP_DTST_SHOW_REASON_ANON_EXPLAIN'		=>	'When set to yes the language string “<strong>Attendee</strong>” will be used instead of the Author’s username',
	'ACP_DTST_USERS_PAGE'					=>	'Reputation page’s pagination',
	'ACP_DTST_USERS_PAGE_EXPLAIN'			=>	'Users to show per page, range 1 to 300. <strong>0</strong> to disable and use “<strong>topics per page</strong>” instead.',

	// Reputation Values explain
	'ACP_LPR_REPUTATION_MANAGEMENT'			=>	'Can assign point values for various events/actions. Note: all values are unlimited, both neg./ pos.',
	// Reputation Values legends
	'ACP_DTST_REP_USER'						=>	'Attendee',
	'ACP_DTST_REP_ALL'						=>	'Host and Attendee',
	'ACP_DTST_REP_HOST'						=>	'Host',
	// Reputation Values
	'ACP_DTST_REP_POINTS_GOOD'				=>	'For good conduct in an event',
	'ACP_DTST_REP_POINTS_GOOD_EXPLAIN'		=>	'Given out by the Host. 0 for no points.',
	'ACP_DTST_REP_POINTS_ATTEND'			=>	'For attending an event',
	'ACP_DTST_REP_POINTS_ATTEND_EXPLAIN'	=>	'0 for no points.',
	'ACP_DTST_REP_POINTS_WITHDRAW'			=>	'For withdrawing from an event',
	'ACP_DTST_REP_POINTS_WITHDRAW_EXPLAIN'	=>	'0 for no points.',
	'ACP_DTST_REP_POINTS_BAD'				=>	'For bad conduct in an event',
	'ACP_DTST_REP_POINTS_BAD_EXPLAIN'		=>	'Given out by the Host. 0 for no points.',
	'ACP_DTST_REP_POINTS_NOSHOW'			=>	'For no show up for an event',
	'ACP_DTST_REP_POINTS_NOSHOW_EXPLAIN'	=>	'Given out by the Host. 0 for no points.',
	'ACP_DTST_REP_POINTS_HOST'				=>	'For hosting an event',
	'ACP_DTST_REP_POINTS_HOST_EXPLAIN'		=>	'0 for no points.',
	'ACP_DTST_REP_POINTS_UP'				=>	'For receiving a thumbs-up',
	'ACP_DTST_REP_POINTS_UP_EXPLAIN'		=>	'Given out by an attendee. 0 for no points.',
	'ACP_DTST_REP_POINTS_DOWN'				=>	'For receiving a thumbs-down',
	'ACP_DTST_REP_POINTS_DOWN_EXPLAIN'		=>	'Given out by an attendee. 0 for no points.',
	'ACP_DTST_REP_POINTS_NOREPLY'			=>	'For not responding to an application',
	'ACP_DTST_REP_POINTS_NOREPLY_EXPLAIN'	=>	'Time limit settings are in “<strong>Reputation Settings</strong>”. 0 for no points.',
	'ACP_DTST_REP_POINTS_CANCEL_EVENT'		=>	'For cancelling an event',
	'ACP_DTST_REP_POINTS_CANCEL_EVENT_EXP'	=>	'0 for no points.',

	// LPR Ranks explain
	'ACP_DTST_RANKS_MANAGEMENT'				=>	'Reputation Ranks settings',
	'ACP_DTST_RANKS_MANAGEMENT_EXPLAIN'		=>	'Changing any of the settings below will disregard any changes made to the current rank.',
	'ACP_DTST_RANK_SETTINGS'				=>	'Rank settings',
	'ACP_DTST_RANK_SETTINGS_EXPLAIN'		=>	'Colorpickers on the right side are showing already stored values.',
	'ACP_DTST_RANK_TITLE_HOLDER'			=>	'No rank title yet…',
	'ACP_DTST_RANK_DESC_HOLDER'				=>	'There is no rank description yet for this language and status combination. Feel free to create one…',
	'ACP_DTST_RANK_BCKG_HOLDER'				=>	'No bckg color yet…',
	'ACP_DTST_RANK_TEXT_HOLDER'				=>	'No text color yet…',
	// Ranks Drop downs
	'ACP_DTST_RANK_LANG'					=>	'Select the language for the rank to edit',

	'ACP_DTST_RANK_VALUE'					=>	'Select the value for the rank to edit',
	'ACP_DTST_RANK_ZERO'					=>	'0',
	'ACP_DTST_RANK_MIN'						=>	'1',
	'ACP_DTST_RANK_ONE'						=>	'10',
	'ACP_DTST_RANK_TWO'						=>	'20',
	'ACP_DTST_RANK_THREE'					=>	'30',
	'ACP_DTST_RANK_FOUR'					=>	'40',
	'ACP_DTST_RANK_FIVE'					=>	'50',
	'ACP_DTST_RANK_SIX'						=>	'60',
	'ACP_DTST_RANK_SEVEN'					=>	'70',
	'ACP_DTST_RANK_EIGHT'					=>	'80',
	'ACP_DTST_RANK_NINE'					=>	'90',
	'ACP_DTST_RANK_TEN'						=>	'100',

	'ACP_DTST_RANK_TITLE'					=>	'Title',
	'ACP_DTST_RANK_TITLE_EXPLAIN'			=>	'That’s the rank',
	'ACP_DTST_RANK_DESC'					=>	'Description',
	'ACP_DTST_RANK_DESC_EXPLAIN'			=>	'That’s the text and the title on-hover.',
	'ACP_DTST_RANK_BCKG'					=>	'Background',
	'ACP_DTST_RANK_BCKG_EXPLAIN'			=>	'That’s the color.',
	'ACP_DTST_RANK_TEXT'					=>	'Text',
	'ACP_DTST_RANK_TEXT_EXPLAIN'			=>	'That’s the color.',

	'ACP_DTST_RANK_COLORPICKER_EXPLAIN'		=>	'Input a color in #HexDec value or use the color-picker.',
	'ACP_DTST_RANK_COLOR_STORED'			=>	'Color #HexDec value and actual color stored in the DB.',
	'ACP_DTST_RANK_HEX_STORED'				=>	'Now',
));
