document.addEventListener("DOMContentLoaded", () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content"); // Get CSRF token from meta tag

    // Generate a random idempotency key
    function generateIdempotencyKey() {
        return Math.random().toString(36).substring(2, 15);
    }

    if (currentUserId) {
        window.Echo.channel(`user.${currentUserId}`)
            .listen(".invitation.details.sent", (e) => {
                console.log("New invitation received:", e.invitation);
                addInvitationToContainer(e.invitation);
            })
            .listen(".invitation.details.canceled", (e) => {
                console.log("Invitation canceled:", e.invitation_id);
                removeInvitationFromContainer(e.invitation_id);
            });
    }

    function addInvitationToContainer(invitation) {
        const invitationContainer = document.getElementById(
            "invitation-container"
        );

        // Create the invitation element
        const invitationElement = document.createElement("div");
        invitationElement.id = `invitation-${invitation.id}`;
        invitationElement.className =
            "flex flex-col sm:flex-row items-center justify-between p-6 bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300";
        invitationElement.innerHTML = `
        <div class="mb-4 sm:mb-0 text-center sm:text-left">
            <p class="font-bold text-xl text-gray-800 mb-1">${
                invitation.board.name
            }</p>
            <p class="text-sm text-gray-600 mb-1">Invited by: <span class="font-semibold">${
                invitation.inviter.name
            }</span></p>
            <p class="text-xs text-gray-500">Invited just now</p>
        </div>
        <div class="flex space-x-3">
            <form action="/boards/invitations/${
                invitation.id
            }/accept" method="POST">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                <button type="submit" class="px-6 py-2 bg-emerald-500 text-white rounded-full hover:bg-emerald-600 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-50 shadow-md hover:shadow-lg">
                    Accept
                </button>
            </form>
            <form action="/boards/invitations/${
                invitation.id
            }/decline" method="POST">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                <button type="submit" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 shadow-md hover:shadow-lg">
                    Decline
                </button>
            </form>
        </div>
        `;

        invitationContainer.appendChild(invitationElement);

        // Remove the "no invitations" message if present
        const noInvitationsMessage = document.getElementById(
            "no-invitations-message"
        );
        if (noInvitationsMessage) {
            noInvitationsMessage.remove();
        }
    }

    function removeInvitationFromContainer(invitationId) {
        const invitationElement = document.getElementById(
            `invitation-${invitationId}`
        );
        if (invitationElement) {
            invitationElement.remove();
        }

        // Show the "no invitations" message if there are no more invitations
        const invitationContainer = document.getElementById(
            "invitation-container"
        );
        if (invitationContainer.children.length === 0) {
            const noInvitationsMessage = document.createElement("div");
            noInvitationsMessage.id = "no-invitations-message";
            noInvitationsMessage.className =
                "p-8 bg-gray-100 rounded-lg text-center";
            noInvitationsMessage.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" height="64px" viewBox="0 -960 960 960" width="64px" fill="#9CA3AF" class="mx-auto">
                <path d="M680-80q-83 0-141.5-58.5T480-280q0-83 58.5-141.5T680-480q83 0 141.5 58.5T880-280q0 83-58.5 141.5T680-80Zm67-105 28-28-75-75v-112h-40v128l87 87Zm-547 65q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v250q-18-13-38-22t-42-16v-212h-80v120H280v-120h-80v560h212q7 22 16 42t22 38H200Zm280-640q17 0 28.5-11.5T520-800q0-17-11.5-28.5T480-840q-17 0-28.5 11.5T440-800q0 17 11.5 28.5T480-760Z"/>
            </svg>
            <p class="mt-4 text-lg font-medium text-gray-600">You have no pending board invitations.</p>
        `;
            invitationContainer.appendChild(noInvitationsMessage);
        }
    }
});
