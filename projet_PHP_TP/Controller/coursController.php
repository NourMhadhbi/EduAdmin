<?php
session_start();
include(__DIR__ . "/../Model/presence.php");
include(__DIR__ . "/../Model/cours.php");
require_once(__DIR__ . "/../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;

$presences = Presence::getHistoriquePresence();
$coursList = Cours::getAllCours();

$selectedCourse = $_POST['selectedCourse'] ?? '';
$filteredPresences = [];

foreach ($presences as $presence) {
    if ($selectedCourse === '' || $presence['titre'] === $selectedCourse) {
        $filteredPresences[] = $presence;
    }
}

if (isset($_POST['generatePdf'])) {
    $html = '<h2 style="text-align:center;">Rapport de Présence</h2>';
    if ($selectedCourse !== '') {
        $html .= '<h4 style="text-align:center;">Cours : ' . htmlspecialchars($selectedCourse) . '</h4>';
    }

    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th>Étudiant</th>
                <th>Cours</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Heure</th>
            </tr>
        </thead>
        <tbody>';

    if (!empty($filteredPresences)) {
        foreach ($filteredPresences as $presence) {
            $html .= '<tr>
                <td>' . htmlspecialchars($presence['nomEtudiant']) . '</td>
                <td>' . htmlspecialchars($presence['titre']) . '</td>
                <td>' . htmlspecialchars(date('d/m/Y', strtotime($presence['date']))) . '</td>
                <td>' . htmlspecialchars($presence['statut']) . '</td>
                <td>' . htmlspecialchars($presence['heureDebut']) . ' - ' . htmlspecialchars($presence['heureFin']) . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="5" style="text-align:center;">Aucune donnée disponible.</td></tr>';
    }

    $html .= '</tbody></table>';


    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream("presence_cours.pdf", ["Attachment" => true]);
    exit;
}


class CoursController
{

    public function ajouterCours()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $titre = htmlspecialchars(trim($_POST["titre"]));
            $description = htmlspecialchars(trim($_POST["description"]));
            $level = htmlspecialchars(trim($_POST["level"]));
            $category = htmlspecialchars(trim($_POST["category"]));
            $enseignant_id = htmlspecialchars(trim($_POST["enseignant_id"]));
            if (empty($titre) || empty($enseignant_id)) {
                $_SESSION["error"] = "Veuillez remplir les champs obligatoires.";
                header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
                exit();
            }
            if (Cours::coursExiste($titre, $level)) {
                $_SESSION["error"] = "Un cours portant ce titre existe déjà.";
                if (strtolower($_SESSION['role']) === "enseignant") {
                    header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
                } else {
                    header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                }
                exit();
            }
            $uploadDir = __DIR__ . "/../Assets/Images/image";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $image = null;
            if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
                $image =  basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $uploadDir . $image);
            }

            $resultat = Cours::ajouterCours(
                $titre,
                $description,
                $level,
                $category,
                $image,
                $enseignant_id
            );
            if (empty($_POST['enseignant_id']) || !is_numeric($_POST['enseignant_id'])) {
                die("Erreur : enseignant invalide.");
            }
            if ($resultat === true) {
                $_SESSION["success"] = "Cours ajouté avec succès.";
            } else {
                $_SESSION["error"] = "Erreur : " . $resultat['error'];
            }

            if ($_SESSION['role'] === "enseignant") {
                header("Location: ../Vue/Enseignant/TableaudeBordEnseignant.php");
            } else {
                header("Location: ../Vue/Administrateur/suivi&controleAd.php");
            }
            exit();
        }
    }

    public static function supprimerCours()
    {
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

    public static function modifierCours()
    {
        session_start();
        $required_fields = ['cours_id', 'titre', 'level', 'category', 'enseignant_id'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Le champ " . $field . " est obligatoire";
                if (strtolower($_SESSION['role']) === "enseignant") {
                    header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
                } else {
                    header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                }
                exit;
            }
        }


        $id = $_POST['cours_id'];
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description'] ?? '');
        $level = $_POST['level'];
        $category = trim($_POST['category']);
        $enseignant_id = $_POST['enseignant_id'];
        $ancienne_image = $_POST['ancienne_image'] ?? '';
        if (Cours::coursExiste($titre, $level, $id)) {
            $_SESSION["error"] = "Un autre cours portant ce titre et ce niveau existe déjà.";
            if (strtolower($_SESSION['role']) === "enseignant") {
                header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
            } else {
                header("Location: ../Vue/Administrateur/suivi&controleAd.php");
            }
            exit();
        }
        $image = $ancienne_image;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . "/../Assets/Images/image/";
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);


            $file_name = basename($_FILES['image']['name']);
            $target_path = $upload_dir . $file_name;


            // Valider le type de fichier
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);


            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image = $file_name;
                    if (!empty($ancienne_image) && $ancienne_image !== 'default_course.jpg' && file_exists($upload_dir . $ancienne_image)) {
                        unlink($upload_dir . $ancienne_image);
                    }
                } else {
                    $_SESSION['error'] = "Erreur lors du téléchargement de l'image";
                    if (strtolower($_SESSION['role']) === "enseignant") {
                        header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
                    } else {
                        header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                    }
                    exit;
                }
            } else {
                $_SESSION['error'] = "Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF, WebP";
                if (strtolower($_SESSION['role']) === "enseignant") {
                    header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
                } else {
                    header("Location: ../Vue/Administrateur/suivi&controleAd.php");
                }
                exit;
            }
        }
        if (Cours::updateCours($id, $titre, $description, $level, $category, $image, $enseignant_id)) {
            $_SESSION['success'] = "Cours modifié avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du cours";
        }


        if (strtolower($_SESSION['role']) === "enseignant") {
            header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
        } else {
            header("Location: ../Vue/Administrateur/suivi&controleAd.php");
        }
        exit;
    }





    public static function getAllCours()
    {
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
