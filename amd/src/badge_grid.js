define(["jquery"], function ($) {
    /**
     * Updates the selected visual card.
     *
     * @param {Object} root The grid root.
     * @param {String|Number} value The selected value.
     * @return void
     */
    function syncSelection(root, value) {
        root.find("[data-badge-option]").removeClass("is-selected");
        root.find("[data-badge-option='" + value + "']").addClass("is-selected");
    }

    /**
     * Initializes the grid selector.
     *
     * @return void
     */
    function init() {
        $("[data-local-rewards-grid='true']").each(function () {
            var root = $(this);
            var input = $("#id_rewards_badgeid");

            if (!input.length) {
                return;
            }

            syncSelection(root, input.val() || 0);

            root.on("click", "[data-badge-option]", function (e) {
                e.preventDefault();
                var value = $(this).attr("data-badge-option");
                input.val(value).trigger("change");
                syncSelection(root, value);
            });
        });
    }

    return {
        init: init
    };
});
