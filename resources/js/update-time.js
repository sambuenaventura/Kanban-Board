function updateDateTime() {
    const timePlaceholder = document.getElementById("timePlaceholder");
    const datePlaceholder = document.getElementById("datePlaceholder");
    const currentDateTime = new Date();

    // Set the time zone to Manila/Philippine time
    const timeZone = "Asia/Manila";

    // Options for formatting time
    //const timeOptions = { timeZone, hour12: true, hour: 'numeric', minute: 'numeric', second: 'numeric' };
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

    // Options for formatting date
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
