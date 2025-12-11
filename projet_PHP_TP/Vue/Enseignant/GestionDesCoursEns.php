<?php
include("../../Model/cours.php");
include("../../Model/presence.php");
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'enseignant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';
$enseignantId = $_SESSION["id"];
$coursesF = Cours::getCoursByEnseignant($enseignantId);
$searchCourse  = $_POST['searchCourse']  ?? '';
$levelFilter   = $_POST['levelFilter']   ?? 'all';
$titreFilter   = $_POST['titreFilter']   ?? '';
$courses = Cours::getCoursFiltresByEnseignant($enseignantId, $searchCourse, $levelFilter, $titreFilter);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Cours - Espace Enseignant</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Keeping specific local styles that define course cards, but relying on global theme for layout */
        .course-card {
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            height: 100%;
            cursor: pointer;
            overflow: hidden;
            background: white;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .course-header {
            border-radius: 12px 12px 0 0;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .course-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
        }

        .course-body {
            padding: 25px;
        }

        .course-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-course {
            width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            border: 1px solid #eef2f7;
        }

        .session-item {
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
        }

        .session-item:hover {
            background-color: #f0f4f8;
            transform: translateX(5px);
        }

        .session-upcoming {
            border-left-color: var(--accent-color);
            background-color: #e8f6f3;
        }

        .session-completed {
            border-left-color: var(--success-color);
            background-color: #e8f5e9;
        }

        .session-cancelled {
            border-left-color: var(--danger-color);
            background-color: #fde8e8;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-color), var(--accent-color));
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .timeline-item.upcoming::before {
            background-color: var(--accent-color);
            box-shadow: 0 0 0 2px var(--accent-color);
        }

        .timeline-item.completed::before {
            background-color: var(--success-color);
            box-shadow: 0 0 0 2px var(--success-color);
        }

        /* Styles pour les modals */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 20px 25px;
            background-color: #fafbfc;
            border-radius: 12px 12px 0 0;
        }

        .modal-footer {
            border-top: 1px solid #eef2f7;
            padding: 20px 25px;
            background-color: #fafbfc;
            border-radius: 0 0 12px 12px;
        }

        .modal-body {
            padding: 25px;
        }

        .course-detail-modal .modal-dialog {
            max-width: 95%;
            width: 95%;
            height: 95vh;
        }

        .course-detail-modal .modal-content {
            height: 100%;
        }

        .course-detail-modal .modal-body {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .modal-course-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 25px;
            border-radius: 12px 12px 0 0;
        }

        /* Amélioration des formulaires */
        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }

        /* Boutons améliorés */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #2980b9);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            border: none;
        }

        /* Tableaux améliorés */
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #eef2f7;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: var(--secondary-color);
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges améliorés */
        .badge {
            border-radius: 6px;
            padding: 6px 10px;
            font-weight: 600;
        }

        /* Cartes améliorées */
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eef2f7;
            padding: 20px;
            font-weight: 600;
            color: var(--secondary-color);
            border-radius: 12px 12px 0 0 !important;
        }




        /* Stat cards améliorées */
        .stat-card {
            text-align: center;
            padding: 25px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary-color);
            display: block;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Présence status */
        .presence-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .presence-present {
            background-color: #d4f5e2;
            color: #27ae60;
        }

        .presence-absent {
            background-color: #fde8e8;
            color: #e74c3c;
        }

        .presence-late {
            background-color: #fef5e6;
            color: #f39c12;
        }

        /* Page header amélioré */
        .page-header {
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid #eef2f7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .course-stats {
                flex-direction: column;
                gap: 10px;
            }

            .stat-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .stat-value {
                font-size: 1.1rem;
            }
        }
        /** hedha ili zedou */
        .scroll-container {
    position: relative;
    padding: 10px 40px;
}

