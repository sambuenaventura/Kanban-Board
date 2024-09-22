const disableScroll = () => document.body.classList.add("no-scroll");
const enableScroll = () => document.body.classList.remove("no-scroll");

// Open modal
document.querySelectorAll("img[data-modal-target]").forEach((img) => {
    img.addEventListener("click", function () {
        const targetId = this.getAttribute("data-modal-target");
        document.querySelector(targetId).classList.remove("hidden");
        disableScroll();
    });
});

// Close modal
document.querySelectorAll(".close-modal").forEach((button) => {
    button.addEventListener("click", function () {
        const targetId = this.getAttribute("data-modal-target");
        document.querySelector(targetId).classList.add("hidden");
        enableScroll();
    });
});

// Close modal when clicking outside the image
document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", function (event) {
        if (event.target === this) {
            this.classList.add("hidden");
            enableScroll();
        }
    });
});
