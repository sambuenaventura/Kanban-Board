const modal = document.getElementById("todoModal");
const openBtn = document.getElementById("openModalBtn");

const closeButtons = document.querySelectorAll(
    "#closeModalBtn, #closeModalBtn2"
);

openBtn.onclick = function () {
    modal.style.display = "block";
};

closeButtons.forEach((btn) => {
    btn.onclick = function () {
        modal.style.display = "none";
    };
});

window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
