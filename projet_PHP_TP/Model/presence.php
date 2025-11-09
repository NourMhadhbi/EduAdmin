<?php
class Presence
{
    private $etudiant_id;
    private $seance_id;
    private $statut;
    private $imageCapture;

    public function __construct($etudiant_id, $seance_id, $statut, $imageCapture)
    {
        $this->etudiant_id = $etudiant_id;
        $this->seance_id = $seance_id;
        $this->statut = $statut;
        $this->imageCapture = $imageCapture;
    }


    public function ajouterPresence($etudiant_id, $seance_id, $statut, $imageCapture)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $sql = "INSERT INTO presence (etudiant_id, seance_id, statut, imageCapture) 
                VALUES (:etudiant_id, :seance_id, :statut, :imageCapture)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':etudiant_id' => $etudiant_id,
            ':seance_id' => $seance_id,
            ':statut' => $statut,
            ':imageCapture' => $imageCapture
        ]);
    }
}
