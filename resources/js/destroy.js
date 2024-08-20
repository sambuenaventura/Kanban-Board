document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".confirm-remove").forEach((element) => {
        element.addEventListener("click", function (event) {
            event.preventDefault();
            if (confirm("Are you sure you want to remove this task?")) {
                deleteTask(this.getAttribute("data-task-id"));
            }
        });
    });
});

function deleteTask(taskId) {
    fetch(`/tasks/${taskId}/delete`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            console.log("Task deleted successfully:", data);

            // Find the task element
            const taskElement = document.querySelector(
                `[data-task-id="${taskId}"]`
            );
            const listItem = taskElement.closest("li");
            const dateGroup = listItem.closest(".mb-4");
            const taskList = dateGroup.querySelector("ul");

            // Remove the task from the UI
            listItem.remove();

            // Check if this was the last task for the date
            if (taskList.children.length === 0) {
                // If no tasks left, remove the entire date group
                dateGroup.remove();
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Failed to delete task. Please try again.");
        });
}
