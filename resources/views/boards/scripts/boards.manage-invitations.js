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
            "bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden";
        invitationElement.innerHTML = `
            <div class="p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="mb-4 sm:mb-0 text-center sm:text-left">
                        <p class="font-bold text-2xl text-gray-800 mb-2">${
                            invitation.board.name
                        }</p>
                        <p class="text-sm text-gray-600 mb-1">Invited by: <span class="font-semibold">${
                            invitation.inviter.name
                        }</span></p>
                        <p class="text-xs text-gray-500">Invited just now</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <form action="/boards/invitations/${
                            invitation.id
                        }/accept" method="POST">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-colors duration-200">
                                Accept
                            </button>
                        </form>
                        <form action="/boards/invitations/${
                            invitation.id
                        }/decline" method="POST">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="idempotency_key" value="${generateIdempotencyKey()}">
                            <button type="submit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
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
            noInvitationsMessage.className =
                "p-8 bg-gray-100 rounded-lg text-center";
            noInvitationsMessage.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                </svg>
                <p class="mt-4 text-lg font-medium text-gray-600">You have no pending board invitations.</p>
            `;
            invitationContainer.appendChild(noInvitationsMessage);
        }
    }
});
