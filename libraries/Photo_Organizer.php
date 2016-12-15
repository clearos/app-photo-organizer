<?php

/**
 * Photo Organizer class.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\photo_organizer;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('photo_organizer');
clearos_load_language('flexshare');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\flexshare\Flexshare as Flexshare;
use \clearos\apps\mail_notification\Mail_Notification as Mail_Notification;
use \clearos\apps\network\Hostname as Hostname;
use \clearos\apps\tasks\Cron as Cron;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('flexshare/Flexshare');
clearos_load_library('mail_notification/Mail_Notification');
clearos_load_library('network/Hostname');
clearos_load_library('tasks/Cron');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\flexshare\Flexshare_Not_Found_Exception as Flexshare_Not_Found_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');
clearos_load_library('flexshare/Flexshare_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Photo_Organizer class.
 *
 * @category   apps
 * @package    photo-organizer
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/photo_organizer/
 */

class Photo_Organizer extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/clearos/photo_organizer.conf';
    const COMMAND_EXIFTOOL = '/usr/bin/exiftool';
    const FOLDER_FLEXSHARE_PHOTOS = '/var/flexshare/shares/photos';
    const FLEXSHARE_PHOTOS = 'photos';
    const FOLDER_PHOTOS_ORGANIZER = '/var/clearos/photo_organizer';
    const CRON_DAILY = '00 03 * * *  root /usr/sbin/photo-organizer >/dev/null 2>&1';
    const CRON_HOURLY = '00 * * * *  root /usr/sbin/photo-organizer >/dev/null 2>&1';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $config = NULL;
    protected $is_loaded = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Photo_Organizer constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Get destination folder options.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_destination_folder_options()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        $options = array (
            self::FOLDER_FLEXSHARE_PHOTOS => lang('flexshare_app_name') . ' - ' . lang('photo_organizer_photos')
        );
        $flexshare = new Flexshare();
        $shares = $flexshare->get_share_summary(FALSE);
        foreach ($shares as $share) {
            if ($share['ShareDir'] == self::FOLDER_FLEXSHARE_PHOTOS)
                continue;
            $options[$share['ShareDir']] = lang('flexshare_app_name') . ' - ' . $share['Name'];
        }
        
        // Read config file
        $destination = $this->get_destination_folder();
        if (!array_key_exists($destination, $options) && $destination != self::FOLDER_FLEXSHARE_PHOTOS)
            $options[$destination] = lang('photo_organizer_custom') . ' - ' . $destination;
        
        return $options;
    }

    /**
     * Get folder format options.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_folder_format_options()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        $options = array (
            '0' => lang('photo_organizer_no_folder_format'),
            '%y-%m' => lang('photo_organizer_folder_format_yy_mm'),
            '%Y-%m' => lang('photo_organizer_folder_format_yyyy_mm'),
            '%y-%B' => lang('photo_organizer_folder_format_yy_mmm'),
            '%Y-%B' => lang('photo_organizer_folder_format_yyyy_mmm')
        );

        // Read config file
        $format = $this->get_folder_format();
        if ($format != NULL && !array_key_exists($format, $options))
            $options[$format] = lang('photo_organizer_custom') . ' - ' . $format;
        
        return $options;
    }

    /**
     * Get file format options.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_file_format_options()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        $options = array (
            '0' => lang('photo_organizer_no_photo_renaming'),
            '$make-%f.%e' => lang('photo_organizer_filename_format_make'),
            '$model-%f.%e' => lang('photo_organizer_filename_format_model'),
            '$make-$model-%f.%e' => lang('photo_organizer_filename_format_make_model')
        );
        // Read config file
        $format = $this->get_file_format();
        if ($format != NULL && !array_key_exists($format, $options))
            $options[$format] = lang('photo_organizer_custom') . ' - ' . $format;
        return $options;
    }

    /**
     * Get valid root folder options.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_valid_root_folders()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['valid_root_folders']))
            return preg_split('/;/', $this->config['valid_root_folders'], 0, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get run options.
     *
     * @return array
     * @throws Engine_Exception
     */

    public function get_run_options()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        $options = array (
            0 => lang('photo_organizer_never'),
            1 => lang('base_hourly'),
            2 => lang('base_daily')
        );
        return $options;
    }

    /**
     * Get file format.
     *
     * @return String
     */

    function get_file_format()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['file_format']) && $this->config['file_format'] !== '0')
            return $this->config['file_format'];

        return NULL;
    }

    /**
     * Get folder format.
     *
     * @return String
     */

    function get_folder_format()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['folder_format']) && $this->config['folder_format'] !== '0')
            return $this->config['folder_format'];

        return NULL;
    }

    /**
     * Get source list.
     *
     * @return array
     */

    function get_sources()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();

        if (!$this->is_loaded)
            $this->_load_config();

        if (!isset($this->config['sources']) || empty($this->config['sources']))
            return $list;

        $sources = explode(';', $this->config['sources']);

        $index = 0;
        foreach ($sources as $source) {
            list($path, $move, $recurse) = explode('|', $source);
            $list[] = array(   
                'id' => $index,
                'path' => $path,
                'move' => $move,
                'recurse' => $recurse
            );
            $index++;
        }
        return $list;
    }

    /**
     * Get destination folder.
     *
     * @return String
     */

    function get_destination_folder()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['destination_folder']) && $this->config['destination_folder'] != '')
            return $this->config['destination_folder'];

        return self::FOLDER_FLEXSHARE_PHOTOS;
    }

    /**
     * Get email notification.
     *
     * @return String
     */

    function get_email_notification()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        if (isset($this->config['email_notification']))
            return $this->config['email_notification'];

        return NULL;
    }

    /**
     * Set destination folder.
     *
     * @param String $destination_folder destination folder
     *
     * @return void
     */

    function set_destination_folder($destination_folder)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_destination_folder($destination_folder));

        $this->_set_parameter('destination_folder', $destination_folder);
    }

    /**
     * Set folder format.
     *
     * @param String $folder_format folder format
     *
     * @return void
     */

    function set_folder_format($folder_format)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_folder_format($folder_format));

        $this->_set_parameter('folder_format', $folder_format);
    }

    /**
     * Set file format.
     *
     * @param String $file_format file format
     *
     * @return void
     */

    function set_file_format($file_format)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validation
        // ----------

        Validation_Exception::is_valid($this->validate_file_format($file_format));

        $this->_set_parameter('file_format', $file_format);
    }

    /**
     * Set email notification.
     *
     * @param String $email email address
     *
     * @return void
     */

    function set_email_notification($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validation
        // ----------

        if (isset($email) && $email != '')
            Validation_Exception::is_valid($this->validate_email_notification($email));

        $this->_set_parameter('email_notification', $email);
    }

    /**
     * Add or edit existing source.
     *
     * @param int     $source_id  source ID
     * @param string  $my_path    path
     * @param boolean $my_move    move files
     * @param boolean $my_recurse recurse through folder
     *
     * @return array
     */

    function add_edit_source($source_id, $my_path, $my_move = 1, $my_recurse = 1)
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();

        if (!$this->is_loaded)
            $this->_load_config();

        $sources = preg_split('/;/', $this->config['sources'], 0, PREG_SPLIT_NO_EMPTY);

        foreach ($sources as $source) {
            list($path, $move, $recurse) = explode('|', $source);
            $list[] = $path . '|' . $move . '|' . $recurse;
        }
        if ($source_id < 0)
            $list[] = $my_path . '|' . $my_move . '|' . $my_recurse;
        else
            $list[$source_id] = $my_path . '|' . $my_move . '|' . $my_recurse;

        $this->_set_parameter('sources', implode(';', $list));
    }

    /**
     * Delete a source.
     *
     * @param int $id ID
     *
     * @return array
     */

    function delete_source($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();

        if (!$this->is_loaded)
            $this->_load_config();

        if (!isset($this->config['sources']))
            return;

        $sources = explode(';', $this->config['sources']);

        $index = 0;
        foreach ($sources as $source) {
            if ($index == $id)
                continue;
            $list[] = $source;
            $index++;
        }
        $this->_set_parameter('sources', implode(';', $list));
    }

    /**
     * Get run option.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_run_option()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        try {
            $cron = new Cron();

            $app = 'app-photo-organizer';

            if (!$cron->exists_configlet($app)) {
                return 0;
            } else {
                $schedule = $cron->get_configlet($app);
                if ($schedule == self::CRON_HOURLY)
                    return 1;
                else
                    return 2;
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set run option.
     *
     * @param int $status status
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_run_option($status)
    {
        clearos_profile(__METHOD__, __LINE__);
            
        try {
            $cron = new Cron();

            $app = 'app-photo-organizer';

            if ($cron->exists_configlet($app) && $status == 0) {
                $cron->delete_configlet($app);
            } else {
                if (!$cron->exists_configlet($app)) {
                    $cron->add_configlet($app, ($status == 1 ? self::CRON_HOURLY : self::CRON_DAILY));
                } else {
                    $schedule = $cron->get_configlet($app);
                    if ($schedule != self::CRON_HOURLY && $status == 1) {
                        $cron->delete_configlet($app);
                        $cron->add_configlet($app, self::CRON_HOURLY);
                    } else if ($schedule != self::CRON_DAILY && $status == 2) {
                        $cron->delete_configlet($app);
                        $cron->add_configlet($app, self::CRON_DAILY);
                    }
                }
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Sends a status change notification to admin.
     *
     * @param string $lines the message content
     *
     * @return void
     * @throws Engine_Exception
     */

    function send_report_notification($lines)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $email = $this->get_email_notification();

            if (!isset($email) || $email == NULL)
                return;

            $mailer = new Mail_Notification();
            $hostname = new Hostname();
            $subject = lang('photo_organizer_report') . ' - ' . $hostname->get();
            $body = "\n" . lang('photo_organizer_report') . "\n";
            $body .= str_pad('', strlen(lang('photo_organizer_report')), '=') . "\n\n";

            foreach ($lines as $line)
                $body .= $line . "\n";
            $mailer->add_recipient($this->get_email_notification());
            $mailer->set_message_subject($subject);
            $mailer->set_message_body($body);
            $mailer->send();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set run option.
     *
     * @string $source_override source override
     *
     * @return void
     * @throws Engine_Exception
     */

    function run($source_override = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);
            
        $shell = new Shell();
        $sources = $this->get_sources();
        if ($source_override == NULL && empty($sources))
            throw new Engine_Exception(lang('photo_organizer_no_sources_defined'));

        if ($source_override != NULL) {
            unset($sources);
            $sources[] = $source_override;
        }

        try {
            
            $destination_folder = $this->get_destination_folder();
            $file_format = $this->get_file_format();
            $folder_format = $this->get_folder_format();
            $params = '';
            $log = array();;

            // Check for default destination folder for Flexshare
            if ($destination_folder == self::FOLDER_FLEXSHARE_PHOTOS) {
                $flexshare = new Flexshare();

                try {
                    $share = $flexshare->get_share(self::FLEXSHARE_PHOTOS);
                } catch (Flexshare_Not_Found_Exception $e) {
                    $flexshare->add_share(
                        self::FLEXSHARE_PHOTOS,
                        lang('photo_organizer_app_name'),
                        'allusers',
                        self::FOLDER_FLEXSHARE_PHOTOS
                    );
                }
            }
            foreach ($sources as $source) {
                $params = '-q ';
                    
                // Recurse folders
                if ($source['recurse'])
                    $params .= '-r ';
                if ($file_format != NULL)
                    $params .= "'-filename<" . $file_format . "' ";

                // Move or copy
                if (!$source['move'])
                    $params .= ' -o . ';

                $params .= "\"-directory<datetimeoriginal\" ";
                
                // Add destination folder
                if ($folder_format != NULL)
                    $params .= "-d \"$destination_folder/$folder_format\" ";
                else
                    $params .= "-d \"$destination_folder/\" ";
                $params .= "\"" . $source['path'] . "\"";

                //echo self::COMMAND_EXIFTOOL . ' ' . $params;

                $options = array('validate_exit_code' => FALSE);
                $shell->execute(self::COMMAND_EXIFTOOL, $params, FALSE, $options);
                $rows = $shell->get_output();
                $log[] = lang('photo_organizer_source') . ":  " . $source['path'];
                $log[] = "";
                foreach ($rows as $row)
                    $log[] = $row;
                $log[] = "";
                $log[] = "~~~~~~~~###~~~~~~~~";
                $log[] = "";
            }
            
            // Chown new folders/files
            $userinfo = posix_getpwuid(fileowner($destination_folder));
            $groupinfo = posix_getgrgid(filegroup($destination_folder));
            $folder = new Folder($destination_folder, TRUE);
            $folder->chown($userinfo['name'], $groupinfo['name'], TRUE);

            // Write log to file and compare with previous to see if email alert required
            $file = new File(self::FOLDER_PHOTOS_ORGANIZER . '/log.txt');
            if (!$file->exists()) {
                $file->create('webconfig', 'webconfig', '644');
                $file->add_lines(implode("\n", $log));
                $this->send_report_notification($log);
                return;
            } 
            
            // Compare log to see if we need to send alert
            $previous = $file->get_contents_as_array();
            $diff = array_diff($log, $previous);
            if (empty($diff))
                return;

            $file->delete();
            $file->create('webconfig', 'webconfig', '644');
            $file->add_lines(implode("\n", $log));
            $this->send_report_notification($log);
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for email
     *
     * @param string $email email
     *
     * @return boolean TRUE if email is valid
     */

    public function validate_email_notification($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        $notify = new Mail_Notification();

        try {
            Validation_Exception::is_valid($notify->validate_email($email));
        } catch (Validation_Exception $e) {
            return lang('photo_organizer_email_is_invalid');
        }
    }

    /**
     * Validation routine for destination folder
     *
     * @param string $destination_folder destination folder
     *
     * @return boolean TRUE if destination folder is valid
     */

    public function validate_destination_folder($destination_folder)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($destination_folder == NULL || !isset($destination_folder))
            return lang('photo_organizer_destination_folder_is_invalid');
    }

    /**
     * Validation routine for run option.
     *
     * @param boolean $option run option
     *
     * @return mixed void if option is valid, errmsg otherwise
     */

    public function validate_run_option($option)
    {
        clearos_profile(__METHOD__, __LINE__);
        if ($option != 0 && $option != 1 && $option != 2)
            return lang('photo_organizer_run_option_is_invalid');
    }

    /**
     * Validation routine for folder format.
     *
     * @param string $folder_format folder format
     *
     * @return mixed void if format is valid, errmsg otherwise
     */

    public function validate_folder_format($folder_format)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (FALSE)
            return lang('photo_organizer_folder_format_is_invalid');
    }

    /**
     * Validation routine for file format.
     *
     * @param string $file_format file format
     *
     * @return mixed void if format is valid, errmsg otherwise
     */

    public function validate_file_format($file_format)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (FALSE)
            return lang('photo_organizer_file_format_is_invalid');
    }

    /**
     * Validation routine for source.
     *
     * @param boolean $source source
     *
     * @return mixed void if move is valid, errmsg otherwise
     */

    public function validate_source($source)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (!isset($source) || $source === '' ||  $source === '/')
            return lang('photo_organizer_source_is_invalid');
    }

    /**
     * Validation routine for move.
     *
     * @param boolean $move move
     *
     * @return mixed void if move is valid, errmsg otherwise
     */

    public function validate_move($move)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for recurse.
     *
     * @param boolean $recurse recurse
     *
     * @return mixed void if recurse is valid, errmsg otherwise
     */

    public function validate_recurse($recurse)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads configuration files.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG, 'match', "/(\S*)\s*=\s*\"(.*)\"/");

        try {
            $this->config = $configfile->load();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);

            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');

            $match = $file->replace_lines("/^$key\s*=\s*/", "$key=\"$value\"\n");

            if (!$match)
                $file->add_lines("$key=\"$value\"\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }
}
