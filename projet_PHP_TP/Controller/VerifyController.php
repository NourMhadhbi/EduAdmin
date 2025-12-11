<?php
session_start();
require_once __DIR__ . '/../Model/VerifyModel.php';
require_once __DIR__ . '/../Model/Presence.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== VERIFY CONTROLLER START ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    $verifyModel = new VerifyModel();
    $presenceModel = new Presence(null, null, null, null);

    $etudiant_id = $_POST['etudiant_id'] ?? null;
    $seance_id = $_POST['seance_id'] ?? null;

    // Validate required fields
    if (!$etudiant_id || !$seance_id) {
        error_log("ERROR: Missing etudiant_id or seance_id");
        $_SESSION['snackbar'] = [
            'type' => 'danger',
            'message' => 'Erreur: Données manquantes (ID étudiant ou séance)'
        ];
        header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
        exit();
    }

    $imageCapture = null;
    $result = null;

    try {
        // Method 1: File upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing file upload...");
            $imageCapture = $_FILES['image']['name'];
            $file = $_FILES['image'];
            
            $resultJson = $verifyModel->verifyFace($file);
            error_log("VerifyFace result (file): " . $resultJson);
            $result = json_decode($resultJson, true);
            
            if ($result === null) {
                throw new Exception("Failed to parse JSON from verifyFace");
            }

        // Method 2: Camera capture (base64)
        } elseif (!empty($_POST['image'])) {
            error_log("Processing camera capture...");
            $base64Image = $_POST['image'];
            $data = explode(',', $base64Image);
            
            if (!isset($data[1])) {
                throw new Exception("Image mal formée - base64 data missing");
            }

            $imageData = base64_decode($data[1]);
            if ($imageData === false) {
                throw new Exception("Failed to decode base64 image");
            }

            $uploadDir = __DIR__ . '/../Assets/Images/probe/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = uniqid('probe_') . '.jpg';
            $filePath = $uploadDir . $filename;
            
            if (file_put_contents($filePath, $imageData) === false) {
                throw new Exception("Failed to save captured image");
            }

            $imageCapture = $filename;
            error_log("Saved camera image to: " . $filePath);

            $resultJson = $verifyModel->verifyFace($filePath, true);
            error_log("VerifyFace result (camera): " . $resultJson);
            $result = json_decode($resultJson, true);
            
            if ($result === null) {
                throw new Exception("Failed to parse JSON from verifyFace");
            }

        } else {
            throw new Exception("Aucune image reçue (ni fichier ni caméra)");
        }

        // Process result
        error_log("Recognition result status: " . ($result['status'] ?? 'UNKNOWN'));
        
        if ($result['status'] === 'found') {
            $added = $presenceModel->ajouterPresence($etudiant_id, $seance_id, 'Présent', $imageCapture);

            if ($added) {
                $_SESSION['snackbar'] = [
                    'type' => 'success',
                    'message' => '✅ Présence validée avec succès ! Votre visage a été reconnu.'
                ];
                error_log("SUCCESS: Presence added for student $etudiant_id");
            } else {
                $_SESSION['snackbar'] = [
                    'type' => 'warning',
                    'message' => 'Vous avez déjà enregistré votre présence pour cette séance.'
                ];
                error_log("WARNING: Duplicate presence attempt");
            }
            
        } elseif ($result['status'] === 'not_found') {
            $_SESSION['snackbar'] = [
                'type' => 'danger',
                'message' => '❌ Aucun visage correspondant trouvé. Assurez-vous d\'être bien enregistré dans le système.'
            ];
            error_log("NOT FOUND: No matching face");
            
        } else {
            $msg = $result['msg'] ?? $result['error'] ?? 'Erreur inconnue';
            $_SESSION['snackbar'] = [
                'type' => 'warning',
                'message' => 'Erreur de reconnaissance : ' . $msg
            ];
            error_log("ERROR in recognition: " . $msg);
        }

    } catch (Exception $e) {
        error_log("EXCEPTION in VerifyController: " . $e->getMessage());
        $_SESSION['snackbar'] = [
            'type' => 'danger',
            'message' => 'Erreur technique : ' . $e->getMessage()
        ];
    }

    error_log("=== VERIFY CONTROLLER END - Redirecting ===");
    header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
    exit();
    
} else {
    // Not a POST request
    error_log("ERROR: VerifyController called without POST");
    $_SESSION['snackbar'] = [
        'type' => 'danger',
        'message' => 'Méthode non autorisée'
    ];
    header('Location: ../Vue/Etudiants/ParticipationAuxCours.php');
    exit();
}
