<?php

/**
 * Photo Organizer file source controller.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
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
 * @category   apps
 * @package    photo-organizer
 * @subpackage controllers
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
     * @param int $source_id source ID
     *
     * @return view
     */

    function index($source_id = NULL)
    {
        // Load libraries
        //---------------

        $this->load->library('photo_organizer/Photo_Organizer');
        $this->lang->load('photo_organizer');

        if ($source_id == NULL) {
            $path = "/";
        } else {
            $sources = $this->photo_organizer->get_sources();
            $path = $sources[$source_id]['path'];
        }

        $data = array('source_path' => $path);
        if ($source_id != NULL)
            $data['source_id'] = $source_id;

        $this->page->view_form('photo_organizer/file_browser', $data, lang('photo_organizer_app_name'));
    }
}
