<?php
session_start();

$errors = $_SESSION['error'] ?? [];
$success = $_SESSION['success'] ?? null;
$redirect = $_SESSION['redirect'] ?? null;
$old_inputs = $_SESSION['old_inputs'] ?? [];

// On nettoie les sessions après récupération
unset($_SESSION['error'], $_SESSION['success'], $_SESSION['redirect'], $_SESSION['old_inputs']);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inscription - Plateforme Présence Étudiants</title>
    <link rel="stylesheet" href="../../Css/global-theme.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <link rel="stylesheet" href="../../Css/inscription.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .input-group-text {
            height: 38px
        }


        .input-group-text-sm .toggle-password {
            padding: 0 0.5rem;
            border-radius: 0 .25rem .25rem 0;
        }

        .input-group-text-lg .toggle-password {
            padding: 0 1rem;
            border-radius: 0 .5rem .5rem 0;
        }

        .input-group-text .toggle-password i {
            font-size: 1rem;

        }




        .error-message {
            color: #e00000 !important;
            font-size: 0.85em;
            margin-top: -7px;
            margin-left: 2px;
            font-weight: 400;
            display: block;
        }

        input.is-invalid,
        select.is-invalid,
        textarea.is-invalid {
            border-color: #e00000 !important;
            box-shadow: none !important;
        }

        input.is-invalid:focus {
            border-color: #e00000 !important;
            box-shadow: 0 0 0 0.1rem rgba(224, 0, 0, 0.25) !important;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus text-white"></i>
                <h2>Créer un compte</h2>
                <p class="mb-0">Rejoignez notre plateforme</p>
            </div>

            <div class="register-body">
                <form id="registerForm" method="POST" action="../../Controller/utilisateurController.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="inscription">

                    <div class="role-selector mb-3">
                        <label class="form-label">Je suis :</label>
                        <div class="role-buttons">
                            <?php
                            $oldRole = $old_inputs['role'] ?? 'Etudiant';
                            $roles = ['Etudiant' => 'fa-user-graduate', 'Enseignant' => 'fa-chalkboard-teacher', 'Admin' => 'fa-user-shield'];
                            foreach ($roles as $roleName => $icon) :
                                $activeClass = ($oldRole === $roleName) ? 'active' : '';
                            ?>
                                <div class="role-btn <?= $activeClass ?>" data-role="<?= $roleName ?>">
                                    <i class="fas <?= $icon ?>"></i>
                                    <span><?= $roleName ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <input type="hidden" id="role" name="role" value="<?= htmlspecialchars($oldRole) ?>" />

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom"
                                    value="<?= htmlspecialchars($old_inputs['prenom'] ?? '') ?>" />
                                <div class="error-message" id="prenomError"><?= $errors['prenom'] ?? '' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom"
                                    value="<?= htmlspecialchars($old_inputs['nom'] ?? '') ?>" />
                                <div class="error-message" id="nomError"><?= $errors['nom'] ?? '' ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?= htmlspecialchars($old_inputs['email'] ?? '') ?>" />
                        <div class="error-message" id="emailError"><?= $errors['email'] ?? '' ?></div>
                    </div>

                    <div id="matriculeField" class="mb-3 <?= ($oldRole === 'Admin') ? 'd-none' : '' ?>">
                        <label for="matricule" class="form-label">Matricule</label>
                        <input type="text" class="form-control" id="matricule" name="matricule"
                            value="<?= htmlspecialchars($old_inputs['matricule'] ?? '') ?>" />
                        <div class="error-message" id="matriculeError"><?= $errors['matricule'] ?? '' ?></div>
                    </div>

                    <div id="specialiteField" class="mb-3 <?= ($oldRole === 'Enseignant') ? '' : 'd-none' ?>">
                        <label for="specialite" class="form-label">Spécialité</label>
                        <input type="text" class="form-control" id="specialite" name="specialite"
                            value="<?= htmlspecialchars($old_inputs['specialite'] ?? '') ?>" />
                        <div class="error-message" id="specialiteError"><?= $errors['specialite'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="photoProfil" class="form-label">Photo de profil</label>
                        <input type="file" class="form-control" id="photoProfil" name="photoProfil" accept="image/*" />
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="motDePasse" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="motDePasse" name="motDePasse" />
                            <span class="input-group-text toggle-password" data-target="motDePasse">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        <div class="error-message" id="motDePasseError"><?= $errors['motDePasse'] ?? '' ?></div>
                    </div>

                    <div class="mb-3 position-relative">
                        <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" />
                            <span class="input-group-text toggle-password" data-target="confirmPassword">
                                <i class="fa-solid fa-eye"></i>
                            </span>
                        </div>
                        <div class="error-message" id="confirmPasswordError"><?= $errors['confirmPassword'] ?? '' ?></div>
                    </div>

                    <button type="submit" class="btn btn-register w-100">S'inscrire</button>
                </form>

                <div class="register-footer mt-3">
                    <p>Vous avez déjà un compte ? <a href="index.html">Se connecter</a></p>
                </div>
            </div>

        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 
    <script>
        const roleButtons = document.querySelectorAll('.role-btn');
        const hiddenRoleInput = document.getElementById('role');
        const matriculeField = document.getElementById('matriculeField');
        const specialiteField = document.getElementById('specialiteField');

        // Gestion du choix du rôle
        roleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                roleButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const selectedRole = btn.getAttribute('data-role');
                hiddenRoleInput.value = selectedRole;

                if (selectedRole === 'Etudiant') {
                    matriculeField.classList.remove('d-none');
                    specialiteField.classList.add('d-none');
                } else if (selectedRole === 'Enseignant') {
                    matriculeField.classList.remove('d-none');
                    specialiteField.classList.remove('d-none');
                } else {
                    matriculeField.classList.add('d-none');
                    specialiteField.classList.add('d-none');
                }
            });
        });

        // Affichage messages PHP
        <?php if (isset($success) && $success): ?>
            Swal.fire({
                icon: 'success',
                title: '<?= addslashes($success) ?>',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.href = '<?= htmlspecialchars($redirect) ?>';
            });
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                html: '<?= implode("<br>", array_map("addslashes", $errors)) ?>'
            });
        <?php endif; ?>

        // Validation formulaire
        const form = document.getElementById('registerForm');
        const inputs = form.querySelectorAll('.form-control');
        const roleSelect = document.getElementById('role');

        function validateField(input) {
            const id = input.id;
            const value = input.value.trim();
            const role = roleSelect.value;
            const errorDiv = document.getElementById(id + "Error");
            let message = "";

            switch (id) {
                case "prenom":
                    if (!value) message = "Le prénom est obligatoire.";
                    break;
                case "nom":
                    if (!value) message = "Le nom est obligatoire.";
                    break;
                case "email":
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!value) message = "L'email est obligatoire.";
                    else if (!emailRegex.test(value)) message = "Adresse email invalide.";
                    break;
                case "matricule":
                    if ((role === "Etudiant" || role === "Enseignant") && !/^[0-9]{7}$/.test(value))
                        message = "Le matricule doit contenir 7 chiffres.";
                    break;
                case "specialite":
                    if (role === "Enseignant" && !value)
                        message = "La spécialité est obligatoire.";
                    break;
                case "motDePasse":
                    const passwordRegex = /^(?=.*[0-9])(?=.*[\W_]).{8,}$/;
                    if (!passwordRegex.test(value))
                        message = "Min. 8 caractères avec un chiffre et un symbole.";
                    break;
                case "confirmPassword":
                    const pass = document.getElementById('motDePasse').value;
                    if (value !== pass) message = "Les mots de passe ne correspondent pas.";
                    break;
            }

            if (message) {
                errorDiv.textContent = message;
                input.classList.add('is-invalid');
                return false;
            } else {
                errorDiv.textContent = "";
                input.classList.remove('is-invalid');
                return true;
            }
        }

     
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
        });

        
        form.addEventListener('submit', (e) => {
            let formValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) formValid = false;
            });

            if (!formValid) {
                e.preventDefault();
              
            }
        });

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
    </script> -->
    <script>
        const roleButtons = document.querySelectorAll('.role-btn');
        const hiddenRoleInput = document.getElementById('role');
        const matriculeField = document.getElementById('matriculeField');
        const specialiteField = document.getElementById('specialiteField');

        // Appliquer l'ancien rôle sélectionné au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const oldRole = '<?= $old_inputs['role'] ?? 'Etudiant' ?>';
            hiddenRoleInput.value = oldRole;
            roleButtons.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-role') === oldRole);
            });

            if (oldRole === 'Etudiant') {
                matriculeField.classList.remove('d-none');
                specialiteField.classList.add('d-none');
            } else if (oldRole === 'Enseignant') {
                matriculeField.classList.remove('d-none');
                specialiteField.classList.remove('d-none');
            } else {
                matriculeField.classList.add('d-none');
                specialiteField.classList.add('d-none');
            }
        });

        // Gestion du choix du rôle
        roleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                roleButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const selectedRole = btn.getAttribute('data-role');
                hiddenRoleInput.value = selectedRole;

                if (selectedRole === 'Etudiant') {
                    matriculeField.classList.remove('d-none');
                    specialiteField.classList.add('d-none');
                } else if (selectedRole === 'Enseignant') {
                    matriculeField.classList.remove('d-none');
                    specialiteField.classList.remove('d-none');
                } else {
                    matriculeField.classList.add('d-none');
                    specialiteField.classList.add('d-none');
                }
            });
        });

        // Validation formulaire
        const form = document.getElementById('registerForm');
        const inputs = form.querySelectorAll('.form-control');
        const roleSelect = document.getElementById('role');

        function validateField(input) {
            const id = input.id;
            const value = input.value.trim();
            const role = roleSelect.value;
            const errorDiv = document.getElementById(id + "Error");
            let message = "";

            switch (id) {
                case "prenom":
                    if (!value) message = "Le prénom est obligatoire.";
                    break;
                case "nom":
                    if (!value) message = "Le nom est obligatoire.";
                    break;
                case "email":
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!value) message = "L'email est obligatoire.";
                    else if (!emailRegex.test(value)) message = "Adresse email invalide.";
                    break;
                case "matricule":
                    if ((role === "Etudiant" || role === "Enseignant") && !/^[0-9]{7}$/.test(value))
                        message = "Le matricule doit contenir 7 chiffres.";
                    break;
                case "specialite":
                    if (role === "Enseignant" && !value)
                        message = "La spécialité est obligatoire.";
                    break;
                case "motDePasse":
                    const passwordRegex = /^(?=.*[0-9])(?=.*[\W_]).{8,}$/;
                    if (!passwordRegex.test(value))
                        message = "Min. 8 caractères avec un chiffre et un symbole.";
                    break;
                case "confirmPassword":
                    const pass = document.getElementById('motDePasse').value;
                    if (value !== pass) message = "Les mots de passe ne correspondent pas.";
                    break;
            }

            if (message) {
                errorDiv.textContent = message;
                input.classList.add('is-invalid');
                return false;
            } else {
                errorDiv.textContent = "";
                input.classList.remove('is-invalid');
                return true;
            }
        }

        // Validation au blur
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
        });

        // Validation stricte au submit : aucun champ invalide ne passe
        form.addEventListener('submit', (e) => {
            let formValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) formValid = false;
            });

            if (!formValid) {
                e.preventDefault(); // Empêche l'envoi si un champ est invalide
            }
        });

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