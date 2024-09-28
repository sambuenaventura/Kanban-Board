//Board Create Event
window.Echo.channel("boards").listen(".board.created", (e) => {
    console.log("Board created:", e);
});
