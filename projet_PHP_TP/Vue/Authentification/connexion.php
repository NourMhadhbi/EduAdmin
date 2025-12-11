<?php
session_start();
$oldEmail = $_SESSION["old_email"] ?? "";
$oldPassword = $_SESSION["old_password"] ?? "";
unset($_SESSION["old_email"], $_SESSION["old_password"]);
$errorMessage = "";
if (isset($_SESSION["error"])) {
    $errorMessage = $_SESSION["error"];
    unset($_SESSION["error"]);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Plateforme Présence Étudiants</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Removed connexion.css to allow for new styling directly or from global-theme -->
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --accent-color: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('../../Assets/classroom_bg.jpg') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 20px;
            text-align: center;
            position: relative;
            border-bottom: none;
        }

        .login-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--accent-color);
        }

        .login-header i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
            color: var(--accent-color);
        }

        .login-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.6rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            max-width: 80%;
            margin: 0 auto;
        }

        .login-body {
            padding: 30px;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 0; /* Margin handled by mb-3 wrapper */
            border: 1px solid #ced4da;
            transition: all 0.3s;
            font-size: 1rem;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .input-group-text {
            background-color: transparent;
            border-left: none;
            cursor: pointer;
            padding: 12px;
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border: 1px solid #ced4da;
            border-left: 0;
        }
        
        .input-group .form-control {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            margin-top: 0.5rem;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
            cursor: pointer;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.35);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .login-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            margin-top: 25px;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .error-alert {
            background-color: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-close {
            margin-left: auto;
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-graduate"></i>
                <h2>Présence Étudiants</h2>
                <p class="mb-0">Plateforme de suivi de présence en ligne</p>
            </div>

            <div class="login-body">
                <?php if (!empty($errorMessage)) : ?>
                    <div class="error-alert" id="errorAlert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($errorMessage) ?></span>
                        <button class="error-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
                <form id="loginForm" action="../../Controller/UtilisateurController.php" method="POST">
                    <input type="hidden" name="action" value="connexion">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" name="email" id="email"
                            placeholder="votre@email.com"
                            value="<?= htmlspecialchars($oldEmail) ?>">
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Votre mot de passe" value="<?= htmlspecialchars($oldPassword) ?>">
                            <span class="input-group-text toggle-password" data-target="password">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-check d-flex justify-content-end mb-3">
                        <a href="../Authentification/motdepasse_oublie.php" class="forgot-password">Mot de passe oublié?</a>
                    </div>

                    <button type="submit" class="btn btn-login w-100">Se connecter</button>
                </form>

                <div class="login-footer">
                    <p>Vous n'avez pas de compte? <a href="../Authentification/inscription.php">Créer un compte</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle mot de passe
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', () => {
                const targetId = icon.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const eye = icon.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    eye.classList.remove('fa-eye');
                    eye.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    eye.classList.remove('fa-eye-slash');
                    eye.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>

</html>