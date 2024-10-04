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
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p class="mt-2 text-sm font-medium text-gray-600">You have no pending board invitations.</p>
        `;
            invitationContainer.appendChild(noInvitationsMessage);
        }
    }
});
