document.addEventListener("DOMContentLoaded", function () {
    const deleteTaskModal = document.getElementById("deleteTaskModal");
    const closeDeleteTaskModalBtn = document.getElementById(
        "closeDeleteTaskModalBtn"
    );
    const deleteTaskForm = document.getElementById("deleteTaskForm");
    const deleteTaskBtns = document.querySelectorAll(".delete-task-btn");

    // Check if the deleteTaskModal and deleteTaskForm exist
    if (deleteTaskModal && deleteTaskForm) {
        deleteTaskBtns.forEach((btn) => {
            btn.addEventListener("click", function () {
                const taskId = this.getAttribute("data-task-id");
                const boardId = this.getAttribute("data-board-id"); // Get boardId from the button's data attribute
                deleteTaskForm.action = `/boards/${boardId}/tasks/${taskId}`; // Set the correct form action
                deleteTaskModal.style.display = "block";
            });
        });

        closeDeleteTaskModalBtn.addEventListener("click", function () {
            deleteTaskModal.style.display = "none";
        });

        window.addEventListener("click", function (event) {
            if (event.target === deleteTaskModal) {
                deleteTaskModal.style.display = "none";
            }
        });
    }
});
