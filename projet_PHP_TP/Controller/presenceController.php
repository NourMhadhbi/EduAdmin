<?php
session_start();
require_once(__DIR__ . "/../Model/presence.php");
require_once(__DIR__ . "/../../vendor/autoload.php"); // Dompdf autoload
require_once(__DIR__ . "/../Model/cours.php");
require_once(__DIR__ . "/../Model/seance.php");
require_once(__DIR__ . "/../Model/inscriptionCours.php");
require_once(__DIR__ . "/../Model/utilisateur.php");

require(__DIR__ . "/../Model/Notification.php");

use Dompdf\Dompdf;
use Dompdf\Options;

class presenceController
{
    public function rendrePresent($etudiant_id, $seance_id)
    {
        return Presence::setPresent($etudiant_id, $seance_id);
    }

    public function rendreAbsent($etudiant_id, $seance_id)
    {
        return Presence::setAbsent($etudiant_id, $seance_id);
    }
    public function exporter($idCours, $idSeance = 0, $format = 'excel')
    {
        $enseignant_id = $_POST['id_enseignant'] ?? $_SESSION['id']; // id enseignant
        $cours = Cours::getCoursById($idCours);
        $etudiants = inscriptionCours::getEtudiantsByCour($idCours);
        $enseignant = Utilisateur::getEnseignantById($enseignant_id);
        $nomEnseignant = $enseignant['nom'];
        $prenomEnseignant = $enseignant['prenom'];
        // Récupération des séances
        if ($idSeance > 0) {
            // Séance unique
            $seancesCours = [seance::getSeanceById($idSeance)];
        } else {

            $seancesCours = seance::getSeancesByCoursEtEnseignant($idCours, $enseignant_id);
        }

        $dateExport = date('d/m/Y H:i');

        if ($format === 'excel') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=presence_{$cours['titre']}_{$dateExport}.xls");

            // En-têtes
            echo "Présences - {$cours['titre']}\tEnseignant: {$prenomEnseignant} {$nomEnseignant}\tDate d'export: {$dateExport}\n\n";


            // Table header
            echo "Étudiant\t";
            foreach ($seancesCours as $s) {
                echo date('d/m', strtotime($s['date'])) . "\t";
            }
            echo "Taux\n";

            // Contenu des étudiants
            foreach ($etudiants as $e) {
                echo "{$e['prenom']} {$e['nom']}\t";
                $nbPresentIndividu = 0;

                foreach ($seancesCours as $s) {
                    $statut = Presence::getStatutByEtudiantSeance($e['id'], $s['id']);
                    if (!in_array($statut, ['Présent', 'Absent'])) $statut = 'En attente';
                    if ($statut === 'Présent') $nbPresentIndividu++;
                    echo "{$statut}\t";
                }

                $taux = count($seancesCours) > 0 ? round(($nbPresentIndividu / count($seancesCours)) * 100) . '%' : '0%';
                echo "{$taux}\n";
            }
            exit;
        }

        if ($format === 'pdf') {
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);

            $html = '<h2>Présences - ' . htmlspecialchars($cours['titre']) . '</h2>';
            $html .= '<p>Enseignant : ' . htmlspecialchars($prenomEnseignant . ' ' . $nomEnseignant) . '</p>';
            $html .= '<p>Date d\'export: ' . $dateExport . '</p>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width:100%;">';
            $html .= '<thead><tr><th>Étudiant</th>';

            foreach ($seancesCours as $s) {
                $html .= '<th>' . date('d/m', strtotime($s['date'])) . '</th>';
            }
            $html .= '<th>Taux</th></tr></thead><tbody>';

            foreach ($etudiants as $e) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($e['prenom'] . ' ' . $e['nom']) . '</td>';

                $nbPresentIndividu = 0;
                foreach ($seancesCours as $s) {
                    $statut = Presence::getStatutByEtudiantSeance($e['id'], $s['id']);
                    if (!in_array($statut, ['Présent', 'Absent'])) $statut = 'En attente';
                    if ($statut === 'Présent') $nbPresentIndividu++;
                    $html .= '<td>' . htmlspecialchars($statut) . '</td>';
                }

