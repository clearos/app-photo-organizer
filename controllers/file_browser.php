<?php

/**
 * Photo Organizer file source controller.
 *
 * @category   Apps
 * @package    Photo_Organizer
 * @subpackage Controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/photo_organizer/
 */

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\photo_organizer\Photo_Organizer as Photo_Organizer;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////


/**
 * Photo Organizer file source controller.
 *
 * @category   Apps
 * @package    Photo_Organizer
 * @subpackage Controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/photo_organizer/
 */

class File_Browser extends ClearOS_Controller
{
    /**
     * Photo Organizer file browser controller.
     *
     * @return view
     */

    function index($action)
    {
        // Load libraries
        //---------------

        $this->load->library('base/File_System_Browser');
        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        if ($action == 'save') {
            $this->photo_organizer->set_custom_folder_selections(
                $this->file_system_browser->get_selections($mode),
                ($mode == 'rbs_custom_backup_select' ? RBS_MODE_BACKUP : RBS_MODE_RESTORE)
            );
            $this->file_system_browser->reset();
            if ($mode == 'rbs_custom_backup_select') {
                $this->page->set_message(lang('photo_organizer_backup_custom_directories_saved'), 'info');
                redirect('/photo_organizer/advanced');
            } else if ($mode == 'rbs_custom_restore_select') {
                $this->page->set_message(lang('photo_organizer_restore_custom_directories_saved'), 'info');
                redirect('/photo_organizer/restore');
            }
        } else if ($action == 'init') {
            if ($mode == 'rbs_custom_backup_select')
                $this->file_system_browser->init(Photo_Organizer::FILE_CUSTOM_BACKUP_SELECTION, $mode);
            else if ($mode == 'rbs_custom_restore_select')
                $this->file_system_browser->init(Photo_Organizer::FILE_CUSTOM_RESTORE_SELECTION, $mode);
        }

        $this->_file_browser($mode, $include_files);

    }

    function _file_browser($mode, $include_files)
    {
        // Load libraries
        //---------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        $data['rbs_path'] = '/';
        // If restore mode, grab snapshot
        if ($mode == 'rbs_custom_restore_select')
            $data['rbs_path'] = RBS_VOLUME_MOUNT_POINT . '/' . $this->photo_organizer->get_restore_image();
        $data['rbs_mode'] = $mode;
        $data['rbs_include_files'] = $include_files;
        $this->page->view_form('photo_organizer/file_browser', $data, lang('photo_organizer_app_name'));
    }
}
