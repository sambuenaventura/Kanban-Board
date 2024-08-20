// resources/js/tag-filter.js
window.applyFilters = function () {
    const form = document.getElementById("filter-form");
    const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
    const selectedTags = Array.from(checkboxes).map(
        (checkbox) => checkbox.value
    );

    // Construct the URL with selected tags
    let url = "/tasks";
    if (selectedTags.length > 0) {
        url += "/tag/" + encodeURIComponent(selectedTags.join(","));
    }

    // Debug: Log the URL to ensure it's correct
    console.log("Redirecting to:", url);

    // Redirect to the filtered URL
    window.location.href = url;
};
