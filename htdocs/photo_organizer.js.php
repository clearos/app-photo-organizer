<?php

/**
 * Photo Organizer javascript helper.
 *
 * @category   Apps
 * @package    Photo_Organizer 
 * @subpackage Javascript
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
//////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

clearos_load_language('base');
clearos_load_language('photo_organizer');

header('Content-Type: application/x-javascript');

echo "

$(document).ready(function() {
    $('#email_notification').css('width', '220px');
    if ($('#source').val() == '')
        $('#source_text').html(source_button());
    if ($(location).attr('href').match('.*\/file_browser\/index\/init$') != null)
        get_file_browser($.base64.encode('/'));
});

function source_button() {
    return '<div class=\'theme-button-set ui-button-set\' style=\'margin-left: 5px;\'>' +
        '<a href=\'/app/photo_organizer/file_browser/index/init\' class=\'theme-button-set-first theme-button-set-last theme-anchor theme-anchor-edit theme-anchor-important\'>" . lang('base_configure') . "</a>' +
        '</div>';
}

function get_file_browser(path) {

    if (path == undefined)
        path = $.base64.encode('/');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/photo_organizer/dir_listing',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + '&path=' + encodeURIComponent(path) + '&include_files=0',
        success: function(data) {
            table_file_browser.fnClearTable();
            $('#file_browser_wrapper div:first').html('<span>" . lang('photo_organizer_location') . ": ' + $.base64.decode(path) + '</span>');
            var icon = '<div class=\'theme-file-browser-folder\'></div>';
            for (var index = 0 ; index < data.contents.length; index++) {
                add_row = table_file_browser.fnAddData([
                    icon,
                    data.contents[index].name +
                    '<input type=\'hidden\' id=\'val-myrow-' + (index +1) + '\' value=\'' + data.contents[index].base64 + '\' />'
                ]);
                var my_row = $('#file_browser').dataTable().fnSettings().aoData[add_row[0]].nTr;
                my_row.setAttribute('id', 'myrow-' + (index + 1));
            }
            table_file_browser.fnAdjustColumnSizing();
            $('#file_browser tr').find('th:eq(0)').css('width', '50');
            $('#file_browser tr').find('th:eq(0)').attr('align', 'center');
            $('#file_browser tr').find('td:eq(0)').attr('align', 'center');
            $('#file_browser thead tr').find('th:eq(0)').html('<div class=\'theme-file-browser-parent\' id=\'parent\'></div>');
            // Make sure it's not empty
            if (!$('#file_browser tr').find('td:eq(0)').hasClass('dataTables_empty')) {
                $('#file_browser tbody tr td').mouseover(function() {
                    if ($('td:eq(0) div', $(this).parent()).hasClass('theme-file-browser-file'))
                        return;
                    $(this).css('cursor', 'pointer');
                });
                $('#file_browser tbody tr').mouseover(function() {
                    if ($('td:eq(0) div', $(this)).hasClass('theme-file-browser-file'))
                        return;
                    $('#' + this.id + ' td').css(
                        'font-weight', 'bold'
                    );
                });
                $('#file_browser tbody tr').mouseout(function() {
                    $('#' + this.id + ' td').css('font-weight', 'normal');
                });
            }
            $('#file_browser tbody tr td').click(function(e) {
                if (e.target['tagName'] == 'TD' || e.target['tagName'] == 'DIV') {
                    get_file_browser($('#val-' + this.parentNode.id).val());
                    $('#source_path').val($.base64.decode($('#val-' + this.parentNode.id).val()));
                    $('html, body').animate({scrollTop:0}, 'slow');
                    return;
                }
            });
            $('#parent').click(function() {
                get_file_browser(data.previous);
                // Move window up after selection
                $('html, body').animate({scrollTop:0}, 'slow');
                return;
            });
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

function select_folder(path, selected) {
    qs = '&';
    if (path instanceof Array) {
        for(i=0; i<path.length; i++)
            qs += 'path[]=' + encodeURIComponent(path[i]) + '&';
        qs += 'include=' + (selected ? 1 : 0);
    } else {
        qs += 'path=' + encodeURIComponent(path) + '&include=' + (selected ? 1 : 0);
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/app/remote_backup/ajax/file_select',
        data: 'ci_csrf_token=' + $.cookie('ci_csrf_token') + qs,
        success: function(data) {
            $('#file_browser').find(':checkbox').removeAttr('readonly');
        },
        error: function(xhr, text, err) {
            // Don't display any errors if ajax request was aborted due to page redirect/reload
            if (xhr['abort'] == undefined)
                clearos_dialog_box('error', '" . lang('base_warning') . "', xhr.responseText.toString());
        }
    });
}

";

// vim: ts=4 syntax=javascript
