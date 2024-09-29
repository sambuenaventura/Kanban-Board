document.addEventListener("DOMContentLoaded", () => {
    const todoModal = document.getElementById("todoModal");
    const collaboratorModal = document.getElementById("collaboratorModal");
    const openTodoBtn = document.getElementById("openModalBtn");
    const openCollaboratorBtn = document.getElementById(
        "openCollaboratorModalBtn"
    );
    const closeButtons = document.querySelectorAll(
        "#closeModalBtn, #closeModalBtn2, #closeCollaboratorModalBtn"
    );

    // Check if open buttons exist
    if (openTodoBtn && todoModal) {
        openTodoBtn.onclick = function () {
            todoModal.style.display = "block";
        };
    }

    if (openCollaboratorBtn && collaboratorModal) {
        openCollaboratorBtn.onclick = function () {
            collaboratorModal.style.display = "block";
        };
    }

    // Check if close buttons exist
    if (closeButtons.length > 0) {
        closeButtons.forEach((btn) => {
            btn.onclick = function () {
                if (todoModal) todoModal.style.display = "none";
                if (collaboratorModal) collaboratorModal.style.display = "none";
            };
        });
    }

    // Close modals when clicking outside of them
    window.onclick = function (event) {
        if (event.target === todoModal) {
            todoModal.style.display = "none";
        }
        if (event.target === collaboratorModal) {
            collaboratorModal.style.display = "none";
        }
    };
});
