<?php
class Enseignant
{
    public static function ajouterCours($titre, $description, $enseignant_id)
    {
        include("../Connexion/connexion.php");

        try {
            $req = $conn->prepare("
                INSERT INTO cours (titre, description, enseignant_id)
                VALUES (:titre, :description, :enseignant_id)
            ");
            $req->bindParam(':titre', $titre);
            $req->bindParam(':description', $description);
            $req->bindParam(':enseignant_id', $enseignant_id);
            $req->execute();

            return true;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>
