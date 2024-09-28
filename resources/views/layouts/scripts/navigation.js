document.addEventListener("DOMContentLoaded", function () {
    // window.currentUserId = {{ auth()->id() }};
    if (currentUserId) {
        window.Echo.channel(`user.${currentUserId}`).listen(
            ".invitation.count.updated",
            (e) => {
                console.log("Invitation count updated:", e.invitation_count);
                updateInvitationBadge(e.invitation_count);
            }
        );
    }

    function updateInvitationBadge(invitationCount) {
        const invitationBadge = document.getElementById(
            "invitation-count-badge"
        );
        if (invitationBadge) {
            invitationBadge.innerText = invitationCount;
            invitationBadge.classList.toggle("hidden", invitationCount === 0);
        }
    }
});
