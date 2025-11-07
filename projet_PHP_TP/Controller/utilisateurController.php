<?php
session_start();
require_once(__DIR__ . "/../Model/utilisateur.php");

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

            $uploadDir = __DIR__ . "/../Assets/Images/";
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
}

$controller = new UtilisateurController();
$action = $_POST['action'] ?? null;

if ($action === 'connexion') {
    $controller->connecter();
} elseif ($action === 'inscription') {
    $controller->traiterFormulaire();
} else {

    exit("Action non reconnue.");
}
