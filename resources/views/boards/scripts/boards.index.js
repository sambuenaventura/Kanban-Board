// Board Create Event
window.Echo.channel("boards").listen(".board.created", (e) => {
    console.log("Board created:", e);
    updateBoardCount();
});

// Board Remove Collaborator Event
document.addEventListener("DOMContentLoaded", () => {
    const boardContainer = document.getElementById("board-container");
    const totalBoardsSpan = document.querySelector(".total-boards-count");

    // Function to update board count display
    function updateBoardCount() {
        const remainingBoards =
            boardContainer.querySelectorAll("[data-board-id]");
        totalBoardsSpan.textContent = `${remainingBoards.length} Boards`;
    }

    // Listen for WebSocket event to remove a board
    window.Echo.channel("boards").listen(".user.removed", (e) => {
        console.log("User removed:", e.user_id, "from board:", e.board_id);

        // Check if the current user is the one being removed
        if (e.user_id === currentUserId) {
            // Find the board element by its ID
            const boardElement = document.querySelector(
                `[data-board-id="${e.board_id}"]`
            );

            // Remove the board element from the DOM
            if (boardElement) {
                boardElement.remove();
                console.log("Board removed:", e.board_id);
                updateBoardCount();

                // Check if there are any boards left
                const remainingBoards =
                    boardContainer.querySelectorAll("[data-board-id]");

                // If no boards are left, add the "No boards" message
                if (remainingBoards.length === 0) {
                    const noBoardsMessage = document.createElement("div");
                    noBoardsMessage.className = "col-span-full";
                    noBoardsMessage.innerHTML = `
                        <div class="text-center py-12 bg-white rounded-lg shadow-md border border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" height="64px" viewBox="0 -960 960 960" width="64px" class="mx-auto" fill="#9CA3AF">
                                <path d="M240-120q-66 0-113-47T80-280q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm480 0q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm-480-80q33 0 56.5-23.5T320-280q0-33-23.5-56.5T240-360q-33 0-56.5 23.5T160-280q0 33 23.5 56.5T240-200Zm480 0q33 0 56.5-23.5T800-280q0-33-23.5-56.5T720-360q-33 0-56.5 23.5T640-280q0 33 23.5 56.5T720-200ZM480-520q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T560-680q0-33-23.5-56.5T480-760q-33 0-56.5 23.5T400-680q0 33 23.5 56.5T480-600Zm0-80Zm240 400Zm-480 0Z"/>
                            </svg>
                            <p class="mt-4 text-lg font-medium text-gray-900">No boards found</p>
                            <p class="mt-2 text-sm text-gray-600">Join a team to start collaborating!</p>
                        </div>
                    `;
                    boardContainer.appendChild(noBoardsMessage);
                }
            } else {
                console.error(
                    "Board element not found for board ID:",
                    e.board_id
                );
            }
        }
    });
});