.scroll-btn {
    position: absolute;
    top: 45%;
    transform: translateY(-50%);
    z-index: 10;
    background: #ffffffdd;
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.scroll-btn:hover {
    background: #fff;
    transform: translateY(-50%) scale(1.1);
}

.left-btn { left: 0; }
.right-btn { right: 0; }

.course-list { gap: 20px; }
    .circle-image {
            width: 200px;
            height: 200px;
            display: none;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            border: 3px solid #e0e0e0;
        }
   .attendance-status {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin: 0 auto;
        }
        
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php include("../NavBar/navbar.php") ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="TableaudeBordEnseignant.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Gestion des Cours</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title mb-0">Gestion des cours</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fas fa-plus me-2"></i> Nouveau cours
                    </button>
                </div>
            </div>
        </div>

             <!-- Filtres -->
                <form method="POST" id="courseFilterForm">
                    <div class="filter-section">
                        <div class="row">


                            <!-- Filtre par titre -->
                            <div class="col-md-4">
                                <label for="courseFilter" class="form-label">Filtrer par matière</label>
                                <select class="form-select" id="courseFilter" name="titreFilter" onchange="this.form.submit()">
                                    <option value="" selected>Toutes les matières</option>


                                    <?php foreach ($coursesF as $course): ?>
                                        <option value="<?= htmlspecialchars($course['titre']) ?>"
                                            <?= (isset($titreFilter) && $titreFilter == $course['titre']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($course['titre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <!-- Filtre par niveau -->
                            <div class="col-md-4">
                                <label class="form-label">Filtrer par niveau</label>
                                <select class="form-select" name="levelFilter" onchange="this.form.submit()">
                                    <option value="all" <?= $levelFilter === 'all' ? 'selected' : '' ?>>Tous</option>
                                    <option value="debutant" <?= $levelFilter === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                                    <option value="intermediaire" <?= $levelFilter === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                    <option value="avance" <?= $levelFilter === 'avance' ? 'selected' : '' ?>>Avancé</option>
                                </select>
                            </div>


                            <!-- Recherche libre -->
                            <div class="col-md-4">
                                <label for="searchCourse" class="form-label">Rechercher</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="searchCourse"
                                        value="<?= htmlspecialchars($searchCourse) ?>"
                                        placeholder="Nom du cours...">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>


                        </div>
                    </div>
                </form>

 <!-- Boutons de scroll -->
<div class="scroll-container position-relative">

    <button class="scroll-btn left-btn">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="course-list d-flex" id="courseList" 
         style="overflow-x: hidden; scroll-behavior: smooth;">
        
        <?php foreach ($courses as $course): 
            $nbEtudiants = Cours::getNbEtudiantsInscrits($course['id']);
            $tauxPresence = Cours::tauxPresenceCours($course['id']);
            $nbSeances = Cours::getNbSeances($course['id']);
            $seances = Cours::getSeancesByCours($course['id']);
        ?>

        <div class="col-xl-4 col-lg-6 mb-4" style="flex: 0 0 33%; max-width: 33%;">
            <div class="card course-card" data-bs-toggle="modal" data-bs-target="#courseDetailModal">

                <div class="course-header" 
                     style="background: linear-gradient(135deg, var(--accent-dark), var(--accent-light));">
                    <h5 class="card-title fw-bold text-white"><?= htmlspecialchars($course['titre']) ?></h5>
                </div>

                <div class="course-body">
                    <p class="card-text text-muted">
                        <?= htmlspecialchars($course['description']) ?>
                    </p>

                    <div class="course-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?= $nbEtudiants ?></span>
                            <span class="stat-label">Étudiants</span>
                        </div>

                        <div class="stat-item">
                            <span class="stat-value"><?= $tauxPresence ?>%</span>
                            <span class="stat-label">Présence</span>
                        </div>

                        <div class="stat-item">
                            <span class="stat-value"><?= $nbSeances ?></span>
                            <span class="stat-label">Séances</span>
                        </div>
                    </div>

                <div class="mt-3">
                  <?php

                  $levels = [
                   'debutant' => ['label' => 'Débutant', 'color' => 'success'],
                    'intermediaire' => ['label' => 'Intermédiaire', 'color' => 'warning'], 
                    'avance' => ['label' => 'Avancé', 'color' => 'danger']           
                     ];

                  $level = $course['level'] ?? '';
                  $levelLabel = $levels[$level]['label'] ?? 'Niveau inconnu';
                  $levelColor = $levels[$level]['color'] ?? 'secondary';
                  ?>

                        <span class="badge bg-<?= $levelColor ?>"><?= $levelLabel ?></span>

                        <span class="badge bg-secondary ms-1"><?= $course['category'] ?></span>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <!-- <button class="btn btn-outline-primary btn-course">
                            <i class="fas fa-eye me-1"></i> Voir le détail
                        </button> -->
                          <!-- <button class="btn btn-outline-primary btn-course" 
                          data-bs-target="#courseDetailModal<?= $course['id'] ?>">
                    <i class="fas fa-eye me-1"></i> Voir le détail
                </button> -->
                   <button class="btn btn-outline-primary btn-course" 
                   data-bs-toggle="modal" data-bs-target="#courseDetailModal<?= $course['id'] ?>">
                    <i class="fas fa-eye me-1"></i> Voir le détail
                </button>
        <button class="btn btn-outline-secondary btn-course" 
        data-bs-toggle="modal"
        data-bs-target="#editCourseModal-<?= $course['id'] ?>">
    <i class="fas fa-edit me-1"></i> Modifier
</button>

                    </div>

                </div>
            </div>
        </div>
    <!-- Modal de détail du cours -->
  <?php 
// Supposons que $course soit le cours sélectionné avant d'inclure ce modal
//$seances = Cours::getSeancesByCours($course['id']);
$nbEtudiants = Cours::getNbEtudiantsInscrits($course['id']);
$tauxPresence = Cours::tauxPresenceCours($course['id']);
?>

<!-- Modal de détail du cours -->
<div class="modal fade course-detail-modal" id="courseDetailModal<?= $course['id'] ?>" tabindex="-1"
    aria-labelledby="courseDetailModalLabel<?= $course['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-course-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="modal-title mb-1 fw-bold" id="courseDetailModalLabel<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['titre']) ?>
                    </h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- Partie gauche -->
                    <div class="col-md-8">
                        <h5 class="fw-bold text-dark mb-3">Description du cours</h5>
                        <p class="text-muted"><?= htmlspecialchars($course['description']) ?></p>

                        <div class="row mb-4">
                            <div class="col-md-3 mb-2">
                                <strong class="text-dark">Étudiants:</strong><br>
                                <span class="text-muted"><?= $nbEtudiants ?></span>
                            </div>
                            <div class="col-md-3 mb-2">
                                <strong class="text-dark">Présence:</strong><br>
                                <span class="badge bg-success"><?= $tauxPresence ?>%</span>
                            </div>
                        </div>

                     <div class="d-flex mb-4">
                       <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#planSessionModal-<?= $course['id'] ?>">
                       <i class="fas fa-calendar-plus me-1"></i> Planifier une séance
                       </button>
                     </div>


                        <h5 class="fw-bold text-dark mb-3">Séances planifiées</h5>
                        <div class="timeline">
    <?php foreach ($seances as $index => $seance):  
        $seanceDebut = strtotime($seance['date'].' '.$seance['heureDebut']);
        $seanceFin   = strtotime($seance['date'].' '.$seance['heureFin']);
        $now = time();
        $presences = Presence::getPresencesBySeance($seance['id']);
        if ($now < $seanceDebut) {
            $status = 'upcoming';
            $badgeColor = 'primary';
            $badgeText = 'À venir';
        } elseif ($now >= $seanceDebut && $now <= $seanceFin) {
            $status = 'ongoing';
            $badgeColor = 'warning';
            $badgeText = 'En cours';
        } else {
            $status = 'completed';
            $badgeColor = 'success';
            $badgeText = 'Terminée';
        }
    ?>

        <div class="timeline-item <?= $status ?>">
            <div class="session-item session-<?= $status ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">Séance <?= $index+1 ?>: <?= htmlspecialchars($seance['titre']) ?></h6>
                        <p class="mb-1 text-muted"><?= date('d M Y', strtotime($seance['date'])) ?>, <?= htmlspecialchars($seance['heureDebut']) ?> - <?= htmlspecialchars($seance['heureFin']) ?></p>
                    </div>
                    <span class="badge bg-<?= $badgeColor ?> ms-2"><?= $badgeText ?></span>
                </div>
                <div class="mt-3">
                    <div class="mt-3">
                        <?php if ($status == 'upcoming'): ?>
                            <button class="btn btn-sm btn-outline-primary edit-session-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editSessionModal"
                                data-session-id="<?= $seance['id'] ?>"
                                data-date="<?= $seance['date'] ?>"
                                data-heure-debut="<?= $seance['heureDebut'] ?>"
                                data-heure-fin="<?= $seance['heureFin'] ?>">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </button>

                        <button class="btn btn-sm btn-outline-danger ms-1"
        data-bs-toggle="modal"
        data-bs-target="#deleteSessionModal-<?= $seance['id'] ?>">
    <i class="fas fa-trash me-1"></i> Supprimer
</button>

<?php else: ?>

<button class="btn btn-sm btn-outline-primary ms-2 btn-view-attendance"
        data-bs-toggle="modal" 
        data-bs-target="#viewAttendanceModal-<?= $seance['id'] ?>">
    <i class="fas fa-eye me-1"></i> Voir présence
</button>


<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Partie droite -->
                    <div class="col-md-4">
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">Statistiques rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Taux de présence moyen:</span>
                                    <strong class="text-dark"><?= $tauxPresence ?>%</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Séances terminées:</span>
                                    <strong class="text-dark"><?= count(array_filter($seances, fn($s) => strtotime($s['date'].' '.$s['heureDebut']) <= time())) ?>/<?= count($seances) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Prochaine séance:</span>
                                    <?php 
                                        $upcomingSeance = null; 
                                        foreach($seances as $s){ 
                                            if(strtotime($s['date'].' '.$s['heureDebut']) > time()){ 
                                                $upcomingSeance = $s; 
                                                break; 
                                            } 
                                        } 
                                    ?>
                                    <strong class="text-dark"><?= $upcomingSeance ? date('d M', strtotime($upcomingSeance['date'])) : '-' ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold">Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                  <a href="./GestionDesPrésencesEns.php" class="btn btn-outline-primary">
    <i class="fas fa-users me-1"></i> Gérer les étudiants
</a>

                                 
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal de planification de séance -->
<div class="modal fade" id="planSessionModal-<?= $course['id'] ?>" tabindex="-1" aria-labelledby="planSessionModalLabel-<?= $course['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="planSessionModalLabel-<?= $course['id'] ?>">
                    <i class="fas fa-calendar-plus me-2 text-primary"></i> Planifier une nouvelle séance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="../../Controller/seanceController.php">
                <input type="hidden" name="cours_id" value="<?= $course['id'] ?>">
                <input type="hidden" name="action" value="ajouterSeance">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sessionDate-<?= $course['id'] ?>" class="form-label">Date</label>
                           <input type="date" class="form-control" id="sessionDate-<?= $course['id'] ?>" name="date" required min="<?= date('Y-m-d') ?>" >
                        </div>
                        <div class="col-md-3">
                            <label for="startTime-<?= $course['id'] ?>" class="form-label">Heure de début</label>
                            <input type="time" class="form-control" id="startTime-<?= $course['id'] ?>" name="heureDebut" required>
                        </div>
                        <div class="col-md-3">
                            <label for="endTime-<?= $course['id'] ?>" class="form-label">Heure de fin</label>
                            <input type="time" class="form-control" id="endTime-<?= $course['id'] ?>" name="heureFin" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Planifier la séance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de modification -->
<div class="modal fade" id="editCourseModal-<?= $course['id'] ?>" tabindex="-1"
     aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-edit me-2 text-primary"></i> Modifier le cours
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="../../Controller/CoursController.php"
                  method="POST" enctype="multipart/form-data">

                <div class="modal-body">

                    <input type="hidden" name="action" value="modifierCours">
                    <input type="hidden" name="cours_id" value="<?= $course['id'] ?>">
                    <input type="hidden" name="ancienne_image" value="<?= $course['image'] ?>">
                    <input type="hidden" name="enseignant_id" value="<?= $_SESSION['id'] ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du cours</label>
                            <input type="text" name="titre" class="form-control"
                                   value="<?= htmlspecialchars($course['titre']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Niveau</label>
                            <select name="level" class="form-select">
                                <option value="debutant" <?= $course['level']=='debutant'?'selected':'' ?>>Débutant</option>
                                <option value="intermediaire" <?= $course['level']=='intermediaire'?'selected':'' ?>>Intermédiaire</option>
                                <option value="avance" <?= $course['level']=='avance'?'selected':'' ?>>Avancé</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="category" class="form-control"
                               value="<?= htmlspecialchars($course['category']) ?>" required>
                    </div>

                    <!-- Image actuelle -->
                    <div class="mb-3">
                        <label class="form-label">Image actuelle :</label>
                        <div class="p-2 border rounded bg-light text-center">
                            <?php if (!empty($course['image'])): ?>
                                <img src="../../Assets/Images/image/<?= htmlspecialchars($course['image']) ?>"
                                     alt="Image du cours"
                                     style="width:150px; height:150px; object-fit:cover; border-radius:8px;">
                            <?php else: ?>
                                <p class="text-muted">Aucune image disponible.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Nouvelle image -->
                    <div class="mb-3">
                        <label class="form-label">Choisir une nouvelle image</label>
                        <input type="file" class="form-control" name="image" id="newImageInput" accept="image/*">
                    </div>
<img id="newImagePreview" 
    class="circle-image"
     style="max-height: 250px; object-fit: cover;">
  
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>

            </form>

        </div>
    </div>
</div>
<!-- UN SEUL MODAL en dehors de toute boucle -->
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../../Controller/SeanceController.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2 text-primary"></i> Modifier la séance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifierSeance">
                    <input type="hidden" name="seance_id" id="editSessionId">
                    <input type="hidden" name="cours_id" value="<?= $course['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="editSessionDate" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Heure début</label>
                            <input type="time" name="heureDebut" id="editSessionHeureDebut" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Heure fin</label>
                            <input type="time" name="heureFin" id="editSessionHeureFin" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php foreach ($seances as $index => $seance):   ?>
<div class="modal fade" id="deleteSessionModal-<?= $seance['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="../../Controller/SeanceController.php">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmation de suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>

                    <h5 class="fw-bold">Supprimer cette séance ?</h5>

                    <p class="text-muted">
                        Cette action est irréversible. Toutes les présences associées seront supprimées.
                    </p>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Séance :</strong> 
                        <?= htmlspecialchars($s['titre']) ?> 
                        (<?= date('d M Y', strtotime($s['date'])) ?>)
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <input type="hidden" name="action" value="supprimerSeance">
                    <input type="hidden" name="seance_id" value="<?= $s['id'] ?>">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <button type="submit" class="btn btn-danger">
                        Supprimer définitivement
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
<div class="modal fade" id="viewAttendanceModal-<?= $seance['id'] ?>" tabindex="-1" aria-labelledby="viewAttendanceModalLabel"
        aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="viewAttendanceModalLabel">
                    <i class="fas fa-users me-2 text-primary"></i>
                    Présences - Séance <?= $index+1 ?> : <?= htmlspecialchars($seance['titre']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Informations de la séance -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <p class="mb-1 text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('d M Y', strtotime($seance['date'])) ?>,
                            <?= $seance['heureDebut'] ?> - <?= $seance['heureFin'] ?>
                        </p>
                    </div>

                    <div class="text-end">
                        <?php
                            $total = count($presences);
                            $presentCount = array_sum(array_map(fn($p) => $p['statut'] == 'present' ? 1 : 0, $presences));
                            $percentage = $total > 0 ? round(($presentCount / $total) * 100) : 0;
                        ?>
                        <h5 class="mb-1"><?= $presentCount ?>/<?= $total ?> présents</h5>
                        <span class="badge bg-success"><?= $percentage ?>% de présence</span>
                    </div>
                </div>

                <!-- Tableau des présences -->
                <form action="../../Controller/presenceController.php" method="POST">
                    <input type="hidden" name="action" value="updatePresence">
                    <input type="hidden" name="seance_id" value="<?= $seance['id'] ?>">

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Matricule</th>
                                    <th>Statut</th>
                                    <th>Heure d'arrivée</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($presences as $p): ?>
                                <tr>

                                    <!-- Étudiant : photo + nom + email -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../../Assets/Images/image/<?= $p['photoProfil'] ?>"
                                                 class="rounded-circle me-3" width="40" height="40">
                                            <div>
                                                <strong><?= $p['etudiant_nom'] . " " . $p['etudiant_prenom'] ?></strong>
                                                <div class="text-muted small"><?= $p['email'] ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Matricule -->
                                    <td><?= $p['etudiant_matricule'] ?></td>

                                    <!-- Statut (modifiable sans JS) -->
                                    <!-- <td>
                                        <select name="statut[<?= $p['id'] ?>]" class="form-select form-select-sm w-auto">
                                            <option value="present" <?= $p['statut']=="present" ? "selected" : "" ?>>
                                                Présent
                                            </option>
                                            <option value="absent" <?= $p['statut']=="absent" ? "selected" : "" ?>>
                                                Absent
                                            </option>
                                        </select>
                                        </td> -->
                            <td >

    <div class="attendance-status <?= ($p['statut']=="Présent" ? "present" : "absent") ?>"
         data-etudiant="<?= $p['etudiant_id'] ?>"
         data-seance="<?= $p['seance_id'] ?>"
         title="<?= $p['statut'] ?>">
        <?= $p['statut']=="Présent" ? "P" : "A" ?>
    </div>
    <input type="hidden"
        name="status[<?= $p['etudiant_id'] ?>][<?= $p['seance_id'] ?>]"
        value="<?= $p['statut'] ?>"
        class="status-input">
        <input type="hidden" name="save2">
</td>


                                    

                                    <!-- Heure d'arrivée -->
                                    <td><?= $p['HeureArrivee'] ? date('H:i', strtotime($p['HeureArrivee'])) : '-' ?></td>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bouton d'enregistrement -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div></div>
                        <button class="btn btn-primary" name="save">
                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

        <?php endforeach; ?>

    </div>

    <button class="scroll-btn right-btn">
        <i class="fas fa-chevron-right"></i>
    </button>

</div>
   <!-- Modal Ajout de Cours -->
<!-- Modal Ajout de Cours -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4">

            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="addCourseModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Créer un nouveau cours
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="../../Controller/CoursController.php"
                  method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="ajouterCours">
                <div class="modal-body">

                    <!-- Titre -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre du cours <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="form-control form-control-lg" placeholder="Ex : Développement Web" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brève description du cours..."></textarea>
                    </div>

                    <div class="row">
                        <!-- Level -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Niveau</label>
                            <select name="level" class="form-select" required>
                                <option disabled selected>Choisir...</option>
                                <option value="debutant">Débutant</option>
                                <option value="intermediaire">Intermédiaire</option>
                                <option value="avance">Avancé</option>
                            </select>
                        </div>

                        <!-- Catégorie -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Catégorie</label>
                            <input type="text" name="category" class="form-control" placeholder="Ex : Informatique, Gestion...">
                        </div>
                    </div>

                    <!-- Upload + Preview -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image du cours</label>
                        <input type="file" name="image" class="form-control" id="imageInput"
                               accept="image/*">

                        <!-- Zone de prévisualisation -->
                        <div class="mt-3 text-center">
                            <img id="previewImage" 
                                 src="" 
                                 alt="Aperçu de l'image"
                                 class="circle-image"
                                 style="max-height: 250px; object-fit: cover;">
                        </div>
                    </div>

                    <!-- ID Enseignant -->
                    <input type="hidden" name="enseignant_id" value="<?= $_SESSION['id'] ?? '' ?>">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check me-1"></i>Ajouter
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


</div><!-- End Main Container -->
    <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_SESSION["success"])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '<?= addslashes($_SESSION["success"]) ?>',
                    showConfirmButton: false,
                    timer: 2000
                });
            <?php unset($_SESSION["success"]);
            endif; ?>

            <?php if (isset($_SESSION["error"])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '<?= addslashes($_SESSION["error"]) ?>'
                });
            <?php unset($_SESSION["error"]);
            endif; ?>
        });
        document.querySelector(".left-btn").addEventListener("click", () => {
    document.getElementById("courseList").scrollLeft -= 350;
});

