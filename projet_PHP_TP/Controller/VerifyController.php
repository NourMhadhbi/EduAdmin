<?php
session_start();
require_once __DIR__ . '/../Model/VerifyModel.php';
require_once __DIR__ . '/../Model/Presence.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verifyModel = new VerifyModel();
    $presenceModel = new Presence(null, null, null, null);

    $etudiant_id = $_POST['etudiant_id'] ?? null;
    $seance_id = $_POST['seance_id'] ?? null;

    $imageCapture = null;

    // Méthode 1 : upload fichier 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageCapture = $_FILES['image']['name'];
        $file = $_FILES['image'];
        $result = json_decode($verifyModel->verifyFace($file), true);
        error_log("Résultat complet de verifyFace : " . print_r($result, true));

        // Méthode 2 : photo caméra en base64 
    } elseif (!empty($_POST['image'])) {
        $base64Image = $_POST['image'];
        $data = explode(',', $base64Image);
        if (!isset($data[1])) {
            $_SESSION['snackbar'] = [
                'type' => 'danger',
                'message' => 'Image mal formée'
            ];
            header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
            exit();
        }

        $imageData = base64_decode($data[1]);

        $uploadDir = __DIR__ . '/../../projet_PHP_TP/Assets/Images/probe/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = uniqid('probe_') . '.jpg';
        $filePath = $uploadDir . $filename;
        file_put_contents($filePath, $imageData);

        $imageCapture = $filename;

        // Ici, tu passes le chemin du fichier à verifyFace et signale que c’est un path
        $result = json_decode($verifyModel->verifyFace($filePath, true), true);
    } else {
        $_SESSION['snackbar'] = [
            'type' => 'danger',
            'message' => 'Aucune image reçue'
        ];
        header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
        exit();
    }

    if ($result['status'] === 'found') {
        $added = $presenceModel->ajouterPresence($etudiant_id, $seance_id, 'Présent', $imageCapture);

        if ($added) {
            $_SESSION['snackbar'] = [
                'type' => 'success',
                'message' => 'Présence validée avec succès !'
            ];
        } else {
            $_SESSION['snackbar'] = [
                'type' => 'warning', 
                'message' => 'Vous avez déjà enregistré votre présence pour cette séance.'
            ];
        }
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

    header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
    exit();
}
