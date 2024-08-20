// Add the CSS class to disable scrolling
const disableScroll = () => document.body.classList.add("no-scroll");
const enableScroll = () => document.body.classList.remove("no-scroll");

// Open modal
document.querySelectorAll("img[data-modal-target]").forEach((img) => {
    img.addEventListener("click", function () {
        const targetId = this.getAttribute("data-modal-target");
        document.querySelector(targetId).classList.remove("hidden");
        disableScroll(); // Disable scrolling
    });
});

// Close modal
document.querySelectorAll(".close-modal").forEach((button) => {
    button.addEventListener("click", function () {
        const targetId = this.getAttribute("data-modal-target");
        document.querySelector(targetId).classList.add("hidden");
        enableScroll(); // Enable scrolling
    });
});

// Close modal when clicking outside the image
document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", function (event) {
        // Check if the click was on the modal background, not the inner content
        if (event.target === this) {
            this.classList.add("hidden");
            enableScroll(); // Enable scrolling
        }
    });
});
