// Board Modal Elements
const boardModal = document.getElementById("boardModal");
const openBoardBtn = document.getElementById("openBoardModalBtn");
const closeBoardBtn = document.getElementById("closeBoardModalBtn");

// Function to open board modal
function openBoardModal() {
    boardModal.style.display = "block";
}

// Function to close board modal
function closeBoardModal() {
    boardModal.style.display = "none";
}

// Event listener for opening the board modal
if (openBoardBtn) {
    openBoardBtn.onclick = function () {
        openBoardModal();
    };
}

// Event listener for closing the board modal
if (closeBoardBtn) {
    closeBoardBtn.onclick = function () {
        closeBoardModal();
    };
}

// Close board modal when clicking outside
window.onclick = function (event) {
    if (event.target == boardModal) {
        closeBoardModal();
    }
};
