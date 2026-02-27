<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2>Welcome, {{ $user->name }}!</h2>
                    </div>
                    <div class="card-body">
                        <p class="lead">Thank you for registering at our platform.</p>
                        <p>We're excited to have you on board. If you have any questions, feel free to contact our support team.</p>
                        <a href="{{ url('/') }}" class="btn btn-success">Go to Dashboard</a>
                    </div>
                    <div class="card-footer text-muted text-center">
                        &copy; {{ date('Y') }} Our Platform. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
