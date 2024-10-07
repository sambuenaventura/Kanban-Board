document.addEventListener("DOMContentLoaded", () => {
    const filterModal = document.getElementById("filterModal");
    const selectedCountElement = document.getElementById("selectedCount");

    // Toggle the visibility of the modal
    window.toggleFilterModal = function () {
        filterModal.style.display =
            filterModal.style.display === "none" ? "block" : "none";
        if (filterModal.style.display === "block") {
            updateSelectedCount();
            setInitialFilterState();
        }
    };

    // Set initial state of filters based on URL parameters
    function setInitialFilterState() {
        const urlParams = new URLSearchParams(window.location.search);

        // Set tags
        const tags = urlParams.get("tags");
        if (tags) {
            const tagArray = tags.split(",");
            tagArray.forEach((tag) => {
                const checkbox = document.querySelector(
                    `input[name="tags[]"][value="${tag}"]`
                );
                if (checkbox) checkbox.checked = true;
            });
        }

        // Set priority
        const priority = urlParams.get("priority");
        if (priority) {
            const priorityRadio = document.querySelector(
                `input[name="priority"][value="${priority}"]`
            );
            if (priorityRadio) priorityRadio.checked = true;
        }

        // Set due date
        const due = urlParams.get("due");
        if (due) {
            const dueRadio = document.querySelector(
                `input[name="due"][value="${due}"]`
            );
            if (dueRadio) dueRadio.checked = true;
        }

        updateSelectedCount();
    }

    // Update the count of selected filters
    window.updateSelectedCount = function () {
        const selectedTagsCount = document.querySelectorAll(
            'input[name="tags[]"]:checked'
        ).length;
        const selectedPriorityCount = document.querySelector(
            'input[name="priority"]:checked'
        )
            ? 1
            : 0;
        const selectedDueCount = document.querySelector(
            'input[name="due"]:checked'
        )
            ? 1
            : 0;
        const totalSelectedCount =
            selectedTagsCount + selectedPriorityCount + selectedDueCount;
        selectedCountElement.textContent = totalSelectedCount;
    };

    // Clear all selected filters (tags, priority, and due date)
    window.clearAllFilters = function () {
        const checkboxes = document.querySelectorAll('input[name="tags[]"]');
        checkboxes.forEach((checkbox) => (checkbox.checked = false));
        const priorityRadios = document.querySelectorAll(
            'input[name="priority"]'
        );
        priorityRadios.forEach((radio) => (radio.checked = false));
        const dueRadios = document.querySelectorAll('input[name="due"]');
        dueRadios.forEach((radio) => (radio.checked = false));
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
        const selectedPriority = document.querySelector(
            'input[name="priority"]:checked'
        )?.value;
        const selectedDue = document.querySelector(
            'input[name="due"]:checked'
        )?.value;

        const boardId = window.location.pathname.split("/")[2];
        let url = `/boards/${boardId}`;
        const params = new URLSearchParams();

        if (selectedTags.length > 0) {
            params.append("tags", selectedTags.join(","));
        }
        if (selectedPriority) {
            params.append("priority", selectedPriority);
        }
        if (selectedDue) {
            params.append("due", selectedDue);
        }

        if (params.toString()) {
            url += `?${params.toString()}`;
        }

        console.log("Redirecting to:", url);
        window.location.href = url;
    };

    // Update the count when filters change
    document
        .querySelectorAll(
            'input[name="tags[]"], input[name="priority"], input[name="due"]'
        )
        .forEach((input) => {
            input.addEventListener("change", updateSelectedCount);
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
