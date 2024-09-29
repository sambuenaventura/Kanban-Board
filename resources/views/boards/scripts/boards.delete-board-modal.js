document.addEventListener("DOMContentLoaded", function () {
    const deleteBoardModal = document.getElementById("deleteBoardModal");
    const closeDeleteBoardModalBtn = document.getElementById(
        "closeDeleteBoardModalBtn"
    );
    const deleteBoardForm = document.getElementById("deleteBoardForm");
    const deleteBoardBtns = document.querySelectorAll(".delete-board-btn");

    // Check if modal and buttons exist before proceeding
    if (deleteBoardModal && deleteBoardForm && closeDeleteBoardModalBtn) {
        deleteBoardBtns.forEach((btn) => {
            btn.addEventListener("click", function () {
                const boardId = this.getAttribute("data-board-id");
                deleteBoardForm.action = `/boards/${boardId}`;
                deleteBoardModal.style.display = "block";
            });
        });

        closeDeleteBoardModalBtn.addEventListener("click", function () {
            deleteBoardModal.style.display = "none";
        });

        window.addEventListener("click", function (event) {
            if (event.target === deleteBoardModal) {
                deleteBoardModal.style.display = "none";
            }
        });
    }
});
