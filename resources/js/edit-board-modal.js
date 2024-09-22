// Edit Board Modal Elements
const editBoardModal = document.getElementById("editBoardModal");
const editBoardForm = document.getElementById("editBoardForm");
const closeEditBoardBtn = document.getElementById("closeEditBoardModalBtn");

// Function to open edit board modal
function openEditBoardModal(boardId, boardName, boardDescription) {
    editBoardForm.action = `/boards/${boardId}`;
    document.getElementById("edit-name").value = boardName;
    document.getElementById("edit-description").value = boardDescription;
    editBoardModal.style.display = "block";
}

// Function to close edit board modal
function closeEditBoardModal() {
    editBoardModal.style.display = "none";
}

// Event listener for closing the edit board modal
if (closeEditBoardBtn) {
    closeEditBoardBtn.onclick = function () {
        closeEditBoardModal();
    };
}

// Close edit board modal when clicking outside
window.onclick = function (event) {
    if (event.target == editBoardModal) {
        closeEditBoardModal();
    }
};

// Add click event listeners to all edit buttons
document.querySelectorAll(".edit-board-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
        e.preventDefault();

        const boardId = this.dataset.boardId;
        const boardName = this.dataset.boardName;
        const boardDescription = this.dataset.boardDescription;

        openEditBoardModal(boardId, boardName, boardDescription);
    });
});

