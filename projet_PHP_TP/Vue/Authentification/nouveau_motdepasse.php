<?php
session_start();
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
    <title>Nouveau mot de passe - Plateforme Présence Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles identiques aux interfaces précédentes */
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(rgba(114, 114, 114, 0.4), rgba(152, 152, 152, 0.32)),
                url('../../Assets/edu.jpg') center/cover no-repeat;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }

        .login-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: linear-gradient(135deg, #6a7c96ff, #445876ff);
            color: white;
            padding: 15px 10px;
            text-align: center;
            position: relative;
        }

        .login-header i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .login-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 30px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #e1e5eb;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #6a7c96ff, #445876ff);
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            margin-top: 20px;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .login-footer a:hover {
            text-decoration: underline;
            color: #2980b9;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-icon {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            background: none;
            border: none;
            z-index: 10;
            padding: 5px;
        }

        .password-toggle .form-control {
            padding-right: 45px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            animation: fadeIn 0.5s ease-out;
        }

        .error-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: rgba(254, 226, 226, 0.95);
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
            animation: fadeIn 0.3s ease-out;
        }

        .error-alert i {
            color: #ef4444;
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .error-alert span {
            color: #b91c1c;
            font-weight: 500;
            font-size: 13px;
            line-height: 1.4;
            flex: 1;
        }

        .error-close {
            background: none;
            border: none;
            color: #dc2626;
            font-size: 12px;
            cursor: pointer;
            padding: 2px;
            border-radius: 3px;
            transition: all 0.2s ease;
            opacity: 0.6;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .error-close:hover {
            opacity: 1;
            background: rgba(220, 38, 38, 0.1);
        }

        .info-text {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .strength-weak {
            background-color: #e74c3c;
            width: 25%;
        }

        .strength-medium {
            background-color: #f39c12;
            width: 50%;
        }

        .strength-strong {
            background-color: #2ecc71;
            width: 100%;
        }

        @media (max-width: 576px) {
            .login-body {
                padding: 25px 20px;
            }

            .login-header {
                padding: 20px 15px;
            }

            .login-header i {
                font-size: 3rem;
            }

            body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-lock"></i>
                <h2>Nouveau mot de passe</h2>
                <p class="mb-0">Créez votre nouveau mot de passe</p>
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

                <div class="info-text">
                    <i class="fas fa-shield-alt me-2"></i>
                    Votre mot de passe doit contenir au moins 8 caractères
                </div>

                <form id="resetPasswordForm" action="../../Controller/UtilisateurController.php" method="POST">
                    <input type="hidden" name="action" value="reset_password">

                    <div class="mb-3 password-toggle">
                        <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="newPassword" id="newPassword"
                            placeholder="Votre nouveau mot de passe" required minlength="8">
                        <button type="button" class="toggle-icon" onclick="togglePassword('newPassword')">
                            <i class="far fa-eye"></i>
                        </button>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="mb-3 password-toggle">
                        <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" name="confirmPassword" id="confirmPassword"
                            placeholder="Confirmez votre mot de passe" required minlength="8">
                        <button type="button" class="toggle-icon" onclick="togglePassword('confirmPassword')">
                            <i class="far fa-eye"></i>
                        </button>
                        <div class="text-danger small mt-1" id="passwordMatchError"></div>
                    </div>

                    <button type="submit" class="btn btn-login" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Réinitialiser le mot de passe
                    </button>
                </form>

                <div class="login-footer">
                    <p>Retour à la <a href="../Authentification/connexion.php">connexion</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentNode.querySelector('.toggle-icon i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }


  

   
    </script>
</body>

</html>