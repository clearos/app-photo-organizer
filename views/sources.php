<?php

/**
 * Photo_Organizer sources view.
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
// Summary table
///////////////////////////////////////////////////////////////////////////////

$items = array();

$action = array(anchor_add('/app/photo_organizer/sources/add_edit'));

foreach ($sources as $info) {
    $item = array(
        'title' => $info['path'],
        'actions' => NULL,
        'anchors' => button_set(
            array(  
                anchor_edit('/app/photo_organizer/sources/add_edit/' . $info['id']),
                anchor_delete('/app/photo_organizer/sources/delete/' . $info['id'])
            )
        ),
        'details' => array(
            $info['path'],
            ($info['move'] ? lang('base_yes') : lang('base_no')),
            ($info['recurse'] ? lang('base_yes') : lang('base_no'))
        )
    );

    $items[] = $item;
}
echo summary_table(
    lang('photo_organizer_sources'),
    $action,
    array(lang('photo_organizer_source'), lang('photo_organizer_delete_original'), lang('photo_organizer_recursive')),
    $items
);
