<?php
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

/**
 * Kent's custom reports in one plugin.
 *
 * @package    report_kent
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_kent;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * CSV Table helper.
 */
class report_table extends \table_sql
{
    private $_itemtotal;
    private $_download;
    private $_ispaging;
    private $_pagenumber;

    /**
     * @param string $uniqueid A string identifying this table.
     */
    public function __construct($uniqueid, $defaultpagesize = 30) {
        parent::__construct($uniqueid);

        $this->_itemtotal = 0;
        $this->_pagesize = optional_param('pagesize', $defaultpagesize, \PARAM_INT);
        $this->_pagenumber = optional_param('page', 0, \PARAM_INT);
        $this->_download = optional_param('download', '', \PARAM_ALPHA);
        $this->_ispaging = empty($this->_download);
    }

    /**
     * Produce a category select dropdown.
     */
    public function category_dropdown($report, $current) {
        global $DB, $PAGE;

        $PAGE->requires->js_call_amd('report_kent/reports', 'init_menu_category', array('#menucategory', $report, 'category'));

        // Allow restriction by category.
        $categories = array(
            0 => "All"
        );

        $categories += \coursecat::make_categories_list();
        return \html_writer::select($categories, 'category', $current);
    }

    /**
     * Setup the table.
     */
    public function setup() {
        global $PAGE;

        $this->is_downloading($this->_download, $this->uniqueid, $this->uniqueid);

        // Copy columns into headers if we have no columns defined.
        if (empty($this->columns) && !empty($this->headers)) {
            $columns = array_map(function($header) {
                $header = preg_replace('/[^a-zA-Z _]/', '', $header);
                return str_replace(' ', '_', strtolower($header));
            }, $this->headers);
            $this->define_columns($columns);
        }

        // Default baseurl to page URL.
        if (empty($this->baseurl)) {
            $this->define_baseurl($PAGE->url);
        }

        $this->show_download_buttons_at(array(\TABLE_P_BOTTOM));

        parent::setup();
    }

    /**
     * This method actually directly echoes the row passed to it now or adds it
     * to the download. If this is the first row and start_output has not
     * already been called this method also calls start_output to open the table
     * or send headers for the downloaded.
     * Can be used as before. print_html now calls finish_html to close table.
     *
     * @param array $row a numerically keyed row of data to add to the table.
     * @param string $classname CSS class name to add to this row's tr tag.
     * @return bool success.
     */
    public function add_data($row, $classname = '') {
        $this->_itemtotal++;

        // Paging logic.
        if ($this->_ispaging) {
            $page = floor($this->_itemtotal / $this->_pagesize);
            if ($page != $this->_pagenumber) {
                return;
            }
        }

        return parent::add_data($row, $classname);
    }

    /**
     * You should call this to finish outputting the table data after adding
     * data to the table with add_data or add_data_keyed.
     */
    public function finish_output($closeexportclassdoc = true) {
        if ($this->_ispaging) {
            $this->pagesize($this->_pagesize, $this->_itemtotal);
        }

        return parent::finish_output($closeexportclassdoc);
    }
}
