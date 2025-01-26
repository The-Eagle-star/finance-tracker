<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocket Donation Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .auth-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 450px;
        }
        .toggle-btn {
            background-color: transparent;
            border: none;
            color: #6c757d;
        }
        .toggle-btn.active {
            color: #007bff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Welcome</h2>
            <p class="text-muted">Login or Register to Continue</p>
        </div>
        
        <div class="mb-3 text-center">
            <button class="toggle-btn mx-2 active" id="loginToggle">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
            <button class="toggle-btn mx-2" id="registerToggle">
                <i class="bi bi-person-plus"></i> Register
            </button>
        </div>

        <div id="loginForm">
            <form action="auth.php" method="post">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </div>
            </form>
        </div>

        <div id="registerForm" class="d-none">
            <form action="auth.php" method="post">
                <input type="hidden" name="action" value="register">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-plus me-2"></i>Register
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginToggle').addEventListener('click', function() {
            document.getElementById('loginForm').classList.remove('d-none');
            document.getElementById('registerForm').classList.add('d-none');
            document.getElementById('loginToggle').classList.add('active');
            document.getElementById('registerToggle').classList.remove('active');
        });

        document.getElementById('registerToggle').addEventListener('click', function() {
            document.getElementById('loginForm').classList.add('d-none');
            document.getElementById('registerForm').classList.remove('d-none');
            document.getElementById('loginToggle').classList.remove('active');
            document.getElementById('registerToggle').classList.add('active');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
