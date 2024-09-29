document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("todoModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeButtons = document.querySelectorAll(
        "#closeModalBtn, #closeModalBtn2"
    );

    // Check if modal and open button exist
    if (modal && openBtn) {
        openBtn.onclick = function () {
            modal.style.display = "block";
        };

        // Check if close buttons exist
        if (closeButtons.length > 0) {
            closeButtons.forEach((btn) => {
                btn.onclick = function () {
                    modal.style.display = "none";
                };
            });
        }

        // Close modal when clicking outside of it
        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    }
});
