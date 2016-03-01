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

namespace report_kent\reports\deadlines;

defined('MOODLE_INTERNAL') || die();

class report
{
    public static function get_deadlines($showpast) {
        $cachekey = 'deadlines_' . $showpast;
        $cache = \cache::make('report_kent', 'kentreports');
        $content = $cache->get($cachekey);

        if ($content !== false) {
            return $content;
        }

        $deadlines = array();

        // Turnitin deadlines.
        $tdeadlines = turnitintool::get_deadlines($showpast);
        if (!empty($tdeadlines)) {
            $deadlines = array_merge($deadlines, $tdeadlines);
        }
        unset($tdeadlines);

        // TurnitinTwo deadlines.
        $ttdeadlines = turnitintooltwo::get_deadlines($showpast);
        if (!empty($ttdeadlines)) {
            $deadlines = array_merge($deadlines, $ttdeadlines);
        }
        unset($ttdeadlines);

        // Quiz deadlines.
        $qdeadlines = quiz::get_deadlines($showpast);
        if (!empty($qdeadlines)) {
            $deadlines = array_merge($deadlines, $qdeadlines);
        }
        unset($qdeadlines);

        // Assign deadlines.
        $adeadlines = assign::get_deadlines($showpast);
        if (!empty($adeadlines)) {
            $deadlines = array_merge($deadlines, $adeadlines);
        }
        unset($adeadlines);

        // Sort deadlines by date.
        usort($deadlines, function($a, $b) {
            if ($a->end >= $b->end) {
                return 1;
            }
            return 0;
        });

        // Set cache.
        $cache->set($cachekey, $deadlines);

        return $deadlines;
    }
}
