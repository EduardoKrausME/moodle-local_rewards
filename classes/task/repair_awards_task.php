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
 * repair_awards_task.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\task;

use coding_exception;
use core\task\scheduled_task;
use local_rewards\manager\issuance_manager;

/**
 * Repairs missing rewards that may have been skipped by events.
 */
class repair_awards_task extends scheduled_task {
    /**
     * Returns the human readable task name.
     *
     * @return string
     * @throws coding_exception
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
