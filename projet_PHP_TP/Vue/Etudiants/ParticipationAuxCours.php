<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Pour afficher les erreurs fatales dans le navigateur
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "<b>Erreur:</b> [$errno] $errstr - $errfile:$errline<br>";
    return false; // permet le traitement normal par PHP
});
set_exception_handler(function ($exception) {
    echo "<b>Exception:</b> ", $exception->getMessage(), "<br>";
});
session_start();
include("../../Model/seance.php");
$snackbar = $_SESSION['snackbar'] ?? null;
unset($_SESSION['snackbar']);
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'etudiant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}
$idEtudiant = $_SESSION['id'] ?? '';
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';

$seances = seance::findSeanceByEtudiant($_SESSION['id'], date('Y-m-d'));
date_default_timezone_set('Africa/Tunis');
$heureActuelle = date('H:i:s');

// On cherche l'index de la séance en cours
$indexEnCours = null;
foreach ($seances as $index => $s) {
    if ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
        $indexEnCours = $index;
        break;
    }
}

$previewSeances = [];

// Cas 1 : une séance est en cours
if ($indexEnCours !== null) {
    // On prend la séance précédente, en cours et suivante (si disponibles)

    $indices = [
        $indexEnCours - 1,
        $indexEnCours,
        $indexEnCours + 1
    ];
    foreach ($indices as $i) {
        if (isset($seances[$i])) {
            $previewSeances[] = $seances[$i];
        }
    }
}
// Cas 2 : aucune séance en cours
else {
    // On cherche la prochaine séance à venir
    $indexProchaine = null;
    foreach ($seances as $index => $s) {
        if ($s['heureDebut'] > $heureActuelle) {
            $indexProchaine = $index;
            break;
        }
    }

    if ($indexProchaine !== null) {
        // On prend la séance précédente et deux suivantes si possibles
        $indices = [
            $indexProchaine - 1,
            $indexProchaine,
            $indexProchaine + 1
        ];
        foreach ($indices as $i) {
            if (isset($seances[$i])) {
                $previewSeances[] = $seances[$i];
            }
        }
    } else {
        // Si plus de séances aujourd’hui, on prend les 3 dernières de la journée
        $previewSeances = array_slice($seances, -3);
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marquer Présence - Système de Présence Intelligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #2b2d42;
            --accent-color: #06d6a0;
            --accent-dark: #05c391;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --border-radius: 12px;
            --box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark-color);
            line-height: 1.5;
            min-height: 100vh;
            font-size: 0.9rem;
        }


        .page-title {
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
            font-size: 1.8rem;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.2rem;
            background: white;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 600;
            padding: 1rem 1.2rem;
            border: none;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .card-header i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .card-body {
            padding: 1.2rem;
        }

        /* Section des cours planifiés */
        .courses-section {
            margin-bottom: 2rem;
        }

        .course-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .course-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            box-shadow: var(--box-shadow);
            border: 2px solid var(--gray-light);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }

        .course-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .course-card.active {
            border: 4px solid var(--primary-color);
            background: rgba(67, 97, 238, 0.03);
        }

        /*   .course-card.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-color);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }*/

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }

        .course-title {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }

        .course-time {
            color: var(--gray-medium);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .course-status {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-available {
            background: rgba(6, 214, 160, 0.12);
            color: #155724;
        }

        .status-ongoing {
            background: rgba(67, 97, 238, 0.12);
            color: var(--primary-color);
        }

        .status-upcoming {
            background: rgba(255, 209, 102, 0.12);
            color: #856404;
        }

        .status-finished {
            background: rgba(108, 117, 125, 0.12);
            color: var(--gray-medium);
        }

        .course-details {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .course-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-medium);
            font-size: 0.8rem;
        }

        .course-detail i {
            width: 14px;
            text-align: center;
            color: var(--primary-color);
        }

        .course-action {
            margin-top: 1rem;
            text-align: center;
        }

        /* Section de marquage de présence */
        .presence-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .presence-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .presence-title {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .time-remaining {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            color: white;
            border-radius: var(--border-radius);
            padding: 0.8rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .time-text {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .time-subtext {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        .presence-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .method-card {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 2px solid transparent;
            transition: var(--transition);
        }

        .method-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .method-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
        }

        .method-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .method-title {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .method-description {
            color: var(--gray-medium);
            font-size: 0.85rem;
        }

        .camera-preview {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-radius: 10px;
            margin: 0 auto 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--gray-medium);
            position: relative;
            overflow: hidden;
            border: 2px dashed var(--gray-medium);
        }

        .camera-preview.active {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        }

        .camera-preview i {
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
            color: var(--primary-color);
        }

        .camera-status {
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Correction pour la zone de téléchargement */
        .upload-container {
            margin-bottom: 1rem;
            position: relative;
        }

        .upload-area {
            border: 2px dashed var(--gray-medium);
            border-radius: 10px;
            padding: 1.5rem 1rem;
            text-align: center;
            background-color: var(--light-color);
            cursor: pointer;
            transition: var(--transition);
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }

        .upload-area:hover,
        .upload-area.active {
            border-color: var(--primary-color);
            background-color: #e8f4fc;
        }

        .upload-area i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .upload-text {
            font-weight: 500;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
            line-height: 1.3;
        }

        .upload-or {
            margin: 0.5rem 0;
            color: var(--gray-medium);
            font-size: 0.85rem;
        }

        .upload-info {
            color: var(--gray-medium);
            font-size: 0.75rem;
            margin-top: 0.8rem;
            line-height: 1.3;
        }

        /* Assurer que le texte ne déborde pas */
        .upload-area * {
            max-width: 100%;
            word-wrap: break-word;
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.35);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-dark) 100%);
            box-shadow: 0 4px 12px rgba(6, 214, 160, 0.25);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(6, 214, 160, 0.35);
        }

        .btn-outline-primary {
            border: 1.5px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        .consent-check {
            margin: 1.5rem 0;
        }

        .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--secondary-color);
        }

        /* Instructions */
        .instructions-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }

        .instructions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .instruction-card {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }

        .instruction-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow);
        }

        .instruction-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .instruction-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .instruction-description {
            color: var(--gray-medium);
            font-size: 0.8rem;
            line-height: 1.4;
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 1.2rem 1.5rem;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff9db 0%, #fff3bf 100%);
            color: #856404;
            border-left: 4px solid #ffd43b;
        }

        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
            font-size: 0.85rem;
        }

        .camera-preview video {
            width: 80px;
            /* même taille que l’icône */
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }



        .camera-status {
            font-size: 0.8rem;
            text-align: center;
            margin-top: 5px;
        }




        /* Responsive adjustments */
        @media (max-width: 768px) {
            .course-selection {
                grid-template-columns: 1fr;
            }

            .presence-methods {
                grid-template-columns: 1fr;
            }

            .presence-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .instructions-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .upload-area {
                padding: 1rem 0.8rem;
                min-height: 160px;
            }

            .upload-area i {
                font-size: 1.8rem;
            }

            .upload-text {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <?php include("../NavBar/navbar.php") ?>

    <!-- Contenu principal -->
    <div class="container py-3">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="accueil.html"><i class="fas fa-home me-1"></i>Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Marquer présence</li>
                    </ol>
                </nav>

                <h1 class="page-title">Participation aux Cours</h1>
            </div>
        </div>

        <!-- Section 1: Accéder aux cours planifiés -->
        <div class="courses-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i> Cours Planifiés - Aujourd'hui
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            Sélectionnez le cours auquel vous souhaitez marquer votre présence.
                            Seuls les cours en cours ou à venir sont disponibles.
                        </p>
                    </div>

                    <div class="course-selection">
                        <?php foreach ($previewSeances as $s): ?>
                            <?php
                            // Déterminer l’état de la séance
                            if ($s['heureFin'] < $heureActuelle) {
                                $etat = 'Terminé';
                                $icon = '<i class="fas fa-check me-1"></i>';
                                $classe = 'course-status status-finished';
                                $active = '';
                            } elseif ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
                                $etat = 'En cours';
                                $icon = '<i class="fas fa-play me-1"></i>';
                                $classe = 'course-status status-ongoing';
                                $active = 'active border border-primary shadow-sm';
                            } else {
                                $etat = 'À venir';
                                $icon = '<i class="fas fa-clock me-1"></i>';
                                $classe = 'course-status status-upcoming';
                                $active = '';
                            }
                            ?>

                            <div class="course-card <?= $active ?>" data-course-id="<?= htmlspecialchars($s['id']) ?>">
                                <div class="course-header">
                                    <div>
                                        <div class="course-title"><?= htmlspecialchars($s['titre']) ?></div>
                                        <div class="course-time">
                                            <i class="fas fa-clock"></i>
                                            <?= substr($s['heureDebut'], 0, 5) ?> - <?= substr($s['heureFin'], 0, 5) ?>
                                        </div>
                                    </div>

                                    <span class="<?= $classe ?>">
                                        <?= $icon . $etat ?>
                                    </span>
                                </div>

                                <div class="course-details">
                                    <div class="course-detail">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?>
                                    </div>
                                    <?php if (!empty($s['salle'])): ?>
                                        <div class="course-detail">
                                            <i class="fas fa-door-open"></i>
                                            <?= htmlspecialchars($s['salle']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Bloc d’action selon l’état -->
                                <?php if ($etat === 'Terminé'): ?>
                                    <div class="course-action">
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-check me-1"></i>Présence déjà marquée
                                        </button>
                                    </div>

                                <?php elseif ($etat === 'En cours'): ?>
                                    <div class="course-action">
                                        <span class="badge bg-primary">Cours sélectionné</span>
                                    </div>

                                <?php elseif ($etat === 'À venir'): ?>
                                    <div class="course-action">
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-clock me-1"></i>Indisponible
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Bouton Voir plus -->
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-primary rounded-pill px-4" id="voirPlusBtn">
                            <i class="fas fa-eye me-1"></i> Voir plus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal : toutes les séances du jour -->
        <div class="modal fade" id="panelSeances" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title"><i class="fas fa-calendar-day me-2"></i>Toutes les séances d'aujourd'hui</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <table class="table align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cours</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Professeur</th>
                                    <th>État</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seances as $s): ?>
                                    <?php
                                    if ($s['heureFin'] < $heureActuelle) {
                                        $etat = '<span class="badge bg-success">Terminé</span>';
                                    } elseif ($s['heureDebut'] <= $heureActuelle && $s['heureFin'] >= $heureActuelle) {
                                        $etat = '<span class="badge bg-warning text-dark">En cours</span>';
                                    } else {
                                        $etat = '<span class="badge bg-info">À venir</span>';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['titre']) ?></td>
                                        <td><?= substr($s['heureDebut'], 0, 5) ?></td>
                                        <td><?= substr($s['heureFin'], 0, 5) ?></td>
                                        <td><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></td>

                                        <td><?= $etat ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($indexEnCours) && isset($seances[$indexEnCours])): ?>
            <?php $seanceEnCours = $seances[$indexEnCours];
            if ($seanceEnCours): ?>
                <?php
                // Calcul du temps restant
                $fin = new DateTime($seanceEnCours['heureFin']);
                $now = new DateTime($heureActuelle);
                $diff = $now->diff($fin);
                $minutesRestantes = ($diff->h * 60) + $diff->i;
                ?>
                <!-- Section 2: Sélectionner le cours en cours -->
                <div class="presence-section">
                    <div class="presence-header">
                        <div class="presence-title">
                            <i class="fas fa-user-check"></i>
                            <div>
                                <div>Marquer ma présence</div>
                                <small class="text-muted">
                                    <?= htmlspecialchars($seanceEnCours['titre']) ?> - En cours
                                </small>
                            </div>
                        </div>
                        <div class="time-remaining">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="time-text">
                                    <?= $minutesRestantes ?> minute<?= $minutesRestantes > 1 ? 's' : '' ?> restantes
                                </div>
                                <div class="time-subtext">Pour marquer votre présence</div>
                            </div>
                        </div>
                    </div>


                    <form id="presenceForm" action="../../Controller/VerifyController.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="etudiant_id" value="<?= htmlspecialchars($idEtudiant) ?>">
                        <input type="hidden" name="seance_id" value="<?= htmlspecialchars($seanceEnCours['id']) ?>">
                        <input type="hidden" name="image" id="imageInput">
                        <div class="presence-methods">
                            <div class="method-card">
                                <div class="method-header">
                                    <div class="method-icon"> <i class="fas fa-camera"></i> </div>
                                    <div>
                                        <div class="method-title">Reconnaissance faciale</div>
                                        <div class="method-description">Prenez une photo en direct</div>
                                    </div>
                                </div>
                                <div class="camera-preview" id="cameraPreview"> <i class="fas fa-camera"></i>
                                    <!-- <div class="camera-status">Caméra inactive</div> <small>Cliquez sur "Activer la caméra"</small> -->
                                    <video id="video" width="320" height="240" autoplay></video>
                                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                                    <div class="camera-status" id="cameraStatus">Caméra inactive</div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="button" id="startCamera" class="btn btn-primary">
                                        <i class="fas fa-video me-2"></i>Activer la caméra
                                    </button>
                                    <button type="button" id="capturePhoto" class="btn btn-success" disabled>
                                        <i class="fas fa-camera me-2"></i>Prendre la photo
                                    </button>
                                    <button type="submit" id="submitPresence" class="btn btn-info" disabled>
                                        <i class="fas fa-check me-2"></i>Valider présence
                                    </button>
                                </div>

                            </div>
                    </form>


                    <form action="../../Controller/VerifyController.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="seance_id" value="<?= htmlspecialchars($seanceEnCours['id']) ?>">

                        <input type="hidden" name="etudiant_id" value="<?= htmlspecialchars($idEtudiant) ?>">

                        <div class="method-card">
                            <div class="method-header">
                                <div class="method-icon">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <div>
                                    <div class="method-title">Téléchargement d'image</div>
                                    <div class="method-description">Importez une photo existante</div>
                                </div>
                            </div>

                            <div class="upload-container">
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="upload-text">Glissez-déposez votre image ici</div>
                                    <p class="upload-or">ou</p>

                                    <!-- Bouton visible -->
                                    <button type="button" id="browseBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-folder-open me-2"></i> Parcourir les fichiers
                                    </button>

                                    <p class="upload-info">Formats supportés : JPG, PNG (max. 5MB)</p>

                                    <!-- Input caché relié au bouton -->
                                    <input type="file" name="image" id="fileInput" accept="image/png, image/jpeg" style="display: none;" required>
                                </div>
                            </div>

                            <div class="consent-check">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="consentCheck" name="consent" value="1" required>
                                    <label class="form-check-label" for="consentCheck">
                                        J'autorise l'utilisation de mon image pour la reconnaissance faciale
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-success" type="submit" id="validatePresence"
                                    <?= $minutesRestantes <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-check-circle me-2"></i> Valider ma présence
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
    </div>