                $taux = count($seancesCours) > 0 ? round(($nbPresentIndividu / count($seancesCours)) * 100) . '%' : '0%';
                $html .= '<td>' . $taux . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            $dompdf->loadHtml($html);

            // Portrait si <6 colonnes, paysage sinon
            $dompdf->setPaper('A4', count($seancesCours) > 5 ? 'landscape' : 'portrait');

            $dompdf->render();
            $dompdf->stream("presence_{$cours['titre']}_{$dateExport}.pdf", ["Attachment" => true]);
            exit;
        }
    }


    public function checkAndNotifyAbsences()
    {


        $year = date('Y');
        $absences = Presence::checkAbsences() ?? [];

        foreach ($absences as $absence) {
            $etudiant_id = $absence['etudiant_id'];
            $enseignant_id = $absence['enseignant_id'];
            $nb = (int)$absence['nbAbsence'];
            $cours = $absence['nomCours'];

            if ($nb == 2) {
                // Notification étudiant
                Notification::create(
                    $etudiant_id,
                    "Absences non justifiées",
                    "fa-triangle-exclamation",
                    "Vous avez 2 absences non justifiées dans le cours de {$cours}."
                );

                // Notification enseignant
                Notification::create(
                    $enseignant_id,
                    "Absences étudiant",
                    "fa-triangle-exclamation",
                    "L'étudiant {$absence['etudiant']} a 2 absences non justifiées dans le cours de {$cours}."
                );
            } elseif ($nb >= 3) {
                // Notification étudiant
                Notification::create(
                    $etudiant_id,
                    "Risque d'élimination",
                    "fa-triangle-exclamation",
                    "Vous avez {$nb} absences non justifiées dans le cours de {$cours}. Contactez votre enseignant."
                );

                // Notification enseignant
                Notification::create(
                    $enseignant_id,
                    "Absences critique",
                    "fa-triangle-exclamation",
                    "L'étudiant {$absence['etudiant']} a {$nb} absences non justifiées dans le cours de {$cours}."
                );
            }
        }
    }
}

$ctrl = new presenceController();

if (isset($_POST['save']) && isset($_POST['status'])) {
    foreach ($_POST['status'] as $etudiant_id => $seances) {
        foreach ($seances as $seance_id => $statut) {
            if ($statut === 'Présent') {
                $ctrl->rendrePresent($etudiant_id, $seance_id);
            } else {
                $ctrl->rendreAbsent($etudiant_id, $seance_id);
            }
        }
    }
    $ctrl->checkAndNotifyAbsences();
    $_SESSION['flash_message'] = 'Présences mises à jour avec succès';
    if (isset($_POST['save2'])) {
        $_SESSION['success'] = 'Présences mises à jour avec succès';
        header("Location: ../Vue/Enseignant/GestionDesCoursEns.php");
        exit();
    }

    header("Location: ../Vue/Enseignant/GestionDesPrésencesEns.php");
    exit();
}

if (!empty($_POST['exportFormat'])) {
    $format = $_POST['exportFormat'];
    $idCours = !empty($_POST['courseSelect']) ? (int)$_POST['courseSelect'] : 0;
    $idSeance = !empty($_POST['sessionSelect']) ? (int)$_POST['sessionSelect'] : 0;

    // Cas où aucun cours n'est sélectionné
    if ($idCours === 0) {
        $message = "Aucun cours sélectionné pour l'export.";
        if ($format === 'excel') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=presence_vide.xls");
            echo $message;
            exit;
        } elseif ($format === 'pdf') {


            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $html = "<h2>{$message}</h2>";
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("presence_vide.pdf", ["Attachment" => true]);
            exit;
        }
    }

    // Cas normal : cours sélectionné
    $ctrl->exporter($idCours, $idSeance, $format);
}
