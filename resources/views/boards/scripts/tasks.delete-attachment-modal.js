document.addEventListener("DOMContentLoaded", function () {
    const deleteAttachmentModal = document.getElementById(
        "deleteAttachmentModal"
    );
    const closeDeleteAttachmentModalBtn = document.getElementById(
        "closeDeleteAttachmentModalBtn"
    );
    const deleteAttachmentForm = document.getElementById(
        "deleteAttachmentForm"
    );
    const deleteAttachmentBtns = document.querySelectorAll(
        ".delete-attachment-btn"
    );

    // Check if the modal and close button exist before adding event listeners
    if (deleteAttachmentModal && closeDeleteAttachmentModalBtn) {
        // Attach event listener to all delete attachment buttons
        deleteAttachmentBtns.forEach((btn) => {
            btn.addEventListener("click", function () {
                const taskId = this.getAttribute("data-task-id");
                const attachmentId = this.getAttribute("data-attachment-id");

                deleteAttachmentForm.action = `/tasks/${taskId}/${attachmentId}/files`;
                deleteAttachmentModal.style.display = "block";
            });
        });

        closeDeleteAttachmentModalBtn.addEventListener("click", function () {
            deleteAttachmentModal.style.display = "none";
        });

        window.addEventListener("click", function (event) {
            if (event.target === deleteAttachmentModal) {
                deleteAttachmentModal.style.display = "none";
            }
        });
    }
});
