document.addEventListener("DOMContentLoaded", () => {
    const collaboratorModal = document.getElementById("collaboratorModal");
    const openCollaboratorBtn = document.getElementById(
        "openCollaboratorModalBtn"
    );
    const closeButtons = document.querySelectorAll(
        "#closeCollaboratorModalBtn"
    );

    if (openCollaboratorBtn && collaboratorModal) {
        openCollaboratorBtn.onclick = function () {
            collaboratorModal.style.display = "block";
        };
    }

    // Check if close buttons exist
    if (closeButtons.length > 0) {
        closeButtons.forEach((btn) => {
            btn.onclick = function () {
                if (collaboratorModal) collaboratorModal.style.display = "none";
            };
        });
    }

    window.onclick = function (event) {
        if (event.target === collaboratorModal) {
            collaboratorModal.style.display = "none";
        }
    };
});
