<?php

/**
 * Photo_Organizer settings view.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearcenter.com/support/documentation/clearos/photo_organizer/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('photo_organizer');

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('photo_organizer/settings');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($mode === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/photo_organizer')
    );
} else {
    $read_only = TRUE;
    $buttons = array(anchor_edit('/app/photo_organizer/settings/edit'));
}

echo field_dropdown('run_option', $run_options, $run_option, lang('photo_organizer_run_option'), $read_only);
echo field_dropdown('destination_folder', $destination_folder_options, $destination_folder, lang('photo_organizer_destination_folder'), $read_only);
echo field_dropdown('folder_format', $folder_format_options, $folder_format, lang('photo_organizer_folder_format'), $read_only);
echo field_dropdown('file_format', $file_format_options, $file_format, lang('photo_organizer_file_format'), $read_only);
echo field_input('email_notification', $email_notification, lang('photo_organizer_email'), $read_only);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
