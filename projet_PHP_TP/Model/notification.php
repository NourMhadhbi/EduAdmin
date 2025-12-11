<?php

class Notification
{
    private $id;
    private $utilisateur_id;
    private $titre;
    private $icone;
    private $message;
    private $date_creation;
    private $lu;

    public function __construct($utilisateur_id, $titre, $icone, $message, $date_creation = null, $lu = false)
    {
        $this->utilisateur_id = $utilisateur_id;
        $this->titre = $titre;
        $this->icone = $icone;
        $this->message = $message;
        $this->date_creation = $date_creation ?? date('Y-m-d H:i:s');
        $this->lu = $lu;
    }
    public function __get($attr)
    {
        return $this->$attr ?? null;
    }

    public function __set($attr, $value)
    {
        $this->$attr = $value;
    }



    public static function create($utilisateur_id, $titre, $icone, $message)
    {
        require(__DIR__ . "/../Connexion/connexion.php");

        $sql = "INSERT INTO notification (utilisateur_id, titre, icone, message, date_creation, lu)
                VALUES (:utilisateur_id, :titre, :icone, :message, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $utilisateur_id,
            ':titre' => $titre,
            ':icone' => $icone,
            ':message' => $message
        ]);
    }




    public static function findByUser($utilisateur_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $sql = "SELECT * FROM notification WHERE utilisateur_id = :id ORDER BY date_creation DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function markAllAsRead($utilisateur_id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $sql = "UPDATE notification SET lu = 1 WHERE utilisateur_id = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':id' => $utilisateur_id]);
    }
    /* Marquer une notification comme lue */
    public static function markAsRead($id)
    {
        include(__DIR__ . "/../Connexion/connexion.php");
        $sql = "UPDATE notification SET lu = 1 WHERE id = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }


    // public static function findByRole($pdo, $role)
    // {
    //     $sql = "SELECT n.* 
    //             FROM notification n
    //             INNER JOIN utilisateur u ON n.utilisateur_id = u.id
    //             WHERE u.role = :role
    //             ORDER BY n.date_creation DESC";
    //     $stmt = $pdo->prepare($sql);
    //     $stmt->execute([':role' => $role]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }
}
