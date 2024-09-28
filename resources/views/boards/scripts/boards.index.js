//Board Create Event
window.Echo.channel("boards").listen(".board.created", (e) => {
    console.log("Board created:", e);
});

//Board Remove Collaborator Event
document.addEventListener("DOMContentLoaded", () => {
    // const currentUserId = window.currentUserId;

    const boardContainer = document.getElementById("board-container");

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

                // After removing, check if there are any boards left
                const remainingBoards =
                    boardContainer.querySelectorAll("[data-board-id]");

                // If no boards are left, add the "No boards" message
                if (remainingBoards.length === 0) {
                    const noBoardsMessage = document.createElement("div");
                    noBoardsMessage.id = "no-boards-message";
                    noBoardsMessage.className =
                        "col-span-full text-center py-12 bg-white rounded-lg shadow-md border border-gray-200";
                    noBoardsMessage.innerHTML =
                        '<p class="text-gray-600 text-lg">No boards found. Join a team to start collaborating!</p>';
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
