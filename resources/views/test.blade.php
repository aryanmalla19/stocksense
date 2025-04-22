<!DOCTYPE html>
<html>

<head>
    <title>Laravel SSE Example</title>
</head>

<body>
    <h1>Live Notifications:</h1>
    <div id="output"></div>

    <script>
        const output = document.getElementById('output');
        const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2F1dGgvbG9naW4iLCJpYXQiOjE3NDUzNDI3MTYsImV4cCI6MTc0NTU1ODcxNiwibmJmIjoxNzQ1MzQyNzE3LCJqdGkiOiJpdVhzNEFITDhLY3k0VHpKIiwic3ViIjoiMTYiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3Iiwicm9sZSI6InVzZXIifQ.yyhbcQghOXbatbtGsQZNf1UdfeKQjo9KHnm81VwC_TM";
        
        // Pass token as a query parameter
        const eventSource = new EventSource(`{{ url('/api/v1/auth/sse-notifications') }}?token=${token}`);

        eventSource.onmessage = function (event) {
            const data = JSON.parse(event.data);
            output.innerHTML += `<p>${data.notification_id} - ${data.timestamp}</p>`;
        };

        eventSource.onerror = function (err) {
            console.error("SSE connection error:", err);
            eventSource.close();
        };
    </script>
</body>

</html>
