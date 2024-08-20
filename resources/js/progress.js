// export function updateTaskProgress(checkbox) {
//     const taskId = checkbox.dataset.taskId;
//     const newProgress = checkbox.checked ? "done" : "to_do";

//     fetch(`/tasks/${taskId}/update-progress`, {
//         method: "POST",
//         headers: {
//             "Content-Type": "application/json",
//             "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
//                 .content,
//         },
//         body: JSON.stringify({ progress: newProgress }),
//     })
//         .then((response) => response.json())
//         .then((data) => {
//             if (data.success) {
//                 checkbox.dataset.taskProgress = newProgress;
//                 console.log(
//                     `Task ${taskId} progress updated to ${newProgress}`
//                 );
//             } else {
//                 console.error("Failed to update task progress");
//                 checkbox.checked = !checkbox.checked; // Revert the checkbox state
//             }
//         })
//         .catch((error) => {
//             console.error("Error:", error);
//             checkbox.checked = !checkbox.checked; // Revert the checkbox state
//         });
// }

// // Function to initialize the checkbox functionality
// export function initializeCheckbox() {
//     // Get all checkboxes
//     const checkboxes = document.querySelectorAll(
//         'input[type="checkbox"].form-checkbox'
//     );

//     checkboxes.forEach((checkbox) => {
//         // Get the associated text span
//         const taskId = checkbox.getAttribute("data-task-id");
//         const taskTextSpan = document.querySelector(
//             `span.task-text[data-task-id="${taskId}"]`
//         );

//         // Function to toggle the strike-through effect
//         function toggleStrikeThrough() {
//             if (checkbox.checked) {
//                 taskTextSpan.classList.add("completed");
//             } else {
//                 taskTextSpan.classList.remove("completed");
//             }
//         }

//         // Add an event listener to the checkbox
//         checkbox.addEventListener("change", toggleStrikeThrough);

//         // Initialize the text style based on the checkbox state on page load
//         toggleStrikeThrough();
//     });
// }

// // Initialize the checkbox functionality on page load
// document.addEventListener("DOMContentLoaded", initializeCheckbox);
