<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .completed-task {
            text-decoration: line-through;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">To-Do List</h1>
        
        <!-- Add Task Form -->
        <form id="add-task-form" class="mb-4">
            <div class="input-group">
                <input type="text" id="new-task" class="form-control" placeholder="Enter new task" required>
                <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
        </form>

        <!-- Task List -->
        <ul id="task-list" class="list-group">
            <!-- Tasks will be dynamically added here -->
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('add-task-form');
            const taskInput = document.getElementById('new-task');
            const taskList = document.getElementById('task-list');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (taskInput.value.trim() !== '') {
                    addTask(taskInput.value);
                    taskInput.value = '';
                }
            });

            function addTask(taskName) {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <span>${taskName}</span>
                    <div>
                        <button class="btn btn-success btn-sm me-2 complete-btn">Complete</button>
                        <button class="btn btn-danger btn-sm delete-btn">Delete</button>
                    </div>
                `;
                taskList.appendChild(li);

                li.querySelector('.complete-btn').addEventListener('click', function() {
                    li.querySelector('span').classList.toggle('completed-task');
                });

                li.querySelector('.delete-btn').addEventListener('click', function() {
                    li.remove();
                });
            }

            // Add some sample tasks
            addTask('Complete Laravel backend');
            addTask('Design database schema');
            addTask('Implement user authentication');
        });
    </script>
</body>
</html>