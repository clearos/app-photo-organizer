<?php

/**
 * Photo Orginizer file browser view.
 *
 * @category   Apps
 * @package    Photo_Organizer
 * @subpackage Views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/photo_organizer/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////


$this->lang->load('base');
$this->lang->load('photo_organizer');

///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$anchors = array(
    form_submit_custom('select_source', lang('base_select')),
    anchor_cancel('/app/photo_organizer/sources/add_edit')
);

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    '&nbsp;',
    lang('photo_organizer_folder')
);

///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

$table_title = lang('photo_organizer_source_dir');

echo form_open('photo_organizer/sources/add_edit');
echo summary_table(
    $table_title,
    $anchors,
    $headers,
    NULL,
    array('id' => 'file_browser', 'no_action' => TRUE, 'sort' => array(0, 1), 'sort-default-col' => 1, 'sort-default-dir' => 'desc', 'col-widths' => array('5%', '95%'))
);
echo "<input type='hidden' name='source_path' id='source_path' value='$source_path' />";
echo form_close();
