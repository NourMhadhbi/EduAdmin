<?php
session_start();
$errorMessage = "";
$oldEmail = "";

if (isset($_SESSION["error"])) {
    $errorMessage = $_SESSION["error"];
    unset($_SESSION["error"]);
}

if (isset($_SESSION["old_email"])) {
    $oldEmail = $_SESSION["old_email"];
    unset($_SESSION["old_email"]);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Plateforme Présence Étudiants</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            background: url('../../Assets/edu.jpg') center/cover no-repeat;
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
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
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
                <i class="fas fa-key"></i>
                <h2>Mot de passe oublié</h2>
                <p class="mb-0">Réinitialisation de votre mot de passe</p>
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
                    <i class="fas fa-info-circle me-2"></i>
                    Entrez votre adresse email pour recevoir un code de vérification
                </div>

                <form id="forgotPasswordForm" action="../../Controller/UtilisateurController.php" method="POST">
                    <input type="hidden" name="action" value="send_reset_code">

                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" name="email" id="email"
                            placeholder="votre@email.com" required
                            value="<?= htmlspecialchars($oldEmail) ?>">
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer le code
                    </button>
                </form>

                <div class="login-footer">
                    <p>Retour à la <a href="../Authentification/connexion.php">connexion</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>