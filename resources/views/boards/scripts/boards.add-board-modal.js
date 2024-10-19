document.addEventListener("DOMContentLoaded", () => {
    // Board Modal Elements
    const boardModal = document.getElementById("createBoardModal");
    const openBoardBtn = document.getElementById("openBoardModalBtn");
    const closeBoardBtn = document.getElementById("closeBoardModalBtn");

    // Function to open board modal
    function openBoardModal() {
        if (boardModal) {
            boardModal.style.display = "block";
        }
    }

    // Function to close board modal
    function closeBoardModal() {
        if (boardModal) {
            boardModal.style.display = "none";
        }
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
        if (boardModal && event.target == boardModal) {
            closeBoardModal();
        }
    };
});
