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

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
global $DB, $CFG, $USER, $COURSE;

$tablename = 'block_attendance_tracker';

if (isloggedin()) {

    $timeinseconds = required_param('time', PARAM_INT);
    $userid = required_param('user_id', PARAM_INT);
    $courseid = required_param('course_id', PARAM_INT);
    $lessonid = required_param('lesson_id', PARAM_INT);
    $quizid = required_param('quiz_id', PARAM_INT);
    $key = required_param('key', PARAM_TEXT);

    if ($userid && $courseid && ($lessonid || $quizid) && $key && $key == $USER->sesskey) {

        $searchwhere = array(
            'user_id' => $userid,
            'course_id' => $courseid,
            'date_from' => date('Y-m-d 00:00:00'),
            'date_to' => date('Y-m-d 23:59:59')
        );
        $lessonidsql = $quizidsql = 'IS NULL';

        if ($lessonid) {
            $lessonidsql = " = ".$lessonid;
        } else if ($quizid) {
            $quizidsql = " = ".$quizid;
        }

        $record = $DB->get_record_select(
            $tablename,
            "user_id = :user_id AND
             course_id = :course_id AND
             lesson_id $lessonidsql AND
             quiz_id $quizidsql AND
             date >= :date_from AND
             date <= :date_to",
            $searchwhere
        );

        if ($record) {
            $datatoupdate = new stdClass;
            $datatoupdate->id = $record->id;
            $datatoupdate->time_in_seconds = $record->time_in_seconds + $timeinseconds;
            $DB->update_record($tablename, $datatoupdate, true);
        } else {
            $data = new stdClass;
            $data->user_id = $userid;
            $data->course_id = $courseid;
            $data->lesson_id = $lessonid ?: null;
            $data->quiz_id = $quizid ?: null;
            $data->date = date('Y-m-d H:i:s');
            $data->time_in_seconds = $timeinseconds;
            $DB->insert_record($tablename, $data);
        }
        echo "{'success': true}";
    } else {
        echo "{'success': false}";
    }
}
