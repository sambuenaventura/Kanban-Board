const todoModal = document.getElementById("todoModal");
const collaboratorModal = document.getElementById("collaboratorModal");
const openTodoBtn = document.getElementById("openModalBtn");
const openCollaboratorBtn = document.getElementById("openCollaboratorModalBtn");
const closeButtons = document.querySelectorAll(
    "#closeModalBtn, #closeModalBtn2, #closeCollaboratorModalBtn"
);

openTodoBtn.onclick = function () {
    todoModal.style.display = "block";
};

openCollaboratorBtn.onclick = function () {
    collaboratorModal.style.display = "block";
};

closeButtons.forEach((btn) => {
    btn.onclick = function () {
        todoModal.style.display = "none";
        collaboratorModal.style.display = "none";
    };
});

window.onclick = function (event) {
    if (event.target == todoModal) {
        todoModal.style.display = "none";
    }
    if (event.target == collaboratorModal) {
        collaboratorModal.style.display = "none";
    }
};
