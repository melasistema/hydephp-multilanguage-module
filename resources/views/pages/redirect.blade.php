<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url={{ $destination }}">
    <title>Redirecting...</title>
</head>
<body>
@if($showText)
    <p>You are being redirected to <a href="{{ $destination }}">{{ $destination }}</a>.</p>
@endif
</body>
</html>
