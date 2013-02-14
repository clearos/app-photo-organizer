<?php

/**
 * Photo organizer settings controller.
 *
 * @category   Apps
 * @package    Photo_Organizer
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Settings controller.
 *
 * @category   Apps
 * @package    Photo_Organizer
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
 */

class Settings extends ClearOS_Controller
{
    /**
     * Photo organizer default controller
     *
     * @return view
     */

    function index()
    {
        $this->_view_edit('view');
    }

    /**
     * Photo organizer edit controller
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * Common view/edit form
     *
     * @param string $mode form mode
     *
     * @return view
     */

    function _view_edit($mode)
    {
        // Load dependencies
        //------------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        $data['mode'] = $mode;

        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('run_option', 'photo_organizer/Photo_Organizer', 'validate_run_option', TRUE);
        $this->form_validation->set_policy('destination_folder', 'photo_organizer/Photo_Organizer', 'validate_destination_folder', TRUE);
        $this->form_validation->set_policy('email_notification', 'photo_organizer/Photo_Organizer', 'validate_email_notification', FALSE);

        // Handle form submit
        //-------------------

        $form_ok = $this->form_validation->run();

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->photo_organizer->set_run_option($this->input->post('run_option'));
                $this->photo_organizer->set_destination_folder($this->input->post('destination_folder'));
                $this->photo_organizer->set_folder_format($this->input->post('folder_format'));
                $this->photo_organizer->set_file_format($this->input->post('file_format'));
                $this->photo_organizer->set_email_notification($this->input->post('email_notification'));
                $this->page->set_message(lang('photo_organizer_settings_updated'), 'info');
                redirect('/photo_organizer');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }


        $data['run_options'] = $this->photo_organizer->get_run_options();
        $data['destination_folder_options'] = $this->photo_organizer->get_destination_folder_options();
        $data['folder_format_options'] = $this->photo_organizer->get_folder_format_options();
        $data['file_format_options'] = $this->photo_organizer->get_file_format_options();

        // Load views
        //-----------
        $data['run_option'] = $this->photo_organizer->get_run_option();
        $data['destination_folder'] = $this->photo_organizer->get_destination_folder();
        $data['folder_format'] = ($this->photo_organizer->get_folder_format() != NULL ? $this->photo_organizer->get_folder_format() : 0);
        $data['file_format'] = ($this->photo_organizer->get_file_format() != NULL ? $this->photo_organizer->get_file_format() : 0);
        $data['email_notification'] = $this->photo_organizer->get_email_notification();

        $this->page->view_form('settings', $data, lang('photo_organizer_photo_organizer'));
    }
}
