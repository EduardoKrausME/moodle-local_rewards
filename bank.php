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
 * bank.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\manager\badge_bank_manager;

require(__DIR__ . "/../../config.php");

require_login();

$systemcontext = context_system::instance();
require_capability("local/rewards:manage", $systemcontext);

$deleteid = optional_param("delete", 0, PARAM_INT);

$PAGE->set_url("/local/rewards/bank.php");
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string("rewardbanktitle", "local_rewards"));
$PAGE->set_heading(get_string("rewardbanktitle", "local_rewards"));

if ($deleteid && confirm_sesskey()) {
    badge_bank_manager::delete_badge($deleteid);
    redirect(new moodle_url("/local/rewards/bank.php"));
}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/bank", [
    "createurl" => (new moodle_url("/local/rewards/badge_edit.php"))->out(false),
    "cards" => badge_bank_manager::export_bank_cards(),
]);

echo $OUTPUT->footer();