<?php endif; ?>
<?php else: ?>
    <!-- Affichage pro si aucune séance en cours -->
    <div class="presence-section">
        <div class="presence-header">
            <div class="presence-title">
                <i class="fas fa-info-circle"></i>
                <div>
                    <div>Aucune séance en cours</div>
                    <small class="text-muted">
                        Il n'y a pas de cours disponible pour le marquage de présence actuellement.
                    </small>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Info :</strong> Veuillez revenir pendant l’horaire du cours pour marquer votre présence.
        </div>
    </div>
<?php endif; ?>
<!-- Section 3: Instructions détaillées -->
<div class="instructions-section">
    <h5 class="mb-3"><i class="fas fa-list-alt me-2"></i> Instructions de Participation</h5>

    <div class="instructions-grid">
        <div class="instruction-card">
            <div class="instruction-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="instruction-title">Accéder aux cours</div>
            <div class="instruction-description">
                Consultez la liste de vos cours planifiés dans la section "Cours Planifiés". Seuls les cours en cours sont disponibles pour le marquage.
            </div>
        </div>

        <div class="instruction-card">
            <div class="instruction-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="instruction-title">Sélectionner le cours</div>
            <div class="instruction-description">
                Cliquez sur le cours auquel vous souhaitez marquer votre présence. Le cours sélectionné sera mis en évidence.
            </div>
        </div>

        <div class="instruction-card">
            <div class="instruction-icon">
                <i class="fas fa-camera"></i>
            </div>
            <div class="instruction-title">Choisir la méthode</div>
            <div class="instruction-description">
                Sélectionnez entre la reconnaissance faciale en direct ou le téléchargement d'une image existante.
            </div>
        </div>

        <div class="instruction-card">
            <div class="instruction-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="instruction-title">Valider la présence</div>
            <div class="instruction-description">
                Après avoir pris ou uploadé votre photo, validez votre présence. Le système confirmera automatiquement.
            </div>
        </div>
    </div>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Important :</strong> Toute tentative de fraude ou d'usurpation d'identité sera sanctionnée selon le règlement intérieur de l'établissement. Votre présence ne sera validée qu'après reconnaissance faciale réussie.
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Conseils pour une bonne reconnaissance</h6>
            <ul class="small text-muted">
                <li>Assurez-vous d'être dans un endroit bien éclairé</li>
                <li>Positionnez votre visage face à la caméra</li>
                <li>Évitez les accessoires qui cachent votre visage</li>
                <li>Utilisez une photo récente pour le téléchargement</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-question-circle me-2 text-primary"></i>Problèmes fréquents</h6>
            <ul class="small text-muted">
                <li>Problème de caméra ? Vérifiez les permissions</li>
                <li>Reconnaissance échouée ? Réessayez avec meilleure luminosité</li>
                <li>Cours non disponible ? Vérifiez les horaires</li>
                <li>Besoin d'aide ? Contactez le support technique</li>
            </ul>
        </div>
    </div>
