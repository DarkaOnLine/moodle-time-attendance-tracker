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
 *
 * @package    block_attendance_tracker
 * @copyright  Darius Matulionis | http://matulionis.lt | darius@matulionis.lt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


global $CFG;
require_once($CFG->libdir . '/formslib.php');

class attendance_tracker_helpers
{

    protected $db;

    public function __construct()
    {
        global $DB;
        $this->db = $DB;
    }

    public function get_module_by_cmid($cmid)
    {
        $sql = "SELECT cm.*, md.name as modname
        FROM {course_modules} cm,
        {modules} md
        WHERE cm.id = ? AND
        md.id = cm.module";

        $cmrec = $this->db->get_record_sql($sql, array($cmid));

        return $cmrec;
    }


    public function get_quiz_id_by_attempt_id($quiz_attempt_id)
    {
        $row = $this->db->get_record('quiz_attempts',array('id' => $quiz_attempt_id));

        if($row)
            return $row->quiz;

        return false;
    }

    /**
     * Check that a given name is in a permittable format
     *
     * @param string $pageformat
     * @return bool
     */
    public function blocks_allowed_in_format($pageformat, $formats) {
        $accept = NULL;
        $maxdepth = -1;
        if (!$formats) {
            $formats = array();
        }
        foreach ($formats as $format => $allowed) {
            $formatregex = '/^'.str_replace('*', '[^-]*', $format).'.*$/';
            $depth = substr_count($format, '-');
            if (preg_match($formatregex, $pageformat) && $depth > $maxdepth) {
                $maxdepth = $depth;
                $accept = $allowed;
            }
        }
        if ($accept === NULL) {
            $accept = !empty($formats['all']);
        }
        return $accept;
    }

    public function calendar($start_date, $end_date = null)
    {
        $calendar = new helper_calendar();
        $calendar->set_data(array('datefrom' => $start_date));

        if($end_date)
            $calendar->set_data(array('dateto' => $end_date));

        return $calendar;
    }


    public function seconds_to_string($seconds) {
        $conmin = 60;
        $conhour = $conmin * 60;
        $conday = $conhour * 24;

        $tempday = (int)((int)$seconds / (int)$conday);
        $seconds = $seconds - $tempday * $conday;
        $temphour = (int)((int)$seconds / (int)$conhour);
        $seconds = $seconds - $temphour * $conhour;
        $tempmin = (int)((int)$seconds / (int)$conmin);
        $seconds = $seconds - $tempmin * $conmin;

        $str = '';
        if ($tempday != 0) {
            $str = $str.$tempday.get_string('days', 'block_attendance_tracker');
        }
        if ($temphour != 0) {
            $str = $str.$temphour.get_string('hours', 'block_attendance_tracker');
        }
        if ($tempmin != 0) {
            $str = $str.$tempmin.get_string('minuts', 'block_attendance_tracker');
        }
        $str = $str.$seconds.get_string('seconds', 'block_attendance_tracker');
        return $str;
    }
}


class helper_calendar extends moodleform {

    function definition() {
        $mform = & $this->_form;
        $mform->addElement('date_time_selector', 'datefrom', get_string('start', 'block_attendance_tracker'));
        $mform->addElement('date_time_selector', 'dateto', get_string('end', 'block_attendance_tracker'));

        // Buttons
        $this->add_action_buttons(false, get_string('calculate', 'block_attendance_tracker'));
    }

}

function dump(){
    $arg_list = func_get_args();
    $numargs = func_num_args();
    for ($i = 0; $i < $numargs; $i++) {
        echo '<pre>';
        var_dump($arg_list[$i]);
        echo '</pre>';
    }
}