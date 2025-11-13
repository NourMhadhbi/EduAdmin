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
    <title>Code de vérification - Plateforme Présence Étudiants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles identiques à l'interface précédente */
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

        .code-inputs {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }

        .code-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .code-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            transform: scale(1.05);
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

            .code-input {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-shield-alt"></i>
                <h2>Code de vérification</h2>
                <p class="mb-0">Entrez le code reçu par email</p>
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
                    <i class="fas fa-envelope me-2"></i>
                    Nous avons envoyé un code à 6 chiffres à votre adresse email
                </div>

                <form id="verifyCodeForm" action="../../Controller/UtilisateurController.php" method="POST">
                    <input type="hidden" name="action" value="verify_reset_code">

                    <div class="code-inputs">
                        <input type="text" class="form-control code-input" name="code1" maxlength="1" required
                            onkeyup="moveToNext(this, 'code2')" pattern="[0-9]">
                        <input type="text" class="form-control code-input" name="code2" maxlength="1" required
                            onkeyup="moveToNext(this, 'code3')" pattern="[0-9]">
                        <input type="text" class="form-control code-input" name="code3" maxlength="1" required
                            onkeyup="moveToNext(this, 'code4')" pattern="[0-9]">
                        <input type="text" class="form-control code-input" name="code4" maxlength="1" required
                            onkeyup="moveToNext(this, 'code5')" pattern="[0-9]">
                        <input type="text" class="form-control code-input" name="code5" maxlength="1" required
                            onkeyup="moveToNext(this, 'code6')" pattern="[0-9]">
                        <input type="text" class="form-control code-input" name="code6" maxlength="1" required
                            onkeyup="moveToNext(this, 'verifyBtn')" pattern="[0-9]">
                    </div>

                    <button type="submit" class="btn btn-login" id="verifyBtn">
                        <i class="fas fa-check-circle me-2"></i>Vérifier le code
                    </button>
                </form>

                <div class="login-footer">
                    <p>Vous n'avez pas reçu le code? <a href="#" onclick="resendCode()">Renvoyer</a></p>
                    <p class="mt-2"><a href="../Authentification/connexion.php">Retour à la connexion</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function moveToNext(current, nextFieldName) {
            if (current.value.length >= current.maxLength) {
                const nextField = document.getElementsByName(nextFieldName)[0];
                if (nextField) {
                    nextField.focus();
                }
            }
        }

        function resendCode() {
            // Implémentation pour renvoyer le code
            alert('Code de vérification renvoyé!');
        }

        // Auto-focus sur le premier champ
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementsByName('code1')[0].focus();
        });
    </script>
</body>

</html>