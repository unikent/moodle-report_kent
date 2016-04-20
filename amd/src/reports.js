// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    report_kent
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module report_kent/reports
  */
define(['jquery', 'core/url'], function($, url) {
    return {
        init_menu_category: function(selector, report, param) {
            $(selector).on('change', function (e) {
            	var id = $(this).val();
            	window.location = url.relativeUrl("/report/kent/reports/" + report + "/index.php?" + param + "=" + id)
            });
        },

        init_manual_toggle: function(selector, uri) {
            $(selector).on('change', function(e) {
                if ($(this).is(":checked")) {
                    window.location = url.relativeUrl(uri + '1');
                } else {
                    window.location = url.relativeUrl(uri + '0');
                }
            });
        },

        init_ws: function(selector, webservice, rowarg, colarg) {
            var rowre = new RegExp(rowarg + "_([a-z0-9]*)", "g");
            var colre = new RegExp(colarg + "_([a-z0-9]*)", "g");

            $(selector).on('click', function() {
                // Extract data.
                var args = {};

                // First, row data.
                var rowdata = rowre.exec($(this).parent().attr('class'));
                if (rowdata.length != 2) {
                    return;
                }
                args[rowarg] = rowdata[1];

                // Second, column data.
                var coldata = colre.exec($(this).attr('class'));
                if (coldata.length != 2) {
                    return;
                }
                args[colarg] = coldata[1];

                require(['core/ajax', 'core/templates', 'core/notification'], function(ajax, templates, notification) {
                    var ajaxpromises = ajax.call([{
                        methodname: webservice,
                        args: args
                    }]);

                    ajaxpromises[0].done(function(data) {
                        console.log(data);
                        templates.render('report_kent/popout_table', data).done(function(html) {
                            notification.alert(html);
                        }.bind(this)).fail(notification.exception);
                    });

                    ajaxpromises[0].fail(notification.exception);
                });
            });
        }
    };
});
