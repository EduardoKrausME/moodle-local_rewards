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
 * course.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\manager\issuance_manager;

require(__DIR__ . "/../../config.php");

require_login();

$courseid = required_param("courseid", PARAM_INT);
$course = get_course($courseid);
$coursecontext = context_course::instance($courseid);
require_capability("local/rewards:viewcourse", $coursecontext);

$PAGE->set_url("/local/rewards/course.php", ["courseid" => $courseid]);
$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout("admin");
$PAGE->set_course($course);
$PAGE->set_title(get_string("rewardcoursebadges", "local_rewards"));
$PAGE->set_heading(format_string($course->fullname));

$rows = issuance_manager::export_course_rows($courseid);

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/course_badges", [
    "title" => get_string("rewardcoursebadges", "local_rewards"),
    "subtitle" => format_string($course->fullname),
    "rows" => $rows,
]);

echo $OUTPUT->footer();
