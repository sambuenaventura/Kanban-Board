document.addEventListener("DOMContentLoaded", () => {
    window.applyFilters = function () {
        const form = document.getElementById("filter-form");
        const checkboxes = form.querySelectorAll(
            'input[type="checkbox"]:checked'
        );
        const selectedTags = Array.from(checkboxes).map(
            (checkbox) => checkbox.value
        );

        // Get the current board ID from the URL
        const boardId = window.location.pathname.split("/")[2];

        // Construct the URL with the selected tags
        let url = `/boards/${boardId}`;
        if (selectedTags.length > 0) {
            url += `?tags=${encodeURIComponent(selectedTags.join(","))}`;
        }

        // Debug: Log the URL to ensure it's correct
        console.log("Redirecting to:", url);

        // Redirect to the filtered URL
        window.location.href = url;
    };
});
