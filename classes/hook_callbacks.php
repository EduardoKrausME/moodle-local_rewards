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
 * hook_callbacks.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards;

use core\hook\output\before_footer_html_generation;
use dml_exception;
use Exception;
use local_rewards\manager\issuance_manager;

/**
 * Output hook callbacks for popup rendering.
 */
class hook_callbacks {
    /**
     * Renders the next reward popup before the footer.
     *
     * @param before_footer_html_generation $hook The footer hook.
     * @return void
     * @throws \coding_exception
     * @throws \core\exception\moodle_exception
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook) {
        global $OUTPUT, $PAGE, $USER;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        try {
            if (!$issue = issuance_manager::get_pending_popup($USER->id)) {
                return;
            }
        } catch (dml_exception $e) {
            return;
        }

        issuance_manager::mark_popup_shown($issue->id);
        $data = issuance_manager::export_issue($issue);

        $hook->add_html($OUTPUT->render_from_template("local_rewards/popup", $data));
        $PAGE->requires->js_call_amd("local_rewards/popup", "init");
    }
}
