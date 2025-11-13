<?php
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
