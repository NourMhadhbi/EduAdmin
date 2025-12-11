<?php
session_start();
include("../Model/seance.php");

class SeanceController {
    // Méthode pour traiter le formulaire d'ajout de séance
    public static function ajouterSeance() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cours_id = $_POST['cours_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $heureDebut = $_POST['heureDebut'] ?? null;
            $heureFin = $_POST['heureFin'] ?? null;

            if (!$cours_id || !$date || !$heureDebut || !$heureFin) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                return;
            }

            // Vérification du créneau
            $success = Seance::addSeance($cours_id, $date, $heureDebut, $heureFin);

            if ($success) {
                $_SESSION['success'] = "Séance ajoutée avec succès !";
            } else {
                $_SESSION['error'] = "Impossible d'ajouter la séance : le créneau est déjà occupé.";
            }

            // Redirection vers la page de gestion des cours
            header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
            exit;
        }
    }
    public static function modifierSeance() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $seance_id   = $_POST['seance_id'] ?? null;
        $cours_id    = $_POST['cours_id'] ?? null;
        $date        = $_POST['date'] ?? null;
        $heureDebut  = $_POST['heureDebut'] ?? null;
        $heureFin    = $_POST['heureFin'] ?? null;

        if (!$seance_id || !$cours_id || !$date || !$heureDebut || !$heureFin) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
            header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
            exit;
        }

        $success = Seance::updateSeance($seance_id, $cours_id, $date, $heureDebut, $heureFin);

        if ($success) {
            $_SESSION['success'] = "Séance modifiée avec succès !";
        } else {
            $_SESSION['error'] = "Impossible de modifier : créneau déjà réservé. $seance_id, $cours_id, $date, $heureDebut, $heureFin ";
        }

        header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
        exit;
    }
}
public static function supprimerSeance()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $seance_id = $_POST['seance_id'] ?? null;

        if (!$seance_id) {
            $_SESSION['error'] = "Séance introuvable.";
            header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
            exit;
        }

        $success = Seance::deleteSeance($seance_id);

        if ($success) {
            $_SESSION['success'] = "Séance supprimée avec succès !";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la séance.";
        }

        header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
        exit;
    }
}

}
$controller = new SeanceController();
$action = $_POST['action'] ?? null;

switch ($action) {
    case 'ajouterSeance':
        $controller->ajouterSeance();
        break;
    case 'modifierSeance':
        $controller->modifierSeance();
        break;
    case 'supprimerSeance':
        $controller->supprimerSeance();
        break;
    default:
        exit("Action non reconnue.");
    }
?>