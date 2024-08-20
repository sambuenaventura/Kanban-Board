document.addEventListener("DOMContentLoaded", function () {
    function closeAllDropdowns() {
        document
            .querySelectorAll('[id^="dropdown-"]')
            .forEach(function (dropdown) {
                dropdown.classList.add("hidden");
            });
    }

    // Toggle dropdown
    document.querySelectorAll(".toggle-dropdown").forEach(function (element) {
        element.addEventListener("click", function (event) {
            event.stopPropagation();
            var taskId = this.getAttribute("data-task-id");
            var dropdown = document.getElementById("dropdown-" + taskId);

            // Close all dropdowns
            closeAllDropdowns();

            // Toggle the clicked dropdown
            dropdown.classList.toggle("hidden");
        });
    });

    // // Confirm remove
    // document.querySelectorAll(".confirm-remove").forEach(function (element) {
    //     element.addEventListener("click", function (event) {
    //         event.preventDefault();
    //         var taskId = this.getAttribute("data-task-id");
    //         if (confirm("Are you sure you want to remove this task?")) {
    //             // Here you would typically call your backend to remove the task
    //             console.log("Removing task " + taskId);
    //         }
    //     });
    // });

    // Close dropdowns when clicking outside
    document.addEventListener("click", function (event) {
        if (!event.target.closest(".toggle-dropdown")) {
            closeAllDropdowns();
        }
    });
});
