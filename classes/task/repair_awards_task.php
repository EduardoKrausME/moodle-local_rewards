<?php

namespace local_rewards\task;

use local_rewards\manager\issuance_manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Repairs missing rewards that may have been skipped by events.
 */
class repair_awards_task extends \core\task\scheduled_task {
    /**
     * Returns the human readable task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string("rewardcompletiontask", "local_rewards");
    }

    /**
     * Executes the repair process.
     *
     * @return void
     */
    public function execute() {
        issuance_manager::repair_missing_issues();
    }
}
