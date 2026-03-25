<?php

require(__DIR__ . "/../../config.php");

require_login();

$id = required_param("id", PARAM_INT);
$issue = \local_rewards\manager\issuance_manager::get_issue($id);

if (!$issue) {
    throw new moodle_exception("rewardnotfound", "local_rewards");
}

$systemcontext = context_system::instance();
require_capability("local/rewards:viewown", $systemcontext);

if (!\local_rewards\manager\issuance_manager::user_can_view($issue, $USER->id)) {
    throw new required_capability_exception($systemcontext, "local/rewards:viewown", "nopermissions", "");
}

$PAGE->set_url("/local/rewards/view.php", ["id" => $id]);
$PAGE->set_context($systemcontext);
$PAGE->set_title(format_string($issue->name));
$PAGE->set_heading(get_string("rewardviewbadge", "local_rewards"));

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/award_page", \local_rewards\manager\issuance_manager::export_issue($issue));

echo $OUTPUT->footer();
