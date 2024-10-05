document.addEventListener("DOMContentLoaded", () => {
    const filterModal = document.getElementById("filterModal");
    const selectedCountElement = document.getElementById("selectedCount");

    // Toggle the visibility of the modal
    window.toggleFilterModal = function () {
        filterModal.style.display =
            filterModal.style.display === "none" ? "block" : "none";
        if (filterModal.style.display === "block") {
            updateSelectedCount();
        }
    };

    // Update the count of selected tags and priority
    window.updateSelectedCount = function () {
        const checkedBoxes = document.querySelectorAll(
            'input[name="tags[]"]:checked'
        );
        const selectedTagsCount = checkedBoxes.length;

        // Check if any priority is selected
        const selectedPriorityCount = document.querySelector(
            'input[name="priority"]:checked'
        )
            ? 1
            : 0; // Count as 1 if a priority is selected

        const totalSelectedCount = selectedTagsCount + selectedPriorityCount;
        selectedCountElement.textContent = totalSelectedCount;
    };

    // Clear all selected filters (tags and priority)
    window.clearAllFilters = function () {
        const checkboxes = document.querySelectorAll('input[name="tags[]"]');
        checkboxes.forEach((checkbox) => (checkbox.checked = false));

        // Clear priority selection
        const priorityRadios = document.querySelectorAll(
            'input[name="priority"]'
        );
        priorityRadios.forEach((radio) => (radio.checked = false));

        updateSelectedCount();
    };

    // Apply selected filters and construct the URL
    window.applyFilters = function () {
        const checkedBoxes = document.querySelectorAll(
            'input[name="tags[]"]:checked'
        );
        const selectedTags = Array.from(checkedBoxes).map(
            (checkbox) => checkbox.value
        );

        // Get the selected priority from the radio buttons
        const selectedPriority = document.querySelector(
            'input[name="priority"]:checked'
        )?.value;

        const boardId = window.location.pathname.split("/")[2];
        let url = `/boards/${boardId}`;

        // Append tags to the URL if any are selected
        if (selectedTags.length > 0) {
            url += `?tags=${encodeURIComponent(selectedTags.join(","))}`;
        }

        // Append priority to the URL if one is selected
        if (selectedPriority) {
            url +=
                selectedTags.length > 0
                    ? `&priority=${selectedPriority}`
                    : `?priority=${selectedPriority}`;
        }

        console.log("Redirecting to:", url);
        window.location.href = url;
    };

    // Update the count when filters change
    document.querySelectorAll('input[name="tags[]"]').forEach((checkbox) => {
        checkbox.addEventListener("change", updateSelectedCount);
    });

    document.querySelectorAll('input[name="priority"]').forEach((radio) => {
        radio.addEventListener("change", updateSelectedCount);
    });

    // Close modal when clicking outside of it
    window.onclick = function (event) {
        if (event.target === filterModal) {
            toggleFilterModal();
        }
    };

    // Initial count update when the page loads
    updateSelectedCount();
});
