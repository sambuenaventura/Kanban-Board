const modal = document.getElementById("todoModal");
const openBtn = document.getElementById("openModalBtn");
// const closeBtn = document.getElementById("closeModalBtn");
// const closeBtn2 = document.getElementById("closeModalBtn2");

const closeButtons = document.querySelectorAll(
    "#closeModalBtn, #closeModalBtn2"
);

openBtn.onclick = function () {
    modal.style.display = "block";
};

// closeBtn.onclick = function () {
//     modal.style.display = "none";
// };

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