document.querySelector(".right-btn").addEventListener("click", () => {
    document.getElementById("courseList").scrollLeft += 350;
});
    document.getElementById("imageInput").addEventListener("change", function() {
    const file = this.files[0];
    const preview = document.getElementById("previewImage");

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    }
});
document.getElementById("newImageInput").addEventListener("change", function() {
    const file = this.files[0];
    const preview = document.getElementById("newImagePreview");

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    }
});
// JavaScript pour charger les données dans le modal
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-session-btn');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Récupérer les données depuis les attributs data
            const sessionId = this.getAttribute('data-session-id');
            const date = this.getAttribute('data-date');
            const heureDebut = this.getAttribute('data-heure-debut');
            const heureFin = this.getAttribute('data-heure-fin');
            
            // Remplir le modal avec les données
            document.getElementById('editSessionId').value = sessionId;
            document.getElementById('editSessionDate').value = date;
            document.getElementById('editSessionHeureDebut').value = heureDebut;
            document.getElementById('editSessionHeureFin').value = heureFin;
        });
    });
}); 
document.addEventListener('DOMContentLoaded', function() {

    const statusElements = document.querySelectorAll('.attendance-status');

    statusElements.forEach(element => {
        element.addEventListener('click', function() {

            let input = this.parentElement.querySelector('.status-input');

            if (input.value === "Présent") {

                this.classList.remove('present');
                this.classList.add('absent');
                this.textContent = "A";
                this.title = "Absent";

                input.value = "Absent";

            } else {

                this.classList.remove('absent');
                this.classList.add('present');
                this.textContent = "P";
                this.title = "Présent";

                input.value = "Présent";
            }
        });
    });
});

    </script>
    
</body>

</html>