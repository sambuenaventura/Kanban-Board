document.addEventListener("DOMContentLoaded", () => {
    function updateDateTime() {
        const timePlaceholder = document.getElementById("timePlaceholder");
        const datePlaceholder = document.getElementById("datePlaceholder");

        // Check if placeholders exist
        if (!timePlaceholder || !datePlaceholder) return;

        const currentDateTime = new Date();
        const timeZone = "Asia/Manila";

        const timeOptions = {
            timeZone,
            hour12: true,
            hour: "numeric",
            minute: "numeric",
        };
        const formattedTime = currentDateTime.toLocaleTimeString(
            "en-US",
            timeOptions
        );
        timePlaceholder.innerText = formattedTime;

        const dateOptions = {
            timeZone,
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
        };
        const formattedDate = currentDateTime.toLocaleDateString(
            "en-US",
            dateOptions
        );
        datePlaceholder.innerText = formattedDate;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
});
