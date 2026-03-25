<?php

require(__DIR__ . "/../../config.php");

$token = required_param("token", PARAM_ALPHANUMEXT);
$issue = \local_rewards\manager\issuance_manager::get_issue_by_token($token);

if (!$issue) {
    throw new moodle_exception("rewardnotfound", "local_rewards");
}

if (!\local_rewards\manager\issuance_manager::is_public_enabled($issue)) {
    throw new moodle_exception("rewardpublicdisabled", "local_rewards");
}

$systemcontext = context_system::instance();
$PAGE->set_url("/local/rewards/public.php", ["token" => $token]);
$PAGE->set_context($systemcontext);
$PAGE->set_title(format_string($issue->name));
$PAGE->set_heading(get_string("rewardpublicpage", "local_rewards"));
$PAGE->set_pagelayout("embedded");

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/award_page", \local_rewards\manager\issuance_manager::export_issue($issue, true));

echo $OUTPUT->footer();
