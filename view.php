<?php

use local_rewards\manager\issuance_manager;

require(__DIR__ . "/../../config.php");

require_login();

$id = required_param("id", PARAM_INT);
$issue = issuance_manager::get_issue($id);

if (!$issue) {
    throw new moodle_exception("rewardnotfound", "local_rewards");
}

$viewcontext = context_course::instance($issue->courseid);

if (!issuance_manager::user_can_view($issue, $USER->id)) {
    throw new required_capability_exception($viewcontext, "local/rewards:viewcourse", "nopermissions", "");
}

$PAGE->set_url("/local/rewards/view.php", ["id" => $id]);
$PAGE->set_context($viewcontext);
$PAGE->set_title(format_string($issue->name));
$PAGE->set_heading(get_string("rewardviewbadge", "local_rewards"));

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/award_page", issuance_manager::export_issue($issue));

echo $OUTPUT->footer();
