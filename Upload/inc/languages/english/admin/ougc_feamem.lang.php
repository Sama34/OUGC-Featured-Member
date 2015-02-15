<?php

/***************************************************************************
 *
 *   OUGC Featured Member plugin (/inc/languages/english/admin/ougc_feamem.lang.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Shows a member information anywhere in the forum.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Plugin API
$l['setting_group_ougc_feamem'] = 'OUGC Featured Member';
$l['setting_group_ougc_feamem_desc'] = 'Shows a member information anywhere in the forum.';

// Settings
$l['setting_ougc_feamem_tip'] = ' <b title="\'username:Sama34\' (without \' quote) will result on the search by username.">Tip</b>';
$l['setting_ougc_feamem_time'] = 'Refesh Time';
$l['setting_ougc_feamem_time_desc'] = 'Time interval (hours) to change the featured member. Default 24.';
$l['setting_ougc_feamem_groups'] = 'Allowed Groups';
$l['setting_ougc_feamem_groups_desc'] = 'Users have to be part of the following groups in order to be featured.';
$l['setting_ougc_feamem_uid'] = 'Static User';
$l['setting_ougc_feamem_uid_desc'] = 'Enter the UID of the user that should be shown as static user. This will ignore above settings.'.$l['setting_ougc_feamem_tip'];
$l['setting_ougc_feamem_away'] = 'Allow Away Users';
$l['setting_ougc_feamem_away_desc'] = 'Whether to allow away users to be featured.';
$l['setting_ougc_feamem_maxavatardim'] = 'Maximum Avatar Dimensions';
$l['setting_ougc_feamem_maxavatardim_desc'] = 'Maximum Avatar Dimensions';
$l['setting_ougc_feamem_ignorefeatured'] = 'Ignore Already Fetured';
$l['setting_ougc_feamem_ignorefeatured_desc'] = 'Do you want to ignore already featured members when selecting a new one? <b style="color: red;">Beta Status</b>';
$l['setting_ougc_feamem_createthread'] = 'Add Forum Thread';
$l['setting_ougc_feamem_createthread_desc'] = 'Add a thread each time a member is selected as the featured member.';
$l['setting_ougc_feamem_thread_fid'] = 'Thread: Forum';
$l['setting_ougc_feamem_thread_fid_desc'] = 'Select the forum where this thread should be created in.';
$l['setting_ougc_feamem_thread_subject'] = 'Thread: Subject';
$l['setting_ougc_feamem_thread_subject_desc'] = 'Insert the subject for the created thread.<br />You can use:<br />
&nbsp;{DATE} -> Date of the thread.<br />
&nbsp;{USERNAME} -> Username of the user.';
$l['setting_ougc_feamem_thread_message'] = 'Thread: Message';
$l['setting_ougc_feamem_thread_message_desc'] = 'Insert the message for the created thread.<br />You can use:<br />
&nbsp;{DATE} -> Date of the thread.<br />
&nbsp;{USERNAME} -> Username of the user.';
$l['setting_ougc_feamem_thread_prefix'] = 'Thread: Prefix';
$l['setting_ougc_feamem_thread_prefix_desc'] = 'Select the prefix the thread should have.';
$l['setting_ougc_feamem_thread_prefix_empty'] = '<b style="color: red;">There are no thread prefixes to be selected.</b>';
$l['setting_ougc_feamem_thread_icon'] = 'Thread: Icon';
$l['setting_ougc_feamem_thread_icon_desc'] = 'Select the post icon for the created thread.';
$l['setting_ougc_feamem_thread_uid'] = 'Thread: User ID';
$l['setting_ougc_feamem_thread_uid_desc'] = 'Insert User ID that should be the author of the created thread. Leave empty to use the featured member. Use -1 for guests. IF USER DOESN\'T EXISTS AND VALUE IS NOT -1, THE THREAD WILL NOT BE CREATED.'.$l['setting_ougc_feamem_tip'];
$l['setting_ougc_feamem_thread_closed'] = 'Thread: Close thread.';
$l['setting_ougc_feamem_thread_closed_desc'] = 'Does the thread have to be closed?';
$l['setting_ougc_feamem_thread_sticky'] = 'Thread: Stick the thread.';
$l['setting_ougc_feamem_thread_sticky_desc'] = 'Does the thread have to be closed?';

// PluginLibrary
$l['ougc_feamem_plreq'] = 'This plugin requires <a href="http://mods.mybb.com/view/pluginlibrary">PluginLibrary</a> version {1} or later to be uploaded to your forum.';
$l['ougc_feamem_plold'] = 'This plugin requires PluginLibrary version {1} or later, whereas your current version is {2}. Please do update <a href="{3}">PluginLibrary</a>.';