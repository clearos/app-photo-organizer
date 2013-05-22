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
     * @return view
     */

    function index($action)
    {
        // Load libraries
        //---------------

        $this->lang->load('photo_organizer');

        $data = array('source_path' => '/');

        $this->page->view_form('photo_organizer/file_browser', $data, lang('photo_organizer_app_name'));
    }
}
