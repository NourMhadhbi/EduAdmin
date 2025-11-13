<?php
session_start();
require_once("../../Model/Presence.php");
require_once("../../Model/seance.php");

if (!isset($_SESSION['email']) || strtolower($_SESSION['role']) !== 'etudiant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$idEtudiant = $_SESSION['id'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';

$presencesSeances = Presence::getHistoriqueByEtudiant($idEtudiant);

// Toutes les séances planifiées pour l’étudiant
$allSeances = seance::findAllSeancesByEtudiant($idEtudiant);

date_default_timezone_set('Africa/Tunis');
$now = date('Y-m-d H:i:s');

// Construire l’historique par matière
$historique = [];
foreach ($allSeances as $s) {
    $presence = null;
    foreach ($presencesSeances as $p) {
        if ($p['seance_id'] == $s['id']) {
            $presence = $p;
            break;
        }
    }

    $seanceEnd = $s['date'] . ' ' . $s['heureFin'];

    if ($presence) {
        $statut = 'Présent';
    } elseif ($seanceEnd <= $now) {
        $statut = 'Absent';
    } else {
        $statut = 'Pas encore';
    }

    $historique[$s['nomCours']][] = [
        'dateSeance' => $s['date'],
        'heureDebut' => $s['heureDebut'],
        'heureFin' => $s['heureFin'],
        'nomProf' => $s['nomProf'],
        'statut' => $statut
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Historique des présences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #022153;
            margin-bottom: 1.5rem;
            border-bottom: 3px solid #022153;
            display: inline-block;
        }

        .presence-status {
            padding: 5px 10px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .present {
            background-color: rgba(6, 214, 160, 0.15);
            color: #06d6a0;
        }

        .absent {
            background-color: rgba(239, 71, 111, 0.15);
            color: #ef476f;
        }

        .pending {
            background-color: rgba(255, 209, 102, 0.15);
            color: #ffb703;
        }

        .table thead th {
            text-transform: uppercase;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <?php include("../NavBar/navbar.php") ?>

    <div class="container mt-4">
        <h2 class="page-title"><i class="fas fa-history me-2"></i>Historique des présences</h2>

        <?php if (empty($historique)) : ?>
            <p class="text-muted mt-4"><i class="fas fa-info-circle me-2"></i>Aucune séance planifiée.</p>
        <?php else : ?>
            <?php foreach ($historique as $matiere => $seances) : ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-book me-2"></i><?= htmlspecialchars($matiere) ?>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Enseignant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seances as $s) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['dateSeance']) ?></td>
                                        <td><?= htmlspecialchars($s['heureDebut']) ?> - <?= htmlspecialchars($s['heureFin']) ?></td>
                                        <td><?= htmlspecialchars($s['nomProf']) ?></td>
                                        <td>
                                            <?php
                                            $class = $s['statut'] === 'Présent' ? 'present' : ($s['statut'] === 'Absent' ? 'absent' : 'pending');
                                            ?>
                                            <span class="presence-status <?= $class ?>">
                                                <i class="fas <?= $class === 'present' ? 'fa-check' : ($class === 'absent' ? 'fa-times' : 'fa-clock') ?> me-1"></i>
                                                <?= htmlspecialchars($s['statut']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include("../../Footer/footer.php") ?>
</body>

</html>