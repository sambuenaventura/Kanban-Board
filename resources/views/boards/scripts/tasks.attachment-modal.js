document.addEventListener("DOMContentLoaded", () => {
    const disableScroll = () => document.body.classList.add("no-scroll");
    const enableScroll = () => document.body.classList.remove("no-scroll");

    // Open modal
    document.querySelectorAll("img[data-modal-target]").forEach((img) => {
        img.addEventListener("click", function () {
            const targetId = this.getAttribute("data-modal-target");
            const modal = document.querySelector(targetId);
            if (modal) {
                modal.classList.remove("hidden");
                disableScroll();
            }
        });
    });

    // Close modal
    document.querySelectorAll(".close-modal").forEach((button) => {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-modal-target");
            const modal = document.querySelector(targetId);
            if (modal) {
                modal.classList.add("hidden");
                enableScroll();
            }
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", function (event) {
            if (event.target === this) {
                this.classList.add("hidden");
                enableScroll();
            }
        });
    });
});