</div>
</div>

<!-- Pied de page -->
<?php include("../../Footer/footer.php") ?>

<!-- Snackbar -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="snackbarToast"
        class="toast align-items-center text-bg-<?= $snackbar['type'] ?? 'success' ?> border-0"
        role="alert" aria-live="assertive" aria-atomic="true"
        <?= $snackbar ? 'data-bs-autohide="true"' : '' ?>
        data-bs-delay="3500">
        <div class="d-flex">
            <div class="toast-body">
                <?= htmlspecialchars($snackbar['message'] ?? '') ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    </div>
</div>
<!-- Prendre photo-->
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('capturePhoto');
    const startBtn = document.getElementById('startCamera');
    const submitBtn = document.getElementById('submitPresence');
    const imageInput = document.getElementById('imageInput');
    const cameraStatus = document.getElementById('cameraStatus');

    let stream = null;

    // 1. Activer la caméra
    startBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: true
            });
            video.srcObject = stream;
            cameraStatus.textContent = "Caméra active";
            captureBtn.disabled = false; // activer bouton capture
        } catch (err) {
            console.error(err);
            cameraStatus.textContent = "Erreur caméra";
        }
    });

    // 2. Capturer la photo
    captureBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        const dataURL = canvas.toDataURL('image/jpeg');
        imageInput.value = dataURL; // stocke l'image pour l'envoyer
        cameraStatus.textContent = "Photo capturée !";
        submitBtn.disabled = false; // activer le bouton de validation
    });

    // Optionnel : arrêter la caméra quand le formulaire est soumis
    document.getElementById('presenceForm').addEventListener('submit', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toastEl = document.getElementById("snackbarToast");
        if (toastEl && "<?= $snackbar ? '1' : '' ?>") {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Gestion de la sélection des cours
    document.querySelectorAll('.course-card').forEach(card => {
        card.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            const status = this.querySelector('.course-status').textContent.trim();

            // Ne permettre la sélection que pour les cours disponibles
            if (status.includes('En cours') || status.includes('À venir')) {
                // Retirer la classe active de toutes les cartes
                document.querySelectorAll('.course-card').forEach(c => {
                    c.classList.remove('active');
                });

                // Ajouter la classe active à la carte cliquée
                this.classList.add('active');

                // Mettre à jour l'interface avec le cours sélectionné
                updateSelectedCourse(courseId);
            }
        });
    });
    // Quand on clique sur "Parcourir les fichiers", ça ouvre l'input caché
    document.getElementById("browseBtn").addEventListener("click", function() {
        document.getElementById("fileInput").click();
    });

    // (Optionnel) Affiche le nom du fichier sélectionné
    document.getElementById("fileInput").addEventListener("change", function() {
        if (this.files.length > 0) {
            document.querySelector(".upload-text").textContent = "Fichier sélectionné : " + this.files[0].name;
        }
    });
    document.getElementById('voirPlusBtn').addEventListener('click', () => {
        new bootstrap.Modal(document.getElementById('panelSeances')).show();
    });
</script>
</body>

</html>