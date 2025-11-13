<?php
session_start();
include("../Model/cours.php");
class CoursController {
public function ajouterCours()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titre = htmlspecialchars(trim($_POST["titre"]));
            $description = htmlspecialchars(trim($_POST["description"]));
            $enseignant_id = htmlspecialchars(trim($_POST["enseignant_id"]));

            if (empty($titre) || empty($enseignant_id)) {
                $_SESSION["error"] = "Veuillez remplir tous les champs obligatoires.";
                header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
                exit();
            }

            $resultat = Cours::ajouterCours($titre, $description, $enseignant_id);

            if ($resultat === true) {
                $_SESSION["success"] = "Le cours a été ajouté avec succès.";
            } else {
                $_SESSION["error"] = "Erreur lors de l’ajout du cours : " . $resultat['error'];
            }
    if($_SESSION['role']==strtolower("enseignant")){
            header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
            exit();
        }
           if($_SESSION['role']==strtolower("admin")){
            header("Location: ../Vue/Administrateur/suivi&controleAd.php");
            exit();
        }
        }
    }
    
    public static function supprimerCours() {
        if ($_POST['action'] == 'supprimer_cours') {
            $cours_id = $_POST['cours_id'] ?? '';
            
            if (empty($cours_id)) {
                $_SESSION['error'] = "ID du cours manquant.";
                header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                exit;
            }
            
            // Récupérer les infos du cours avant suppression pour les messages
            $cours_info = Cours::getCoursById($cours_id);
            
            if ($cours_info) {
                // Vérifier s'il y a des séances associées
                $hasSeances = Cours::hasSeancesAssociees($cours_id);
                $nbEtudiants = Cours::countEtudiantsInscrits($cours_id);
                
                // Supprimer le cours
                $success = Cours::deleteCours($cours_id);
                
                if ($success) {
                    $message = "Cours '{$cours_info['titre']}' supprimé avec succès.";
                    if ($hasSeances) {
                        $message .= " Toutes les séances associées ont également été supprimées.";
                    }
                    if ($nbEtudiants > 0) {
                        $message .= " {$nbEtudiants} étudiant(s) ont été désinscrit(s).";
                    }
                    $_SESSION['success'] = $message;
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression du cours.";
                }
            } else {
                $_SESSION['error'] = "Cours non trouvé.";
            }
            
            header("Location: ../Vue/Administrateur/suivi&controleAd.php");
            exit;
        }
    }
    
    public static function modifierCours() {
        // À implémenter si besoin
    }
    
    public static function getAllCours() {
        return Cours::getAllCoursWithDetails();
    }
}

$controller = new CoursController();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'ajouterCours':
           $controller->ajouterCours();
            break;
        case 'supprimer_cours':
            $controller->supprimerCours();
            break;
        case 'modifierCours':
            $controller->modifierCours();
            break;
        default:
            $_SESSION['error'] = "Action non reconnue.";
            exit;
    }
}
?>