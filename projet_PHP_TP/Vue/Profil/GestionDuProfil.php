<?php session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Système de Présence Intelligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #2b2d42;
            --accent-color: #06d6a0;
            --accent-dark: #05c391;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
        }


        .user-profile-nav {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .user-profile-nav:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .user-profile-nav i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--primary-dark);
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        .page-title {
            font-weight: 800;
            color: var(--secondary-color);
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 15px;
            font-size: 2.2rem;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
            background: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 600;
            padding: 1.2rem 1.5rem;
            border: none;
            display: flex;
            align-items: center;
        }

        .card-header i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 1.8rem;
        }

        .profile-section {
            position: relative;
        }

        .profile-img-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .profile-img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            display: block;
            margin: 0 auto;
            transition: var(--transition);
        }

        .profile-img:hover {
            transform: scale(1.03);
        }

        .change-photo-btn {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            cursor: pointer;
            transition: var(--transition);
        }

        .change-photo-btn:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2f44c7 100%);
        }

        .photo-info {
            text-align: center;
            margin-top: 1rem;
        }

        .photo-info p {
            color: var(--gray-medium);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.2rem 1.5rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #0d47a1;
            border-left: 4px solid #2196f3;
        }

        .login-history {
            margin-top: 1.5rem;
        }

        .login-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .login-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .login-item:last-child {
            border-bottom: none;
        }

        .login-details {
            flex: 1;
        }

        .login-date {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .login-info {
            color: var(--gray-medium);
            font-size: 0.9rem;
        }

        .badge-online {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            box-shadow: 0 4px 8px rgba(6, 214, 160, 0.2);
        }

        /* Styles pour le système d'onglets */
        .tab-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .nav-tabs {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid var(--gray-light);
            padding: 0.5rem 1.5rem 0;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--gray-medium);
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
            margin-right: 0.5rem;
            transition: var(--transition);
            position: relative;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.05);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: white;
            border-bottom: 3px solid var(--primary-color);
        }

        .nav-tabs .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .tab-content {
            padding: 2rem;
        }

        .tab-pane {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-light);
            transition: var(--transition);
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            color: var(--gray-medium);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-medium);
            cursor: pointer;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .password-strength {
            height: 6px;
            margin-top: 8px;
            border-radius: 3px;
            transition: var(--transition);
        }

        .strength-weak {
            background: linear-gradient(to right, #ef476f, #f56a8a);
            width: 25%;
        }

        .strength-medium {
            background: linear-gradient(to right, #ffd166, #ffdc83);
            width: 50%;
        }

        .strength-strong {
            background: linear-gradient(to right, #06d6a0, #2ce6b0);
            width: 100%;
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-medium);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.8rem 1.8rem;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
            background-color: #e9ecef;
            border: none;
            margin-right: 0.75rem;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .privacy-settings .form-check-label {
            font-weight: 500;
            color: var(--secondary-color);
        }

        .section-title {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            position: relative;
            padding-bottom: 8px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }

            .profile-img {
                width: 150px;
                height: 150px;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .nav-tabs .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .tab-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Barre de navigation -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Contenu principal -->
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="accueil.html"><i class="fas fa-home me-1"></i>Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Mon profil</li>
                    </ol>
                </nav>

                <h1 class="page-title">Mon Profil</h1>
            </div>
        </div>

        <div class="row">
            <!-- Colonne de gauche -->
            <div class="col-lg-4">
                <!-- Photo de profil -->
                <div class="card profile-section">
                    <div class="card-header">
                        <i class="fas fa-id-card"></i> Photo de profil
                    </div>
                    <div class="card-body text-center">
                        <div class="profile-img-container">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80" alt="Photo de profil" class="profile-img" id="profileImage">
                            <div class="change-photo-btn" id="changePhotoBtn">
                                <i class="fas fa-camera"></i>
                            </div>
                            <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                        </div>
                        <div class="photo-info">
                            <p class="mb-3">
                                Cette photo est utilisée pour la reconnaissance faciale lors du marquage de présence.
                            </p>
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Conseil :</strong> Utilisez une photo récente, bien éclairée et avec un fond neutre.
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Colonne de droite -->
            <div class="col-lg-8">
                <!-- Système d'onglets -->
                <div class="tab-container">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
                                <i class="fas fa-user-edit"></i> Informations personnelles
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">
                                <i class="fas fa-lock"></i> Sécurité du compte
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab" aria-controls="privacy" aria-selected="false">
                                <i class="fas fa-shield-alt"></i> Confidentialité
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Onglet Informations personnelles -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <h3 class="section-title">Informations personnelles</h3>
                            <form id="profileForm">
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label for="firstName" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="firstName" value="Jean" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="lastName" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="lastName" value="Dupont" required>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label for="email" class="form-label">Adresse email</label>
                                        <input type="email" class="form-control" id="email" value="jean.dupont@etudiant.univ.fr" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" value="+33 6 12 34 56 78">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label for="studentId" class="form-label">Numéro étudiant</label>
                                        <input type="text" class="form-control" id="studentId" value="ETU12345" disabled>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="formation" class="form-label">Formation</label>
                                        <input type="text" class="form-control" id="formation" value="Master Informatique" disabled>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="promotion" class="form-label">Promotion</label>
                                        <input type="text" class="form-control" id="promotion" value="2022-2024" disabled>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="department" class="form-label">Département</label>
                                        <input type="text" class="form-control" id="department" value="Informatique" disabled>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Onglet Sécurité du compte -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <h3 class="section-title">Sécurité du compte</h3>
                            <form id="passwordForm">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="password-container">
                                            <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                            <input type="password" class="form-control" id="currentPassword" required>
                                            <button type="button" class="password-toggle" id="toggleCurrentPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="password-container">
                                            <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                            <input type="password" class="form-control" id="newPassword" required>
                                            <button type="button" class="password-toggle" id="toggleNewPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength strength-weak" id="passwordStrength"></div>
                                        <div class="password-requirements">
                                            Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="password-container">
                                            <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                                            <input type="password" class="form-control" id="confirmPassword" required>
                                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-2"></i> Modifier le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Onglet Confidentialité -->
                        <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
                            <h3 class="section-title">Paramètres de confidentialité</h3>
                            <div class="privacy-settings">
                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="notificationsSwitch" checked>
                                    <label class="form-check-label" for="notificationsSwitch">
                                        Recevoir les notifications par email
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="recognitionSwitch" checked>
                                    <label class="form-check-label" for="recognitionSwitch">
                                        Autoriser la reconnaissance faciale pour la présence
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-4">
                                    <input class="form-check-input" type="checkbox" id="dataUsageSwitch">
                                    <label class="form-check-label" for="dataUsageSwitch">
                                        Partager les données anonymisées pour améliorer le système
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="analyticsSwitch" checked>
                                    <label class="form-check-label" for="analyticsSwitch">
                                        Autoriser l'analyse d'utilisation pour améliorer l'expérience
                                    </label>
                                </div>
                                <div class="mt-4">
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-save me-2"></i> Enregistrer les préférences
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2023 Système de Présence Intelligente. Tous droits réservés.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Développement web dynamique - Projet académique</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion du changement de photo
        document.getElementById('changePhotoBtn').addEventListener('click', function() {
            document.getElementById('photoUpload').click();
        });

        document.getElementById('photoUpload').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;

                    // Animation de confirmation
                    const profileImg = document.getElementById('profileImage');
                    profileImg.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        profileImg.style.transform = 'scale(1)';
                    }, 300);
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Simulation de validation des formulaires
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Animation de succès
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> Modifications enregistrées!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');

            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }, 2000);
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Animation de succès
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> Mot de passe modifié!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');

            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }, 2000);
        });

        // Simulation de vérification de force du mot de passe
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');

            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                strengthBar.style.width = '0%';
            } else if (password.length < 6) {
                strengthBar.className = 'password-strength strength-weak';
                strengthBar.style.width = '25%';
            } else if (password.length < 10) {
                strengthBar.className = 'password-strength strength-medium';
                strengthBar.style.width = '50%';
            } else {
                strengthBar.className = 'password-strength strength-strong';
                strengthBar.style.width = '100%';
            }
        });

        // Fonctionnalité d'affichage/masquage du mot de passe
        function setupPasswordToggle(toggleId, inputId) {
            const toggleBtn = document.getElementById(toggleId);
            const passwordInput = document.getElementById(inputId);

            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Changer l'icône
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        // Initialiser les boutons d'affichage du mot de passe
        setupPasswordToggle('toggleCurrentPassword', 'currentPassword');
        setupPasswordToggle('toggleNewPassword', 'newPassword');
        setupPasswordToggle('toggleConfirmPassword', 'confirmPassword');
    </script>
</body>

</html>