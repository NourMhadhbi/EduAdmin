<?php

session_start();
require_once("../Model/inscriptionCours.php");

class inscriptionCoursController
{
    public function inscrire()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            die("Méthode non autorisée");
        }

        $etudiant_id = $_SESSION["id"];
        $cours_ids = json_decode($_POST["cours_ids"], true);

        if (!$etudiant_id || empty($cours_ids)) {
            die("Données manquantes");
        }

        $status = [
            "success" => [],
            "existe" => [],
            "error" => []
        ];

        foreach ($cours_ids as $cours_id) {

            $result = inscriptionCours::inscriptionCours($etudiant_id, $cours_id);

            if ($result === true) {
                $status["success"][] = $cours_id;
            } elseif ($result === "existe") {
                $status["existe"][] = $cours_id;
            } elseif (isset($result["error"])) {
                $status["error"][] = $result["error"];
            }
        }

        $_SESSION["status"] = $status;

        header("Location: ../Vue/Etudiants/inscritCours.php");
        exit();
    }
}

$ctrl = new inscriptionCoursController();
$ctrl->inscrire();
