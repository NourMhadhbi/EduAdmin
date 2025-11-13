<?php
session_start();
include("../Model/admin.php");

class AdminController
{
    public function ajouterCours()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $titre = htmlspecialchars(trim($_POST["titre"]));
            $description = htmlspecialchars(trim($_POST["description"]));
            $enseignant_id = htmlspecialchars(trim($_POST["enseignant_id"]));

            if (empty($titre) || empty($enseignant_id)) {
                $_SESSION["error"] = "Veuillez remplir tous les champs obligatoires.";
                header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                exit();
            }

            $resultat = Admin::ajouterCours($titre, $description, $enseignant_id);

            if ($resultat === true) {
                $_SESSION["success"] = "Le cours a été ajouté avec succès.";
            } else {
                $_SESSION["error"] = "Erreur lors de l’ajout du cours : " . $resultat['error'];
            }

            header("Location: ../Vue/Administrateur/suivi&controleAd.php");
            exit();
        }
    }
}

// ✅ Point d’entrée
if (isset($_POST["action"]) && $_POST["action"] === "ajouterCours") {
    $controller = new AdminController();
    $controller->ajouterCours();
}
?>
