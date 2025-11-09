<?php
session_start();
require_once __DIR__ . '/../Model/VerifyModel.php';
require_once __DIR__ . '/../Model/presence.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verifyModel = new VerifyModel();
    $presenceModel = new Presence(null, null, null, null);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = json_decode($verifyModel->verifyFace($_FILES['image']), true);
        error_log("Résultat complet de verifyFace : " . print_r($result, true));

        $etudiant_id = $_POST['etudiant_id'] ?? null;
        $seance_id = $_POST['seance_id'] ?? null;
        error_log("etudiant_id = $etudiant_id, seance_id = $seance_id");


        $imageCapture = $_FILES['image']['name'];

        if ($result['status'] === 'found') {

            $presenceModel->ajouterPresence($etudiant_id, $seance_id, 'Présent', $imageCapture);

            $_SESSION['snackbar'] = [
                'type' => 'success',
                'message' => 'Présence validée avec succès !'
            ];
        } elseif ($result['status'] === 'not_found') {
            $_SESSION['snackbar'] = [
                'type' => 'danger',
                'message' => 'Aucun visage correspondant trouvé'
            ];
        } else {
            $msg = $result['msg'] ?? 'Erreur inconnue';
            $_SESSION['snackbar'] = [
                'type' => 'warning',
                'message' => 'Erreur de reconnaissance : ' . $msg
            ];
            error_log("Erreur reconnaissance faciale : " . $msg);
        }
    } else {
        $_SESSION['snackbar'] = [
            'type' => 'danger',
            'message' => 'Aucune image reçue'
        ];
    }

    header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
    exit();
}
