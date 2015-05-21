<?php

/**
 * Photo_Organizer add/edit source view.
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

if($invalid_source)
    echo infobox_warning(lang('base_warning'), lang('photo_organizer_source_required'));

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('photo_organizer/sources/add_edit' . (isset($source_id) ? "/$source_id" : ""));
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

if ($mode == 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('edit_source'),
        anchor_cancel('/app/photo_organizer')
    );
} else {
    $read_only = FALSE;
    $buttons = array(
        form_submit_add('add_source'),
        anchor_cancel('/app/photo_organizer')
    );
}

echo field_input('source', $source, lang('photo_organizer_source'), TRUE);
echo field_checkbox('move', $move, lang('photo_organizer_delete_original'), $read_only);
echo field_checkbox('recurse', $recurse, lang('photo_organizer_recursive'), $read_only);
echo field_input('source_id', $source_id, '', TRUE, array('hide_field' => TRUE));
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();
