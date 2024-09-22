document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll(".task-progress-select");
    selects.forEach((select) => {
        adjustWidth(select);
        select.addEventListener("change", function () {
            const color =
                this.value === "to_do"
                    ? "#4F46E5"
                    : this.value === "in_progress"
                    ? "#D97706"
                    : "#16A34A";
            this.style.backgroundColor = color;
            adjustWidth(this);
        });
    });
});

function adjustWidth(select) {
    const selectedOption = select.options[select.selectedIndex];
    const tempSpan = document.createElement("span");
    tempSpan.className = select.className;
    tempSpan.style.visibility = "hidden";
    tempSpan.style.position = "absolute";
    tempSpan.style.whiteSpace = "nowrap";
    tempSpan.innerHTML = selectedOption.textContent;
    document.body.appendChild(tempSpan);
    const width = tempSpan.offsetWidth;
    document.body.removeChild(tempSpan);
    select.style.width = width + 5 + "px";
}
