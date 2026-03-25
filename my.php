<?php

require(__DIR__ . "/../../config.php");

require_login();

$systemcontext = context_system::instance();
require_capability("local/rewards:viewown", $systemcontext);

$PAGE->set_url("/local/rewards/my.php");
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string("rewardmybadges", "local_rewards"));
$PAGE->set_heading(get_string("rewardmybadges", "local_rewards"));

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/my_badges", [
    "cards" => \local_rewards\manager\issuance_manager::export_issue_cards($USER->id),
]);

echo $OUTPUT->footer();
