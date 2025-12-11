<?php
class VerifyModel
{
    public function verifyFace($file, $isFilePath = false)
    {
        // Dossier pour stocker l’image temporaire (uploadée)

        $uploadDir = __DIR__ . '/../Assets/Images/probe/';
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

        // Chemin vers Python - Auto-détection intelligente
        $pythonPath = $this->getPythonPath();
        $scriptPath = __DIR__ . '/../python/check_face.py';


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

    /**
     * Auto-détecte le chemin Python de manière intelligente
     * Vérifie dans l'ordre : variable d'environnement, PATH système, emplacements communs
     */
    private function getPythonPath()
    {
        // 1. Vérifier la variable d'environnement PYTHON_PATH
        $envPath = getenv('PYTHON_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        // 2. Essayer de trouver Python dans le PATH système
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows
            $output = shell_exec('where python 2>nul');
            if ($output) {
                $paths = explode("\n", trim($output));
                foreach ($paths as $path) {
                    $path = trim($path);
                    if (file_exists($path)) {
                        return $path;
                    }
                }
            }

            // 3. Emplacements communs Windows
            $commonPaths = [
                'C:\\Python313\\python.exe',
                'C:\\Python312\\python.exe',
                'C:\\Python311\\python.exe',
                'C:\\Python310\\python.exe',
                getenv('LOCALAPPDATA') . '\\Programs\\Python\\Python313\\python.exe',
                getenv('LOCALAPPDATA') . '\\Programs\\Python\\Python312\\python.exe',
                getenv('LOCALAPPDATA') . '\\Programs\\Python\\Python311\\python.exe',
            ];

            foreach ($commonPaths as $path) {
                if ($path && file_exists($path)) {
                    return $path;
                }
            }

            // Fallback Windows
            return 'python';
        } else {
            // Linux/Mac
            $output = shell_exec('which python3 2>/dev/null');
            if ($output) {
                $path = trim($output);
                if (file_exists($path)) {
                    return $path;
                }
            }

            // Fallback Unix
            return 'python3';
        }
    }
}
