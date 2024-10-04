document.addEventListener("DOMContentLoaded", function () {
    function closeAllDropdowns() {
        document
            .querySelectorAll('[id^="dropdown-"]')
            .forEach(function (dropdown) {
                dropdown.classList.add("hidden");
            });
    }

    document.querySelectorAll(".toggle-dropdown").forEach(function (element) {
        element.addEventListener("click", function (event) {
            event.stopPropagation();
            var taskId = this.getAttribute("data-task-id");
            var dropdown = document.getElementById("dropdown-" + taskId);

            if (dropdown) {
                // Check if dropdown exists
                closeAllDropdowns();
                dropdown.classList.toggle("hidden");
            }
        });
    });

    document.addEventListener("click", function (event) {
        if (!event.target.closest(".toggle-dropdown")) {
            closeAllDropdowns();
        }
    });
});
