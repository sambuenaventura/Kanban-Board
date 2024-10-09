document.addEventListener("DOMContentLoaded", () => {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

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

        const invitationElement = document.createElement("div");
        invitationElement.id = `invitation-${invitation.id}`;
        invitationElement.className =
            "flex items-center justify-between py-4 px-6 bg-white hover:bg-gray-50 border-b border-gray-200 last:border-b-0 rounded-lg";
        invitationElement.innerHTML = `
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-indigo-600 font-semibold text-sm">${invitation.board.name.substring(
                            0,
                            2
                        )}</span>
                    </div>
                </div>
                <div>
                    <p class="text-lg font-medium text-gray-900">
                        <span class="font-semibold text-indigo-600">${
                            invitation.inviter.name
                        }</span> invited you to join <span class="font-semibold">${
            invitation.board.name
        }</span>
                    </p>
                    <p class="text-xs text-gray-500">Invited just now</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <form action="/boards/invitations/${
                    invitation.id
                }/accept" method="POST">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                    <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Accept
                    </button>
                </form>
                <form action="/boards/invitations/${
                    invitation.id
                }/decline" method="POST">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                    <button type="submit" class="inline-flex items-center px-5 py-2 border border-gray-300 text-sm font-medium rounded-full shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Decline
                    </button>
                </form>
            </div>
        `;

        invitationContainer.appendChild(invitationElement);

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

        const invitationContainer = document.getElementById(
            "invitation-container"
        );
        if (invitationContainer.children.length === 0) {
            const noInvitationsMessage = document.createElement("div");
            noInvitationsMessage.id = "no-invitations-message";
            noInvitationsMessage.className = "col-span-full";
            noInvitationsMessage.innerHTML = `
                <div class="text-center py-12 bg-white rounded-lg shadow-md border border-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-900">No Pending Invitations</p>
                    <p class="mt-2 text-sm text-gray-600">Check back later for new invites.</p>
                </div>
            `;
            invitationContainer.appendChild(noInvitationsMessage);
        }
    }
});
