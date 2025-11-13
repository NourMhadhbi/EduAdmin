<?php
class VerifyModel
{
    public function verifyFace($file, $isFilePath = false)
    {
        // Dossier pour stocker l’image temporaire (uploadée)

        $uploadDir = __DIR__ . '/../../projet_PHP_TP/Assets/Images/probe/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        //     return json_encode(["status" => "error", "msg" => "Aucune image reçue."]);
        // }

        // $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        // $filename = uniqid('probe_') . '.' . $ext;
        // $path = $uploadDir . $filename;

        // if (!move_uploaded_file($file['tmp_name'], $path)) {
        //     return json_encode(["status" => "error", "msg" => "Erreur lors de l'upload."]);
        // }
        if ($isFilePath) {
            $path = $file;
        } else {
            if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                return json_encode(["status" => "error", "msg" => "Aucune image reçue."]);
            }
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('probe_') . '.' . $ext;
            $path = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $path)) {
                return json_encode(["status" => "error", "msg" => "Erreur lors de l'upload."]);
            }
        }

        // Chemin vers Python et script DeepFace
        $pythonPath = "C:\\Users\\pc\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
        $scriptPath = __DIR__ . '/../../projet_PHP_TP/python/check_face.py';


        $command = "\"$pythonPath\" \"$scriptPath\" \"$path\" 2>&1"; // redirige stderr vers stdout
        $output = shell_exec($command);
        file_put_contents('debug_log.txt', "Commande: $command\nOutput: $output\n", FILE_APPEND);

        if ($output === null || trim($output) === "") {
            return json_encode(["status" => "error", "msg" => "Aucun résultat reçu. Vérifiez Python."]);
        }

        // Extraire uniquement la ligne JSON (souvent la dernière)
        $lines = explode("\n", $output);
        $jsonLine = null;
        foreach (array_reverse($lines) as $line) {
            $line = trim($line);
            if (str_starts_with($line, "{")) {
                $jsonLine = $line;
                break;
            }
        }

        if ($jsonLine === null) {
            return json_encode(["status" => "error", "msg" => "Impossible d'extraire le JSON du script Python."]);
        }

        return $jsonLine;
    }
}
