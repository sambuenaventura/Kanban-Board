document.addEventListener("DOMContentLoaded", () => {
    // Task Create Event
    window.Echo.channel("boards." + boardId).listen(".task.created", (e) => {
        console.log("New task created:", e.task);
        if (e.userId !== window.currentUserId) {
            const updateIndicator = document.getElementById("update-indicator");
            updateIndicator.classList.remove("opacity-0");
            updateIndicator.classList.add("opacity-100");
        }
    });

    // Task Update Event
    window.Echo.channel("boards." + boardId).listen(".task.updated", (e) => {
        console.log("Task update received:", e);
        if (e.userId !== window.currentUserId) {
            const updateIndicator = document.getElementById("update-indicator");
            updateIndicator.classList.remove("opacity-0");
            updateIndicator.classList.add("opacity-100");
        }
    });

    // Task Delete Event
    window.Echo.channel("boards." + boardId).listen(".task.deleted", (e) => {
        console.log("Task deleted:", e);

        const taskElement = document.querySelector(
            `[data-task-id="${e.taskId}"]`
        );
        if (taskElement) {
            const ulElement = taskElement.closest("ul");
            taskElement.remove();

            // Update the task count
            const taskCountElement = document.querySelector(".task-count");
            if (taskCountElement) {
                let currentCount = parseInt(
                    taskCountElement.textContent.match(/\d+/)[0]
                ); // Extract current count
                taskCountElement.textContent = `(${currentCount - 1})`; // Decrement count
            }

            // Remove empty elements if needed
            if (ulElement.children.length === 0) {
                const dateHeader = ulElement.previousElementSibling;
                dateHeader.remove();
                ulElement.remove();
            }

            const updateIndicator = document.getElementById("update-indicator");
            updateIndicator.classList.remove("opacity-0");
            updateIndicator.classList.add("opacity-100");
        }
    });
});
