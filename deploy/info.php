<?php

/////////////////////////////////////////////////////////////////////////////
// General information
///////////////////////////////////////////////////////////////////////////// 
$app['basename'] = 'photo_organizer';
$app['version'] = '2.1.7';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('photo_organizer_app_description');
$app['tooltip'] = lang('photo_organizer_app_tooltip');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('photo_organizer_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_file');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-flexshare-core',
    'perl-Image-ExifTool >= 9.17'
);

$app['core_file_manifest'] = array(
   'photo_organizer.conf' => array(
        'target' => '/etc/clearos/photo_organizer.conf',
        'mode' => '0644',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'photo-organizer' => array(
        'target' => '/usr/sbin/photo-organizer',
        'mode' => '0744',
        'owner' => 'root',
        'group' => 'root',
    )
);

$app['core_directory_manifest'] = array(
   '/var/clearos/photo_organizer' => array('mode' => '755', 'owner' => 'webconfig', 'group' => 'webconfig')
);

$app['delete_dependency'] = array(
    'app-photo-organizer-core'
);
