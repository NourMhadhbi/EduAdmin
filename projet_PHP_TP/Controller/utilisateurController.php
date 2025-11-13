<?php
session_start();
require_once(__DIR__ . "/../Model/utilisateur.php");
include_once("configEmail.php");
class UtilisateurController
{
    public function traiterFormulaire()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nom = $_POST["nom"];
            $prenom = $_POST["prenom"];
            $email = $_POST["email"];
            $motDePasse = $_POST["motDePasse"];
            $confirmPassword = $_POST["confirmPassword"];
            $role = $_POST["role"];
            $matricule = $_POST["matricule"] ?? null;
            $specialite = $_POST["specialite"] ?? null;
            $errors = [];
            if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";
            if (empty($nom)) $errors[] = "Le nom est obligatoire.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email est invalide.";
            if (($role === "Etudiant" || $role === "Enseignant") && (!preg_match('/^[0-9]{7}$/', $matricule))) $errors[] = "Le matricule doit contenir 7 chiffres.";
            if ($role === "Enseignant" && empty($specialite)) $errors[] = "La spécialité est obligatoire pour les enseignants.";
            if (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{8,}$/', $motDePasse)) $errors[] = "Le mot de passe doit contenir au moins 8 caractères, un chiffre et un symbole.";
            if ($motDePasse !== $confirmPassword) $errors[] = "Les mots de passe ne correspondent pas.";

            if (!empty($errors)) {
                $_SESSION['error'] = $errors;
                $_SESSION['old_inputs'] = $_POST;
                header("Location: ../Vue/Authentification/inscription.php");
                exit;
            }

            $uploadDir = __DIR__ . "/../Assets/Images/known";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            switch (strtolower($role)) {
                case 'etudiant':
                    $photoProfil = "default_etudiant.png";
                    break;
                case 'enseignant':
                    $photoProfil = "default_enseignant.png";
                    break;
                case 'admin':
                    $photoProfil = "default_admin.png";
                    break;
                default:
                    $photoProfil = "default_etudiant.png";
                    break;
            }

            // Upload photo si présente
            if (isset($_FILES["photoProfil"]) && $_FILES["photoProfil"]["error"] === UPLOAD_ERR_OK) {
                // $fileName = uniqid() . "_" . basename($_FILES["photoProfil"]["name"]);
                $fileName = basename($_FILES["photoProfil"]["name"]);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES["photoProfil"]["tmp_name"], $targetPath)) {
                    $photoProfil = $fileName;
                }
            }

            $utilisateur = new Utilisateur($nom, $prenom, $email, $motDePasse, $role, $photoProfil);
            $result = $utilisateur->ajouterCompte($matricule, $specialite);

            if ($result && $result['success']) {
                $_SESSION['id'] = $result['id'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = strtolower($role);
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['photoProfil'] = $photoProfil;
                switch (strtolower($role)) {
                    case 'etudiant':
                        $redirectURL = "/projet_PHP_TP/Vue/Etudiants/InterfaceAccueil.php";
                        break;
                    case 'enseignant':
                        $redirectURL = "/projet_PHP_TP/Vue/Enseignant/TableaudeBordEnseignant.php";
                        break;
                    case 'admin':
                        $redirectURL = "/projet_PHP_TP/Vue/Administrateur/DashboardAdmin.php";
                        break;
                    default:
                        $redirectURL = "/projet_PHP_TP/Vue/dashboard.php";
                        break;
                }

                $_SESSION['success'] = "Compte créé avec succès !";
                $_SESSION['redirect'] = $redirectURL;
                header("Location: $redirectURL");
                exit;
            } else {

                $_SESSION['error'] = [$result];
                $_SESSION['old_inputs'] = $_POST;
                header("Location: ../Vue/Authentification/inscription.php");
                exit;
            }
        }
    }

    public function connecter()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $email = htmlspecialchars(trim($_POST["email"]));
            $motDePasse = htmlspecialchars(trim($_POST["password"]));

            if (empty($email) || empty($motDePasse)) {
                $_SESSION["error"] = "Veuillez remplir tous les champs.";
                $_SESSION["old_email"] = $email;
                $_SESSION["old_password"] = $motDePasse;
                header("Location: ../Vue/Authentification/connexion.php");
                exit();
            }

            $utilisateur = Utilisateur::Connection($email, $motDePasse);


            if (isset($utilisateur['error'])) {
                switch ($utilisateur['error']) {
                    case 'email_not_found':
                        $_SESSION["error"] = "Aucun compte n’est associé à cet email.";
                        break;

                    case 'wrong_password':
                        $_SESSION["error"] = "Mot de passe incorrect.";
                        break;

                    default:
                        $_SESSION["error"] = "Une erreur est survenue lors de la connexion.";
                        break;
                }


                $_SESSION["old_email"] = $email;
                $_SESSION["old_password"] = $motDePasse;

                header("Location: ../Vue/Authentification/connexion.php");
                exit();
            }
            $_SESSION["id"] = $utilisateur["id"];
            $_SESSION["email"] = $utilisateur["email"];
            $_SESSION["role"] = strtolower($utilisateur["role"]);
            $_SESSION['nom'] = $utilisateur["nom"];
            $_SESSION['prenom'] = $utilisateur["prenom"];
            $_SESSION['photoProfil'] = $utilisateur["photoProfil"];
            switch (strtolower($utilisateur["role"])) {
                case "admin":
                    header("Location: ../Vue/Administrateur/DashboardAdmin.php");
                    break;
                case "enseignant":
                    header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
                    break;
                default:
                    header("Location: ../Vue/Etudiants/InterfaceAccueil.php");
                    break;
            }
            exit();
        } else {
            exit();
        }
    }
    public function changerStatut()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            include(__DIR__ . "/../Connexion/connexion.php");
            require_once(__DIR__ . "/configEmail.php");

            $id = $_POST['id'] ?? null;
            $action = $_POST['statusAction'] ?? null;
            $reason = trim($_POST['reason'] ?? '');

            if (!$id || !in_array($action, ['activate', 'deactivate'])) {
                $_SESSION['snackbar'] = [
                    'type' => 'danger',
                    'message' => "Paramètres invalides pour changer le statut."
                ];
                header("Location: ../Vue/Administrateur/ListeUtilisateursAd.php");
                exit;
            }

            $user = Utilisateur::getById($id);

            if (!$user) {
                $_SESSION['snackbar'] = [
                    'type' => 'danger',
                    'message' => "Utilisateur introuvable."
                ];
                header("Location: ../Vue/Administrateur/ListeUtilisateursAd.php");
                exit;
            }

            if ($action === 'activate') {
                $status = 1;
                $emailSubject = "Activation de votre compte";
                $emailMessage = "
                Bonjour {$user['prenom']} {$user['nom']},<br><br>
                Votre compte sur la plateforme a été <strong>activé</strong> avec succès.<br>
                Vous pouvez désormais vous connecter à votre espace.<br><br>
                Cordialement,<br>L’équipe d’administration.
            ";
                $snackbarType = 'success';
                $snackbarMessage = " Le compte de {$user['prenom']} {$user['nom']} a été activé avec succès.";
            } else {
                if (empty($reason)) {
                    $_SESSION['snackbar'] = [
                        'type' => 'warning',
                        'message' => "Vous devez fournir une raison pour désactiver le compte."
                    ];
                    header("Location: ../Vue/Administrateur/ListeUtilisateursAd.php");
                    exit;
                }

                $status = 0;
                $emailSubject = "Désactivation de votre compte";
                $emailMessage = "
                Bonjour {$user['prenom']} {$user['nom']},<br><br>
                Votre compte a été <strong>désactivé</strong> par l’administrateur.<br>
                <strong>Raison :</strong> " . htmlspecialchars($reason) . "<br><br>
                Si vous pensez qu’il s’agit d’une erreur, veuillez contacter le support.<br><br>
                Cordialement,<br>L’équipe d’administration.
            ";
                $snackbarType = 'warning';
                $snackbarMessage = " Le compte de {$user['prenom']} {$user['nom']} a été désactivé.";
            }

            $updated = Utilisateur::updateIsActive($id, $status);

            if ($updated) {
                envoyerEmail($user['email'], $emailSubject, $emailMessage);

                $_SESSION['snackbar'] = [
                    'type' => $snackbarType,
                    'message' => $snackbarMessage
                ];
            } else {
                $_SESSION['snackbar'] = [
                    'type' => 'danger',
                    'message' => " Impossible de mettre à jour le statut."
                ];
            }

            header("Location: ../Vue/Administrateur/ListeUtilisateursAd.php");
            exit;
        }
    }
    public function envoyerCodeReset()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Adresse e-mail invalide.";
                $_SESSION['old_email'] = $email;
                header("Location: ../Vue/Authentification/motdepasse_oublie.php");
                exit;
            }

            $user = Utilisateur::getUserByEmail($email);
            if (!$user) {
                $_SESSION['error'] = "Aucun compte trouvé pour cet e-mail.";
                $_SESSION['old_email'] = $email;
                header("Location: ../Vue/Authentification/motdepasse_oublie.php");
                exit;
            }

            $code = random_int(100000, 999999);
            Utilisateur::saveResetCode($email, $code);
            $subject = "Code de réinitialisation de votre mot de passe";
            $message = "
                Bonjour {$user['prenom']} {$user['nom']},<br><br>
                Voici votre code de vérification : <strong>$code</strong><br>
                Ce code est valable pendant 10 minutes.<br><br>
                Cordialement,<br>L’équipe de support.
            ";

            envoyerEmail($email, $subject, $message);

            $_SESSION['reset_email'] = $email;


            header("Location: ../Vue/Authentification/verification_code.php");
            exit;
        }
    }

    public function verifierCodeReset()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_SESSION['reset_email'] ?? '';
            if (empty($email)) {
                $_SESSION['error'] = "Session expirée. Veuillez recommencer.";
                header("Location: ../Vue/Authentification/motdepasse_oublie.php");
                exit;
            }

            $code = implode('', [
                $_POST['code1'] ?? '',
                $_POST['code2'] ?? '',
                $_POST['code3'] ?? '',
                $_POST['code4'] ?? '',
                $_POST['code5'] ?? '',
                $_POST['code6'] ?? ''
            ]);

            if (Utilisateur::verifyResetCode($email, $code)) {
                $_SESSION['verified_reset'] = true;
                header("Location: ../Vue/Authentification/nouveau_motdepasse.php");
            } else {
                $_SESSION['error'] = "Code invalide ou expiré.";
                header("Location: ../Vue/Authentification/verification_code.php");
            }
            exit;
        }
    }

    public function reinitialiserMotDePasse()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_SESSION['reset_email'] ?? '';
            $verified = $_SESSION['verified_reset'] ?? false;
            $newPassword = $_POST['newPassword'] ?? '';
            $confirm = $_POST['confirmPassword'] ?? '';

            if (empty($email) || !$verified) {
                $_SESSION['error'] = "Accès non autorisé.";
                header("Location: ../Vue/Authentification/motdepasse_oublie.php");
                exit;
            }

            if ($newPassword !== $confirm) {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                header("Location: ../Vue/Authentification/nouveau_motdepasse.php");
                exit;
            }

            if (!preg_match('/^(?=.*[0-9])(?=.*[\W_]).{8,}$/', $newPassword)) {
                $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères, un chiffre et un symbole.";
                header("Location: ../Vue/Authentification/nouveau_motdepasse.php");
                exit;
            }

            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            Utilisateur::updateUserPassword($email, $hashed);
            unset($_SESSION['reset_email'], $_SESSION['verified_reset']);

            $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès.";
            header("Location: ../Vue/Authentification/connexion.php");
            exit;
        }
    }
}


$controller = new UtilisateurController();
$action = $_POST['action'] ?? null;

switch ($action) {
    case 'inscription':
        $controller->traiterFormulaire();
        break;
    case 'connexion':
        $controller->connecter();
        break;
    case 'changerStatut':
        $controller->changerStatut();
        break;
    case 'send_reset_code':
        $controller->envoyerCodeReset();
        break;
    case 'verify_reset_code':
        $controller->verifierCodeReset();
        break;
    case 'reset_password':
        $controller->reinitialiserMotDePasse();
        break;
    default:
        exit("Action non reconnue.");
}
