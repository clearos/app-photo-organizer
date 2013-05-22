<?php

/**
 * Photo Organizer controller.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
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
 * Photo_Organizer controller.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
 */

class Photo_Organizer extends ClearOS_Controller
{

    /**
     * Photo_Organizer default controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        // Load views
        //-----------

        $views = array('photo_organizer/settings', 'photo_organizer/sources');

        if (clearos_app_installed('user_photo_organizer'))
            $views[] = 'photo_organizer/policy';

        $this->page->view_forms($views, lang('photo_organizer_app_name'));
    }

    /**
     * Ajax get folder contents controller
     *
     * @return JSON 
     */

    function dir_listing()
    {
        clearos_profile(__METHOD__, __LINE__);
        
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Fri, 01 Jan 2010 05:00:00 GMT');
        header('Content-type: application/json');
        try {
            $this->load->library('photo_organizer/Photo_Organizer');
            $this->load->library('base/File_System_Browser');

            // Default path is root
            $path = '/';

            if ($this->input->post('path'))
                $path = base64_decode($this->input->post('path'));

            $valid_root_folders = $this->photo_organizer->get_valid_root_folders();

            $contents = $this->file_system_browser->get_listing($path, FALSE, NULL);
            // Hide directories as per config file
            if ($path == '/') {
                $index = 0;
                foreach ($contents as $folder) {
                    if (!in_array($folder['name'], $valid_root_folders))
                        unset($contents[$index]);
                    $index++;
                }
            }
            // Re-index
            $contents = array_values($contents);
            $data = array(
                'path' => base64_encode($path),
                'previous' => base64_encode(dirname($path)),
                'contents' => $contents,
                'code' => 0
            );
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }
}
