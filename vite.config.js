import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig(({ command, mode }) => {
    const isDocker = process.env.DOCKER === "true";

    return {
        plugins: [
            laravel({
                input: [
                    "resources/css/app.css",
                    "resources/js/app.js",
                    "resources/js/update-time.js",
                    "resources/js/navigation.js",
                    "resources/js/welcome.js",
                    "resources/views/boards/scripts/boards.add-board-modal.js",
                    "resources/views/boards/scripts/boards.add-collaborator-modal.js",
                    "resources/views/boards/scripts/boards.add-task-modal.js",
                    "resources/views/boards/scripts/boards.delete-board-modal.js",
                    "resources/views/boards/scripts/boards.delete-dropdown.js",
                    "resources/views/boards/scripts/boards.drag-drop.js",
                    "resources/views/boards/scripts/boards.edit-board-modal.js",
                    "resources/views/boards/scripts/boards.fetch-delete-task.js",
                    "resources/views/boards/scripts/boards.index.js",
                    "resources/views/boards/scripts/boards.manage-invitations.js",
                    "resources/views/boards/scripts/boards.manage-invitations.js",
                    "resources/views/boards/scripts/boards.show.js",
                    "resources/views/boards/scripts/boards.tag-filter.js",
                    "resources/views/boards/scripts/tasks.attachment-modal.js",
                    "resources/views/boards/scripts/tasks.delete-attachment-modal.js",
                    "resources/views/boards/scripts/tasks.delete-task-modal.js",
                ],
                refresh: true,
            }),
        ],
        server: isDocker
            ? {
                  host: "0.0.0.0",
                  hmr: {
                      host: "localhost",
                  },
              }
            : {},
    };
});
