document.addEventListener("DOMContentLoaded", (event) => {
    const tasks = document.querySelectorAll(".kanban-column.p-4 li");
    const columns = document.querySelectorAll(
        ".flex-1.bg-gray-100.rounded-lg.overflow-hidden"
    );

    tasks.forEach((task) => {
        task.addEventListener("dragstart", dragStart);
        task.addEventListener("dragend", dragEnd);
    });

    columns.forEach((column) => {
        column.addEventListener("dragover", dragOver);
        column.addEventListener("dragenter", dragEnter);
        column.addEventListener("dragleave", dragLeave);
        column.addEventListener("drop", drop);
    });

    function dragStart(e) {
        e.dataTransfer.setData("text/plain", e.target.dataset.taskId);
        this.classList.add("dragging");
    }

    function dragEnd() {
        this.classList.remove("dragging");
    }

    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
    }

    function dragEnter(e) {
        e.preventDefault();
        // Add drag-over class to the column
        const column = this.closest(
            ".flex-1.bg-gray-100.rounded-lg.overflow-hidden"
        );
        if (column) {
            column.classList.add("drag-over");
        }
    }

    function dragLeave(e) {
        e.preventDefault();
        // Check if the drag leave event is still within the column
        const column = this.closest(
            ".flex-1.bg-gray-100.rounded-lg.overflow-hidden"
        );
        if (column && !isWithinColumn(e, column)) {
            column.classList.remove("drag-over");
        }
    }

    function isWithinColumn(e, column) {
        const { clientX, clientY } = e;
        const rect = column.getBoundingClientRect();
        return (
            clientX >= rect.left &&
            clientX <= rect.right &&
            clientY >= rect.top &&
            clientY <= rect.bottom
        );
    }

    function drop(e) {
        e.preventDefault();
        const column = this.closest(
            ".flex-1.bg-gray-100.rounded-lg.overflow-hidden"
        );
        if (column) {
            column.classList.remove("drag-over");
        }
        const taskId = e.dataTransfer.getData("text");
        const task = document.querySelector(`[data-task-id="${taskId}"]`);
        const targetColumn = this.querySelector(".kanban-column.p-4") || this;
        const sourceColumn = task.closest(".kanban-column.p-4");

        if (targetColumn !== sourceColumn) {
            const taskDate = task
                .closest(".mb-4")
                .querySelector("h5").textContent;
            moveTask(task, targetColumn, taskDate);
            updateTaskStatus(taskId, targetColumn.dataset.column);

            // Check and remove empty date groups in the source column
            removeEmptyDateGroups(sourceColumn);

            if (targetColumn.dataset.column === "done") {
                task.querySelector(".text-gray-700").classList.add(
                    "strikethrough"
                );
            } else {
                task.querySelector(".text-gray-700").classList.remove(
                    "strikethrough"
                );
            }
        }
    }

    function moveTask(task, targetColumn, taskDate) {
        let dateGroup = findOrCreateDateGroup(targetColumn, taskDate);
        dateGroup.querySelector("ul").appendChild(task);
    }

    function removeEmptyDateGroups(column) {
        const dateGroups = column.querySelectorAll(".mb-4");
        dateGroups.forEach((group) => {
            if (group.querySelector("ul").children.length === 0) {
                group.remove();
            }
        });
    }

    function findOrCreateDateGroup(column, date) {
        let dateGroup = Array.from(column.children).find(
            (child) =>
                child.querySelector("h5") &&
                child.querySelector("h5").textContent === date
        );

        if (!dateGroup) {
            dateGroup = createDateGroup(date);
            column.appendChild(dateGroup);
        }

        return dateGroup;
    }

    function createDateGroup(date) {
        const div = document.createElement("div");
        div.className = "mb-4";
        div.innerHTML = `
            <h5 class="text-sm font-semibold text-gray-600 mb-2">${date}</h5>
            <ul class="space-y-3"></ul>
        `;
        return div;
    }

    function updateTaskStatus(taskId, newStatus) {
        fetch("/update-task-status", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                taskId: taskId,
                newStatus: newStatus,
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                console.log("Task updated successfully:", data);
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Failed to update task. Please try again.");
            });
    }
});
