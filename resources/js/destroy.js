document.addEventListener("DOMContentLoaded", () => {
    let taskIdToRemove;

    // Show modal and set the task ID
    document.querySelectorAll(".confirm-remove").forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            taskIdToRemove = event.target.dataset.taskId; // Get task ID from data attribute
            document.getElementById("removeTaskModal").style.display = "block"; // Show the modal
        });
    });

    // Confirm removal
    document
        .getElementById("confirmRemoveBtn")
        .addEventListener("click", () => {
            fetch(`/tasks/${taskIdToRemove}/remove`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        // Handle specific status codes
                        return response.json().then((err) => {
                            throw new Error(
                                `Error ${response.status}: ${err.message}`
                            );
                        });
                    }
                    return response.json();
                })
                .then(() => {
                    // Remove the task from the DOM
                    const taskElement = document.querySelector(
                        `li[data-task-id="${taskIdToRemove}"]`
                    );
                    if (taskElement) {
                        const taskList = taskElement.closest("ul"); // Get the task list
                        taskElement.remove(); // Remove the task element

                        // Check if there are any remaining tasks
                        const remainingTasks = taskList.querySelectorAll("li");
                        if (remainingTasks.length === 0) {
                            const dateBlock = taskList.closest(".mb-4"); // Get the parent block of the task list
                            dateBlock.remove(); // Remove the entire block if no tasks are left
                        }

                        // Log success message
                        console.log(
                            `Task with ID ${taskIdToRemove} deleted successfully.`
                        );
                    }
                    document.getElementById("removeTaskModal").style.display =
                        "none"; // Hide the modal
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert(error.message); // Display an alert with the error message
                });
        });

    // Cancel removal
    document.getElementById("cancelRemoveBtn").addEventListener("click", () => {
        document.getElementById("removeTaskModal").style.display = "none"; // Hide the modal
    });
});
