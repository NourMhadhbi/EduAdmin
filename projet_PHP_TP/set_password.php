<?php
/**
 * Script de réinitialisation de mot de passe
 * ATTENTION: Uniquement pour développement/testing
 * NE PAS utiliser en production sans authentification appropriée
 */

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    die('Accès refusé. Authentification administrateur requise.');
}

// Vérifier la méthode POST pour éviter les accès directs
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Réinitialisation de mot de passe</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">⚠️ Réinitialisation de mot de passe</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <strong>Attention:</strong> Cet outil est destiné uniquement au développement.
                            </div>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email de l'utilisateur</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="text" class="form-control" id="password" name="password" value="Test123!" required>
                                </div>
                                <button type="submit" class="btn btn-danger">Réinitialiser</button>
                                <a href="Vue/Administrateur/DashboardAdmin.php" class="btn btn-secondary">Annuler</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Traitement du formulaire
try {
    $conn = new PDO('mysql:host=localhost;dbname=dbonlearn', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? 'Test123!';
    
    if (empty($email)) {
        die('Email requis');
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE utilisateur SET motDePasse = ? WHERE email = ?');
    $success = $stmt->execute([$hash, $email]);
    
    if ($success && $stmt->rowCount() > 0) {
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="alert alert-success">
                    ✅ Mot de passe réinitialisé avec succès pour: ' . htmlspecialchars($email) . '<br>
                    Nouveau mot de passe: ' . htmlspecialchars($password) . '
                </div>
                <a href="Vue/Administrateur/DashboardAdmin.php" class="btn btn-primary">Retour au tableau de bord</a>
            </div>
        </body>
        </html>';
    } else {
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="alert alert-danger">
                    ❌ Aucun utilisateur trouvé avec cet email
                </div>
                <a href="set_password.php" class="btn btn-secondary">Réessayer</a>
            </div>
        </body>
        </html>';
    }
} catch(PDOException $e) {
    die('Erreur de base de données: ' . $e->getMessage());
}
?>
