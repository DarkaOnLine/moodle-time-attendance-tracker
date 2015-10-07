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


require_once('../../../config.php');
require_once('../lib/helpers.php');
global $DB, $CFG, $USER, $COURSE;

$table_name = 'block_attendance_tracker';
//$CFG->debug = -1;
//$CFG->debugdisplay = 1;
//$DB->set_debug(true);
if (isloggedin()) {

    $time_in_seconds = required_param('time',PARAM_INT);
    $user_id = required_param('user_id',PARAM_INT);
    $course_id = required_param('course_id',PARAM_INT);
    $lesson_id = required_param('lesson_id',PARAM_INT);
    $quiz_id = required_param('quiz_id',PARAM_INT);
    $key = required_param('key',PARAM_TEXT);

    if($user_id && $course_id && ($lesson_id || $quiz_id) && $key && $key == $USER->sesskey){

        $search_where = array(
            'user_id' => $user_id,
            'course_id' => $course_id,
            'date_from' => date('Y-m-d 00:00:00'),
            'date_to' => date('Y-m-d 23:59:59')
        );
        $lesson_id_sql = $quiz_id_sql = 'IS NULL';

        if($lesson_id){
            $lesson_id_sql = " = ".$lesson_id;
        }elseif($quiz_id){
            $quiz_id_sql = " = ".$quiz_id;
        }

        $record = $DB->get_record_select(
            $table_name,
            "user_id = :user_id AND
             course_id = :course_id AND
             lesson_id $lesson_id_sql AND
             quiz_id $quiz_id_sql AND
             date >= :date_from AND
             date <= :date_to",
            $search_where
        );

        if($record){
            $data_to_update = new stdClass;
            $data_to_update->id = $record->id;
            $data_to_update->time_in_seconds = $record->time_in_seconds + $time_in_seconds;
            $DB->update_record($table_name, $data_to_update, true);
        }else{
            $data = new stdClass;
            $data->user_id = $user_id;
            $data->course_id = $course_id;
            $data->lesson_id = $lesson_id ?: null;
            $data->quiz_id = $quiz_id ?: null;
            $data->date = date('Y-m-d H:i:s');
            $data->time_in_seconds = $time_in_seconds;
            $DB->insert_record($table_name, $data);
        }

        echo 'OK';
    }

}