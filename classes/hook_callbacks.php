<?php

namespace local_rewards;

use core\hook\output\before_footer_html_generation;
use dml_exception;
use local_rewards\manager\issuance_manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Output hook callbacks for popup rendering.
 */
class hook_callbacks {
    /**
     * Renders the next reward popup before the footer.
     *
     * @param before_footer_html_generation $hook The footer hook.
     * @return void
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
