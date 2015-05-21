<?php

/**
 * Photo organizer sources controller.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
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
 * Sources controller.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
 */

class Sources extends ClearOS_Controller
{
    /**
     * Source default controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        $data['sources'] = $this->photo_organizer->get_sources();

        // Load views
        //-----------

        $this->page->view_form('sources', $data, lang('photo_organizer_app_name'));
    }

    /**
     * Add/edit source controller.
     *
     * @return view
     */

    function add_edit($index = -1)
    {
        // Load dependencies
        //------------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        // Load views
        //-----------
        $data = array();
        if ($this->input->post('select_source')) {
            if ($this->input->post('source_path') != '/')
                $data['source'] = $this->input->post('source_path');
        } else if ($this->input->post('add_source') || $this->input->post('edit_source')) {

            // Validate data
            $this->form_validation->set_policy('source', 'photo_organizer/Photo_Organizer', 'validate_source', TRUE);
            $this->form_validation->set_policy('move', 'photo_organizer/Photo_Organizer', 'validate_move', FALSE);
            $this->form_validation->set_policy('recurse', 'photo_organizer/Photo_Organizer', 'validate_recurse', FALSE);
            $form_ok = $this->form_validation->run();

            if ($form_ok) {
                try {
                    $this->photo_organizer->add_edit_source(
                        $index,
                        $this->input->post('source'),
                        $this->input->post('move'),
                        $this->input->post('recurse')
                    );
                    redirect('/photo_organizer');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                    return;
                }
            } else {
                $data['invalid_source'] = TRUE;
            }
        }

        if ($index >= 0) {
            $sources = $this->photo_organizer->get_sources();
            $data['mode'] = 'edit';
            if (!isset($data['source']))
                $data['source'] = $sources[$index]['path'];
            $data['recurse'] = $sources[$index]['recurse'];
            $data['move'] = $sources[$index]['move'];
            $data['source_id'] = $index;
        }
        $this->page->view_form('add_edit_source', $data, lang('photo_organizer_app_name'));
    }

    /**
     * Delete source controller.
     *
     * @param int $id Source ID
     *
     * @return view
     */

    function delete($id)
    {
        // Load dependencies
        //------------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        $this->photo_organizer->delete_source($id);

        $this->page->set_message(lang('photo_organizer_source_removed'), 'info');
        redirect('/photo_organizer');
    }
}
