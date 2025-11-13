<?php
include("../../Model/admin.php");
include("../../Model/presence.php");
include("../../Model/cours.php");
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");
    exit;
}

if (strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';
$enseignants = Admin::getEnseignants();
$presences = presence::getHistoriquePresence();
$coursList = Cours::getAllCours();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedCourse'])) {
    $_SESSION['selectedCourse'] = $_POST['selectedCourse'];
    $activeTab = 'attendance';
}

$activeTab = $_GET['tab'] ?? 'courses';
$selectedCourse = $_SESSION['selectedCourse'] ?? '';

// Filtrer les présences
$filteredPresences = [];
foreach ($presences as $presence) {
    if ($selectedCourse === '' || $presence['titre'] === $selectedCourse) {
        $filteredPresences[] = $presence;
    }
}
$nbPresence = Presence::getPresenceMois();
$tauxPresence = Presence::getTauxPresenceAnnuel();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduAdmin - Suivi & Contrôle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5f7fb;
            color: var(--dark);
        }

        /* Header */
        .header {
            height: 70px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .user-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-info p {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Navigation */
        .nav {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-menu {
            display: flex;
            list-style: none;
        }

        .nav-menu li {
            padding: 0;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
            border-bottom: 3px solid transparent;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-bottom: 3px solid var(--success);
        }

        .nav-menu i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .page-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .breadcrumb {
            display: flex;
            list-style: none;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .breadcrumb li:not(:last-child)::after {
            content: "/";
            margin: 0 10px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 0 20px;
        }

        .tab {
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-body h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .card-body p {
            font-size: 0.9rem;
            color: var(--gray);
        }

        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Pie chart container - made smaller */
        .pie-chart-container {
            height: 350px;
        }

        /* Tables */
        .table-container {
            background-color: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }

        .btn-danger {
            background-color: #e63946;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c1121f;
        }

        .btn-success {
            background-color: #2a9d8f;
            color: white;
        }

        .btn-success:hover {
            background-color: #21867a;
        }

        .form-control {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border 0.3s;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: var(--dark);
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .report-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .report-option {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .report-option:hover {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .report-option.selected {
            border-color: var(--primary);
            background-color: rgba(67, 97, 238, 0.1);
        }

        .report-option input {
            margin-right: 8px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                flex-direction: column;
            }

            .nav-menu a {
                justify-content: center;
            }

            .tabs {
                flex-direction: column;
            }

            .chart-row {
                grid-template-columns: 1fr;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .report-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Content Area -->
    <div class="content">
        <div class="tabs">
            <div class="tab <?= ($activeTab === 'courses') ? 'active' : '' ?>" data-tab="courses">Gestion des Cours</div>
            <div class="tab <?= ($activeTab === 'attendance') ? 'active' : '' ?>" data-tab="attendance">Suivi de Présence</div>


        </div>

        <!-- Gestion des Cours Tab -->
        <div class="tab-content <?= ($activeTab === 'courses') ? 'active' : '' ?>" id="courses-tab">
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours Actifs</div>
                        <div class="card-icon" style="background-color: var(--success);">
                            <i class="fas fa-play-circle"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>142</h3>
                        <p>En cours cette semaine</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours Terminés</div>
                        <div class="card-icon" style="background-color: var(--info);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>89</h3>
                        <p>Cette année académique</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cours à Venir</div>
                        <div class="card-icon" style="background-color: var(--warning);">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>24</h3>
                        <p>Programmés pour le mois prochain</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Tous les Cours</div>
                    <button class="btn btn-primary" id="addCourseBtn">Ajouter un Cours</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom du Cours</th>
                            <th>Enseignant</th>
                            <th>Date de Début</th>
                            <th>Date de Fin</th>
                            <th>Étudiants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>INF301</td>
                            <td>Algorithmes Avancés</td>
                            <td>Prof. Dubois</td>
                            <td>15 Sep 2023</td>
                            <td>15 Jan 2024</td>
                            <td>45</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary edit-course-btn"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-course-btn"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>MAT205</td>
                            <td>Analyse Complexe</td>
                            <td>Prof. Laurent</td>
                            <td>10 Sep 2023</td>
                            <td>10 Jan 2024</td>
                            <td>32</td>
                            <td><span class="badge badge-success">Actif</span></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-primary edit-course-btn"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-course-btn"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Suivi de Présence Tab -->
        <div class="tab-content <?= ($activeTab === 'attendance') ? 'active' : '' ?>" id="attendance-tab">
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Présence Globale</div>
                        <div class="card-icon" style="background-color: var(--primary);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3><?php echo $tauxPresence; ?>%</h3>
                        <p>Moyenne annuelle</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Présence Étudiants</div>
                        <div class="card-icon" style="background-color: var(--info);">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3><?php echo $nbPresence; ?></h3>
                        <p>Ce mois-ci</p>
                    </div>
                </div>

            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Détails de Présence</div>
                    <div class="d-flex align-items-start gap-2 gap-md-3 gap-lg-4">
                        <form method="POST" action="" id="presenceFilterForm">
                            <select class="form-select" name="selectedCourse" onchange="this.form.submit()">
                                <option value="">Sélectionner un cours</option>
                                <?php foreach ($coursList as $coursItem): ?>
                                    <option value="<?= htmlspecialchars($coursItem['titre']) ?>"
                                        <?= ($selectedCourse === $coursItem['titre']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($coursItem['titre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <form method="POST" action="../../Controller/coursController.php" target="_blank">
                            <input type="hidden" name="selectedCourse" value="<?= htmlspecialchars($selectedCourse) ?>">
                            <button type="submit" name="generatePdf" class="btn btn-primary">
                                Générer PDF
                            </button>
                        </form>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Cours</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $selectedCourse = $_POST['selectedCourse'] ?? '';
                        $filteredPresences = [];

                        foreach ($presences as $presence) {
                            if ($selectedCourse === '' || $presence['titre'] === $selectedCourse) {
                                $filteredPresences[] = $presence;
                            }
                        }

                        if (empty($filteredPresences)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Aucune donnée disponible.</td>
                            </tr>
                            <?php else:
                            foreach ($filteredPresences as $presence): ?>
                                <tr>
                                    <td><?= htmlspecialchars($presence['nomEtudiant']) ?></td>
                                    <td><?= htmlspecialchars($presence['titre']) ?></td>
                                    <td><?= date('d M Y', strtotime($presence['date'])) ?></td>
                                    <td>
                                        <?php if (strtolower($presence['statut']) === 'présent' || strtolower($presence['statut']) === 'present'): ?>
                                            <span class="badge badge-success">Présent</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Absent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($presence['heureDebut']) ?> - <?= htmlspecialchars($presence['heureFin']) ?></td>
                                </tr>
                        <?php endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>


    <!-- Modal pour Ajouter un Cours -->
    <div class="modal" id="addCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ajouter un Nouveau Cours</h3>
                <button class="modal-close" data-modal="addCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm" action="../../Controller/AdminController.php" method="POST">
                    <input type="hidden" name="action" value="ajouterCours">

                    <div class="form-group">
                        <label class="form-label" for="courseTitle">Titre du Cours</label>
                        <input type="text" class="form-control" id="courseTitle" name="titre"
                            placeholder="Ex: Algorithmique Avancée" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="courseTeacher">Enseignant</label>
                        <select class="form-control" id="courseTeacher" name="enseignant_id" required>
                            <option value="">Sélectionner un enseignant</option>
                            <?php foreach ($enseignants as $ens): ?>
                                <option value="<?= htmlspecialchars($ens['id']) ?>">
                                    <?= htmlspecialchars($ens['nom']) ?> <?= htmlspecialchars($ens['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="courseDescription">Description du Cours</label>
                        <textarea class="form-control" id="courseDescription" name="description" rows="3"
                            placeholder="Description du cours..."></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" data-modal="addCourseModal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="saveCourseBtn">Enregistrer le Cours</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal pour Modifier un Cours -->
    <div class="modal" id="editCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier le Cours</h3>
                <button class="modal-close" data-modal="editCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editCourseCode">Code du Cours</label>
                            <input type="text" class="form-control" id="editCourseCode" placeholder="Ex: INF301"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editCourseName">Nom du Cours</label>
                            <input type="text" class="form-control" id="editCourseName"
                                placeholder="Ex: Algorithmes Avancés" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editCourseTeacher">Enseignant</label>
                        <select class="form-control" id="editCourseTeacher" required>
                            <option value="">Sélectionner un enseignant</option>
                            <option value="Prof. Dubois">Prof. Dubois</option>
                            <option value="Prof. Laurent">Prof. Laurent</option>
                            <option value="Prof. Martin">Prof. Martin</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editStartDate">Date de Début</label>
                            <input type="date" class="form-control" id="editStartDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editEndDate">Date de Fin</label>
                            <input type="date" class="form-control" id="editEndDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editMaxStudents">Nombre Maximum d'Étudiants</label>
                        <input type="number" class="form-control" id="editMaxStudents" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editCourseDescription">Description du Cours</label>
                        <textarea class="form-control" id="editCourseDescription" rows="3"
                            placeholder="Description du cours..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="editCourseModal">Annuler</button>
                <button class="btn btn-success" id="updateCourseBtn">Mettre à Jour</button>
            </div>
        </div>
    </div>

    <!-- Modal pour Supprimer un Cours -->
    <div class="modal" id="deleteCourseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirmer la Suppression</h3>
                <button class="modal-close" data-modal="deleteCourseModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le cours <strong id="courseToDeleteName"></strong> ?</p>
                <p class="text-danger">Cette action est irréversible et supprimera toutes les données associées à ce
                    cours.</p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="deleteCourseModal">Annuler</button>
                <button class="btn btn-danger" id="confirmDeleteCourseBtn">Supprimer Définitivement</button>
            </div>
        </div>
    </div>

    <!-- Modal pour Générer un Rapport -->
    <div class="modal" id="generateReportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Générer un Rapport</h3>
                <button class="modal-close" data-modal="generateReportModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="reportCourse">Sélectionner un Cours</label>
                    <select class="form-control" id="reportCourse">
                        <option value="">Sélectionner un cours</option>
                        <option value="Algorithmes Avancés">Algorithmes Avancés</option>
                        <option value="Analyse Complexe">Analyse Complexe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Type de Rapport</label>
                    <div class="report-options">
                        <label class="report-option">
                            <input type="radio" name="reportType" value="presence" checked>
                            Rapport de Présence
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="performance">
                            Rapport de Performance
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="students">
                            Liste des Étudiants
                        </label>
                        <label class="report-option">
                            <input type="radio" name="reportType" value="detailed">
                            Rapport Détaillé
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="startDateRange">Date de Début</label>
                        <input type="date" class="form-control" id="startDateRange">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="endDateRange">Date de Fin</label>
                        <input type="date" class="form-control" id="endDateRange">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reportFormat">Format du Rapport</label>
                    <select class="form-control" id="reportFormat">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="generateReportModal">Annuler</button>
                <button class="btn btn-primary" id="confirmGenerateReportBtn">Générer le Rapport</button>
            </div>
        </div>
    </div>
    <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // document.addEventListener('DOMContentLoaded', () => {

        //     const urlParams = new URLSearchParams(window.location.search);
        //     const activeTab = urlParams.get('tab') || 'courses';

        //     activateTab(activeTab);

        //     document.querySelectorAll('.tab').forEach(tab => {
        //         tab.addEventListener('click', function() {
        //             const tabId = this.getAttribute('data-tab');
        //             activateTab(tabId);

        //             const url = new URL(window.location);
        //             url.searchParams.set('tab', tabId);
        //             window.history.pushState({}, '', url);
        //         });
        //     });

        //     function activateTab(tabId) {
        //         document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        //         document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');

        //         document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        //         document.getElementById(tabId + '-tab').classList.add('active');
        //     }
        // });
        document.addEventListener('DOMContentLoaded', () => {

            let activeTab = localStorage.getItem('activeTab');
            if (activeTab !== 'courses' && activeTab !== 'attendance') {
                activeTab = 'courses';
            }
            activateTab(activeTab);

            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    activateTab(tabId);
                    localStorage.setItem('activeTab', tabId);
                });
            });

            function activateTab(tabId) {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                const tab = document.querySelector(`.tab[data-tab="${tabId}"]`);
                if (tab) tab.classList.add('active');

                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                const content = document.getElementById(tabId + '-tab');
                if (content) content.classList.add('active');
            }
        });









        // Success Rate Chart
        const successRateCtx = document.getElementById('successRateChart').getContext('2d');
        const successRateChart = new Chart(successRateCtx, {
            type: 'bar',
            data: {
                labels: ['L1', 'L2', 'L3', 'M1', 'M2'],
                datasets: [{
                    label: 'Taux de Réussite (%)',
                    data: [75, 82, 79, 85, 88],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4cc9f0',
                        '#4895ef',
                        '#f72585'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

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
    </script>
</body>

</html>