<?php

defined("MOODLE_INTERNAL") || die();

/**
 * Adds the badge bank page to site administration.
 */
if ($hassiteconfig) {
    $ADMIN->add("localplugins", new admin_externalpage(
        "local_rewards_bank",
        get_string("rewardbanktitle", "local_rewards"),
        new moodle_url("/local/rewards/bank.php"),
        "local/rewards:manage"
    ));
}
