document.addEventListener("DOMContentLoaded", () => {
    const columns = document.querySelectorAll(
        ".flex-1.bg-gray-100.rounded-lg.overflow-hidden"
    );

    // Selectors
    const taskSelector = ".kanban-column.p-4 li";

    // Add drag event listeners to columns
    columns.forEach((column) => {
        column.addEventListener("dragover", dragOver);
        column.addEventListener("dragenter", dragEnter);
        column.addEventListener("dragleave", dragLeave);
        column.addEventListener("drop", drop);
    });

    // Event delegation for tasks
    const tasks = document.querySelectorAll(taskSelector);
    tasks.forEach((task) => {
        task.addEventListener("dragstart", dragStart);
        task.addEventListener("dragend", dragEnd);
    });

    function dragStart(e) {
        e.dataTransfer.setData(
            "text/plain",
            e.target.closest(taskSelector).dataset.taskId
        );
        e.target.closest(taskSelector).classList.add("dragging");
    }

    function dragEnd(e) {
        e.target.closest(taskSelector).classList.remove("dragging");
    }

    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
    }

    function dragEnter(e) {
        e.preventDefault();
        const column = e.currentTarget;
        column.classList.add("drag-over");
    }

    function dragLeave(e) {
        const column = e.currentTarget;
        column.classList.remove("drag-over");
    }

    function drop(e) {
        e.preventDefault();
        const column = e.currentTarget;
        column.classList.remove("drag-over");

        const taskId = e.dataTransfer.getData("text/plain");
        const task = document.querySelector(`[data-task-id="${taskId}"]`);

        // Check if the task was found
        if (!task) {
            console.error("Task not found with ID:", taskId);
            return;
        }

        const targetColumn =
            column.querySelector(".kanban-column.p-4") || column;
        const sourceColumn = task.closest(".kanban-column.p-4");

        if (targetColumn !== sourceColumn) {
            const taskDate = task
                .closest(".mb-6")
                .querySelector("h5 span:last-child")
                .textContent.replace("Due: ", "");
            moveTask(task, targetColumn, taskDate);
            updateTaskStatus(taskId, targetColumn.dataset.column);

            removeEmptyDateGroups(sourceColumn);
            sortDateGroups(targetColumn);

            // Update task status indicators
            updateTaskStatusIndicators(task, targetColumn.dataset.column);
            updateTaskCount(sourceColumn);
            updateTaskCount(targetColumn);
        }

        // Update strikethrough class based on the target column
        if (targetColumn.dataset.column === "done") {
            task.classList.add("strikethrough");
        } else {
            task.classList.remove("strikethrough");
        }
    }

    function updateTaskStatusIndicators(task, newStatus) {
        const statusBar = task.querySelector(".w-full.h-1");
        const statusText = task.querySelector(".px-4.py-2");

        if (newStatus === "done") {
            if (statusBar) statusBar.remove();
            if (statusText) statusText.remove();
        } else {
            if (!statusBar && !statusText) {
                const placeholder = determineTaskStatus(task);
                const newStatusBar = document.createElement("div");
                newStatusBar.className = `w-full h-1 ${placeholder.barColor}`;
                const newStatusText = document.createElement("div");
                newStatusText.className = `px-4 py-2 ${placeholder.textColor} text-sm font-medium`;
                newStatusText.textContent = placeholder.status;
                task.appendChild(newStatusBar);
                task.appendChild(newStatusText);
            }
        }
    }

    function determineTaskStatus(task) {
        const dueDateText = task
            .closest(".mb-6")
            .querySelector("h5 span:last-child")
            .textContent.replace("Due: ", "");
        const dueDate = new Date(dueDateText);
        const now = new Date();

        const oneDay = 24 * 60 * 60 * 1000;

        // Overdue: due date is at least one day past
        if (dueDate <= now - oneDay) {
            return {
                status: "Overdue",
                barColor: "bg-red-500",
                textColor: "bg-red-50 text-red-700",
            };
        }
        // Due Today: the due date matches today's date
        else if (dueDate.toDateString() === now.toDateString()) {
            return {
                status: "Due Today",
                barColor: "bg-yellow-500",
                textColor: "bg-yellow-50 text-yellow-700",
            };
        }
        // Due Soon: due date is more than today and within 7 days
        else if (dueDate > now && dueDate - now <= 7 * oneDay) {
            return {
                status: "Due Soon",
                barColor: "bg-orange-500",
                textColor: "bg-orange-50 text-orange-700",
            };
        }
    }

    function moveTask(task, targetColumn, taskDate) {
        let dateGroup = findOrCreateDateGroup(targetColumn, taskDate);
        dateGroup.querySelector("ul").appendChild(task);
    }

    function removeEmptyDateGroups(column) {
        const dateGroups = column.querySelectorAll(".mb-6");
        dateGroups.forEach((group) => {
            if (group.querySelector("ul").children.length === 0) {
                group.remove();
            }
        });
    }

    function findOrCreateDateGroup(column, date) {
        let dateGroup = Array.from(column.children).find(
            (child) =>
                child.querySelector("h5 span:last-child")?.textContent ===
                `Due: ${date}`
        );

        if (!dateGroup) {
            dateGroup = createDateGroup(date);
            column.appendChild(dateGroup);
        }

        return dateGroup;
    }

    function createDateGroup(date) {
        const div = document.createElement("div");
        div.className = "mb-6";
        div.innerHTML = `
            <h5 class="flex items-center text-sm font-semibold text-gray-600 mb-3">
                <span class="material-symbols-outlined text-gray-400 text-lg mr-1">schedule</span>
                <span class="text-gray-600">Due: ${date}</span>
            </h5>
            <ul class="space-y-4"></ul>
        `;
        return div;
    }

    function updateTaskStatus(taskId, newStatus) {
        fetch(`/tasks/${taskId}/status`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ progress: newStatus }),
        })
            .then((response) => {
                if (!response.ok)
                    throw new Error("Network response was not ok");
                return response.json();
            })
            .then((data) => console.log("Task updated successfully:", data))
            .catch((error) => {
                console.error("Error:", error);
                alert("Error 403: This action is unauthorized.");
            });
    }

    function sortDateGroups(column) {
        const dateGroups = Array.from(column.querySelectorAll(".mb-6"));
        dateGroups.sort((a, b) => {
            const dateA = new Date(
                a
                    .querySelector("h5 span:last-child")
                    .textContent.replace("Due: ", "")
            );
            const dateB = new Date(
                b
                    .querySelector("h5 span:last-child")
                    .textContent.replace("Due: ", "")
            );
            return dateA - dateB;
        });

        dateGroups.forEach((group) => column.appendChild(group));
    }

    function updateTaskCount(column) {
        const taskCount = column.querySelectorAll("li").length;
        const countElement = column
            .closest(".flex-1")
            .querySelector(".task-count");
        if (countElement) {
            countElement.textContent = `(${taskCount})`;
        }
    }

    // Initial task count update
    columns.forEach(updateTaskCount);
});
