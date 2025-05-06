// assets/js/clock.js
document.addEventListener('DOMContentLoaded', function() {
    function updatePST() {
        const pstElement = document.getElementById("philippine-time");
        if (!pstElement) return; // Guard clause if element doesn't exist
        
        try {
            const now = new Date();
            pstElement.innerText = now.toLocaleString("en-PH", {
                timeZone: "Asia/Manila",
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        } catch (error) {
            console.error("Error updating clock:", error);
        }
    }

    // Update immediately and then every second
    updatePST();
    setInterval(updatePST, 1000);
});
