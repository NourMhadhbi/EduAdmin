<?php
require_once("../Connexion/connexion.php");

class Utilisateur
{
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $motDePasse;
    private $role;
    private $photoProfil;

    public function __construct($nom, $prenom, $email, $motDePasse, $role, $photoProfil = "default_profile.png")
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->role = $role;
        $this->photoProfil = $photoProfil;
    }

    // --- Getters et setters magiques ---
    public function __get($attr)
    {
        return $this->$attr ?? null;
    }

    public function __set($attr, $value)
    {
        $this->$attr = $value;
    }
    public static function Connection($email, $motDePass)
    {
        include("../Connexion/connexion.php");

        // Vérifier si l'email existe
        $req = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $req->bindParam(':email', $email);
        $req->execute();

        if ($req->rowCount() == 0) {
            // Aucun compte trouvé avec cet email
            return ['error' => 'email_not_found'];
        }

        // Si l'email existe, on vérifie le mot de passe
        $user = $req->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($motDePass, $user['motDePasse'])) {
            return ['error' => 'wrong_password'];
        }

        // Si tout est bon
        return $user;
    }

    public function ajouterCompte($matricule = null, $specialite = null)
    {
        include("../Connexion/connexion.php");
        try {
            $conn->beginTransaction();
            $checkEmail = $conn->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
            $checkEmail->execute([$this->email]);
            if ($checkEmail->fetchColumn() > 0) {
                throw new Exception("Cet email est déjà utilisé.");
            }
            if ($matricule) {
                $checkMatriculeEtudiant = $conn->prepare("SELECT COUNT(*) FROM etudiant WHERE matricule = ?");
                $checkMatriculeEtudiant->execute([$matricule]);
                $checkMatriculeEnseignant = $conn->prepare("SELECT COUNT(*) FROM enseignant WHERE matricule = ?");
                $checkMatriculeEnseignant->execute([$matricule]);

                if ($checkMatriculeEtudiant->fetchColumn() > 0 || $checkMatriculeEnseignant->fetchColumn() > 0) {
                    throw new Exception("Ce matricule existe déjà.");
                }
            }
            // Hachage du mot de passe
            $motDePasseHache = password_hash($this->motDePasse, PASSWORD_DEFAULT);
            // Insertion dans utilisateur
            $stmtUser = $conn->prepare("
                INSERT INTO utilisateur (nom, prenom, email, motDePasse, role, photoProfil)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtUser->execute([
                $this->nom,
                $this->prenom,
                $this->email,
                $motDePasseHache,
                $this->role,
                $this->photoProfil
            ]);

            $userId = $conn->lastInsertId();
            if ($this->role === 'Etudiant') {
                $stmtEtudiant = $conn->prepare("INSERT INTO etudiant (id, matricule) VALUES (?, ?)");
                $stmtEtudiant->execute([$userId, $matricule]);
            } elseif ($this->role === 'Enseignant') {
                $stmtEns = $conn->prepare("INSERT INTO enseignant (id, matricule, specialite) VALUES (?, ?, ?)");
                $stmtEns->execute([$userId, $matricule, $specialite]);
            }

            $conn->commit();
            return [
                'success' => true,
                'id' => $userId
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erreur lors de l'ajout du compte : " . $e->getMessage());
            return $e->getMessage();
        }
    }
}
