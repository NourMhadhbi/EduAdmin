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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Css/connexion.css">
    <style>
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

        .login-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
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
            /* background: linear-gradient(to right, #b6a794ff, #cfa876); */
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

        .form-check {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-check-label {
            margin-left: 8px;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Responsive adjustments */
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

            .error-alert {
                padding: 10px 12px;
                gap: 8px;
            }

            .error-alert i {
                font-size: 14px;
            }

            .error-alert span {
                font-size: 12px;
            }
        }

        /* Animation pour le chargement */
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
                        <!-- <input type="email" class="form-control" name="email" id="email" placeholder="votre@email.com" > -->
                        <input type="email" class="form-control" name="email" id="email"
                            placeholder="votre@email.com"
                            value="<?= htmlspecialchars($oldEmail) ?>">


                    </div>

                    <div class="mb-3 password-toggle">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="Votre mot de passe"
                            value="<?= htmlspecialchars($oldPassword) ?>">
                        <button type="button" class="toggle-icon" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>

                    <div class="form-check">
                        <div>
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                        </div>
                        <a href="reset-password.html" class="forgot-password">Mot de passe oublié?</a>
                    </div>

                    <button type="submit" class="btn btn-login">Se connecter</button>
                </form>

                <div class="login-footer">
                    <p>Vous n'avez pas de compte? <a href="../Authentification/inscription.php">Créer un compte</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../../Javascript/connexion.js"></script>
</body>

</html>