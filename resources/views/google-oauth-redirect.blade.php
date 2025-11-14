<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to Google...</title>
</head>
<body>
    <script>
        // Cookie should be set by the server response
        // Redirect to Google OAuth after a brief delay to ensure cookie is set
        setTimeout(function() {
            window.location.href = @json($redirectUrl);
        }, 100);
    </script>
    <p>Redirecting to Google...</p>
</body>
</html>

