console.log("deleteTask function loaded");
function deleteTask(taskId) {
    if (!confirm("Are you sure you want to delete this task?")) {
        return;
    }

    fetch(`/tasks/${taskId}`, {
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
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            console.log("Task deleted successfully:", data);
            location.reload(); // Reload the page to reflect changes
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("Failed to delete task. Please try again.");
        });
}
