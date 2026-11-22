/* ============================================================
   THE JOURNEY ARCHIVE — public contact-form demo
   Talks to api/contact.php: fetches a CSRF token, sends JSON,
   renders the response without ever using innerHTML.
   ============================================================ */

(function () {
    "use strict";

    const form = document.getElementById("demo-contact-form");
    if (!form) return;

    const status = document.getElementById("demo-contact-status");
    const submit = form.querySelector('[type="submit"]');
    const endpoint = "api/contact.php";

    let token = null;
    let startedAt = Math.floor(Date.now() / 1000);
    let sending = false;

    function setStatus(message, state) {
        if (!status) return;
        status.textContent = message;
        status.dataset.state = state || "";
    }

    async function refreshToken() {
        const response = await fetch(endpoint, { headers: { Accept: "application/json" } });
        if (!response.ok) throw new Error("The contact service is unavailable.");
        const data = await response.json();
        token = data.csrf_token;
        startedAt = data.server_time;
    }

    refreshToken().catch(function () {
        setStatus("The contact service is temporarily unavailable.", "error");
    });

    form.addEventListener("submit", async function (event) {
        event.preventDefault();
        if (sending) return;

        if (typeof form.checkValidity === "function" && !form.checkValidity()) {
            form.reportValidity();
            return;
        }

        sending = true;
        if (submit) submit.disabled = true;
        setStatus("Sending…", "sending");

        try {
            if (!token) await refreshToken();

            const payload = {
                _csrf: token,
                sender_name: form.elements.namedItem("sender_name").value.trim(),
                email: form.elements.namedItem("email").value.trim(),
                message: form.elements.namedItem("message").value.trim(),
                website: form.elements.namedItem("website").value,
                started_at: startedAt
            };

            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": token,
                    Accept: "application/json"
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.status === 419) {
                token = null;
                await refreshToken();
                setStatus("Your session expired. Please press Send again.", "error");
                return;
            }

            if (!response.ok || !result.ok) {
                const errors = result.errors && typeof result.errors === "object"
                    ? Object.values(result.errors)[0]
                    : "";
                setStatus(errors || result.message || "Please check the form and try again.", "error");
                return;
            }

            form.reset();
            token = null;
            setStatus(result.message || "Thank you — your message has been received.", "success");
        } catch (error) {
            setStatus("Something went wrong. Please try again in a moment.", "error");
        } finally {
            sending = false;
            if (submit) submit.disabled = false;
        }
    });
})();
