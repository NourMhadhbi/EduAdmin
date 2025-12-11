<?php
include("../../Model/admin.php");
include("../../Model/presence.php");
include("../../Model/cours.php");
include("../../Model/seance.php");
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
$statistiques = seance::getSeancesStatistics();
$seances = seance::findAllSeances();

$cours = Cours::getAllCoursWithDetails();

$coursAvecSeances = [];
foreach ($cours as $c) {
    $coursAvecSeances[$c['id']] = Cours::hasSeancesAssociees($c['id']);
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduAdmin - Suivi & Contrôle</title>
    <link rel="stylesheet" href="../../Css/global-theme.css">
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

        .cours-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .cours-table th {
            background: #f5f7fa;
            text-align: left;
            padding: 12px;
            font-weight: 600;
            color: #333;
        }

        .cours-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .course-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .course-image {
            width: 55px;
            height: 35px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }



        .action-buttons button {
            margin-right: 5px;
        }

        .cours-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .cours-table th {
            background: #f5f7fa;
            text-align: left;
            padding: 12px;
            font-weight: 600;
            color: #333;
        }

        .cours-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .course-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .course-image {
            width: 55px;
            height: 35px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .badge-level,
        .badge-category {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            color: #fff;
        }

        .badge-level {
            background: #007bff;
        }

        .badge-category {
            background: #d12a04ff;
        }

        .action-buttons button {
            margin-right: 5px;
        }

        /* Styles généraux pour le modal */
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
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* En-tête du modal */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #eaeaea;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .modal-title {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.2s;
            line-height: 1;
        }

        .modal-close:hover {
            color: #e74c3c;
        }

        /* Corps du modal */
        .modal-body {
            padding: 25px;
        }

        /* Styles pour le formulaire */
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Styles pour les champs de fichier */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 1px dashed #bdc3c7;
            border-radius: 8px;
            text-align: center;
            color: #7f8c8d;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-input-wrapper:hover .file-input-label {
            background-color: #e8f4fd;
            border-color: #3498db;
            color: #3498db;
        }

        /* Pied de page du modal */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid #eaeaea;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .circle-image {
            width: 200px;
            height: 200px;
            display: none;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            border: 3px solid #e0e0e0;
        }

        .image-preview-container {
            text-align: center;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .modal-content {
                width: 95%;
            }

            .modal-body {
                padding: 15px;
            }
        }

        .description-truncate {
            cursor: pointer;
            color: var(--primary);
            transition: color 0.3s;
            display: block;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .description-truncate:hover {
            color: var(--secondary);
            text-decoration: underline;
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
                        <h3><?php echo $statistiques['active']; ?></h3>

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
                        <h3><?php echo $statistiques['completed']; ?></h3>

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
                        <h3><?php echo $statistiques['upcoming']; ?></h3>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Tous les Cours</div>
                    <button class="btn btn-primary" id="addCourseBtn">Ajouter un Cours</button>
                </div>
                <table class="cours-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Cours</th>
                            <th>Description</th>
                            <th>Enseignant</th>
                            <th>Niveau</th>
                            <th>Catégorie</th>
                            <th>Étudiants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($cours as $c): ?>
                            <tr>
                                <td><?php echo str_pad($c['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="td-course-title">


                                    <span class="course-title-text">
                                        <?php echo htmlspecialchars($c['titre']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="description-truncate"
                                        data-description="<?php echo htmlspecialchars($c['description']); ?>">
                                        <?php
                                        $description = $c['description'];
                                        if (strlen($description) > 50) {
                                            echo htmlspecialchars(substr($description, 0, 50)) . '...';
                                        } else {
                                            echo htmlspecialchars($description);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($c['enseignant_nom']); ?></td>
                                <td>
                                    <span class="badge-level">
                                        <?php echo htmlspecialchars($c['level'] ?? "—"); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-category">
                                        <?php echo htmlspecialchars($c['category'] ?? "—"); ?>
                                    </span>
                                </td>
                                <td><?php echo $c['nb_etudiants']; ?></td>

                                <td class="action-buttons">

                                    <button class="btn btn-sm btn-primary edit-course-btn"
                                        data-cours-id="<?php echo $c['id']; ?>"
                                        data-cours-titre="<?php echo htmlspecialchars($c['titre']); ?>"
                                        data-cours-description="<?php echo htmlspecialchars($c['description'] ?? ''); ?>"
                                        data-cours-level="<?php echo htmlspecialchars($c['level'] ?? ''); ?>"
                                        data-cours-category="<?php echo htmlspecialchars($c['category'] ?? ''); ?>"
                                        data-cours-image="<?php echo htmlspecialchars($c['image'] ?? ''); ?>"
                                        data-cours-enseignant-id="<?php echo $c['enseignant_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>


                                    <button class="btn btn-sm btn-danger delete-course-btn"
                                        data-cours-id="<?php echo $c['id']; ?>"
                                        data-cours-titre="<?php echo htmlspecialchars($c['titre']); ?>"
                                        data-cours-nb-etudiants="<?php echo $c['nb_etudiants']; ?>"
                                        data-cours-has-seances="<?php echo $coursAvecSeances[$c['id']] ? 'true' : 'false'; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>


                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($cours)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucun cours trouvé</td>
                            </tr>
                        <?php endif; ?>
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

    <!-- Modal pour afficher la description complète -->
    <div class="modal" id="descriptionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Description complète</h3>
                <button class="modal-close" data-modal="descriptionModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="fullDescription"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal="descriptionModal">Fermer</button>
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
                <form id="addCourseForm" action="../../Controller/CoursController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="ajouterCours">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="courseTitle">Titre du Cours</label>
                            <input type="text" class="form-control" id="courseTitle" name="titre" placeholder="Ex: Algorithmique Avancée" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="courseTeacher">Enseignant</label>
                            <select class="form-control" id="courseTeacher" name="enseignant_id" required>
                                <option value="">Sélectionner un enseignant</option> <?php foreach ($enseignants as $ens): ?> <option value="<?= htmlspecialchars($ens['id']) ?>"> <?= htmlspecialchars($ens['nom']) ?> <?= htmlspecialchars($ens['prenom']) ?> </option> <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="courseDescription">Description du Cours</label>
                        <textarea class="form-control" id="courseDescription" name="description" rows="3" placeholder="Description du cours..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="courseLevel">Niveau</label>
                            <select class="form-control" id="courseLevel" name="level" required>
                                <option value="">Sélectionner un niveau</option>
                                <option value="debutant">Débutant</option>
                                <option value="intermediaire">Intermédiaire</option>
                                <option value="avance">Avancé</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="courseCategory">Catégorie</label>
                            <input type="text" class="form-control" id="courseCategory" name="category" placeholder="Ex: Informatique">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="courseImage">Image du Cours</label>
                            <div class="file-input-wrapper">
                                <input type="file" class="form-control file-input" id="courseImage" name="image" accept="image/*">
                                <span class="file-input-label">Choisir un fichier</span>
                            </div>
                            <div style="text-align:center; margin-top:10px;">
                                <img id="previewImage" src="#" alt="Aperçu de l'image" class="circle-image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal="addCourseModal">Annuler</button>
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
                <form id="editCourseForm" action="../../Controller/CoursController.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="modifierCours">
                    <input type="hidden" name="cours_id" id="editCoursId">
                    <input type="hidden" name="ancienne_image" id="ancienne_image">

                    <div class="form-group">
                        <label class="form-label" for="editCourseName">Nom du Cours</label>
                        <input type="text" class="form-control" id="editCourseName" name="titre" placeholder="Ex: Algorithmes Avancés" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCourseDescription">Description du Cours</label>
                        <textarea class="form-control" id="editCourseDescription" name="description" rows="3" placeholder="Description du cours..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editCourseLevel">Niveau</label>
                            <select class="form-control" id="editCourseLevel" name="level" required>
                                <option value="">Sélectionner un niveau</option>
                                <option value="debutant">Débutant</option>
                                <option value="intermediaire">Intermédiaire</option>
                                <option value="avance">Avancé</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="editCourseCategory">Catégorie</label>
                            <input type="text" class="form-control" id="editCourseCategory" name="category" placeholder="Ex: Informatique" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCourseTeacher">Enseignant</label>
                        <select class="form-control" id="editCourseTeacher" name="enseignant_id" required>
                            <option value="">Sélectionner un enseignant</option>
                            <?php foreach ($enseignants as $ens): ?>
                                <option value="<?= htmlspecialchars($ens['id']) ?>">
                                    <?= htmlspecialchars($ens['nom']) ?> <?= htmlspecialchars($ens['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCourseImage">Image du Cours</label>
                        <div class="file-input-wrapper">
                            <input type="file" class="form-control file-input" id="editCourseImage" name="image" accept="image/*">
                            <span class="file-input-label" id="editFileInputLabel">Choisir un nouveau fichier</span>
                        </div>
                        <div style="text-align:center; margin-top:10px;">
                            <div id="imagePreviewContainer">
                                <img id="editPreviewImage" src="#" alt="Aperçu de l'image" class="circle-image">
                            </div>
                            <small id="imageInfo" class="text-muted" style="display:none;"></small>
                        </div>
                        <small class="text-muted">Laisser vide pour conserver l'image actuelle</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal="editCourseModal">Annuler</button>
                <button type="button" class="btn btn-success" id="updateCourseBtn">Mettre à Jour</button>
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
                <div id="deleteBasicInfo">
                    <p>Êtes-vous sûr de vouloir supprimer le cours <strong id="courseToDeleteName"></strong> ?</p>
                    <p id="studentsInfo" class="text-warning"></p>
                </div>
                <div id="seancesWarning" style="display: none;">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Attention !</h5>
                        <p>Ce cours a des séances associées. La suppression du cours entraînera également la suppression de :</p>
                        <ul>
                            <li>Toutes les séances planifiées</li>
                            <li>Tous les enregistrements de présence associés</li>
                        </ul>
                        <p class="text-danger"><strong>Cette action est irréversible !</strong></p>
                    </div>
                </div>
                <p class="text-danger">Cette action est irréversible et supprimera toutes les données associées à ce cours.</p>
            </div>
            <div class="modal-footer">
                <button class="btn" data-modal="deleteCourseModal">Annuler</button>

                <form method="POST" action="../../Controller/CoursController.php" id="deleteCourseForm" style="display: inline;">
                    <input type="hidden" name="action" value="supprimer_cours">
                    <input type="hidden" name="cours_id" id="deleteCourseId">
                    <button type="submit" class="btn btn-danger">Supprimer Définitivement</button>
                </form>
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
        window.openModal = function(modalId) {
            document.getElementById(modalId).classList.add('active');
        };

        window.closeModal = function(modalId) {
            document.getElementById(modalId).classList.remove('active');
        };
        document.querySelectorAll('.modal-close, .modal-footer .btn:not(.btn-primary):not(.btn-success):not(.btn-danger)').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                closeModal(modalId);
            });
        });
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });
        document.querySelectorAll('.description-truncate').forEach(element => {
            element.addEventListener('click', function() {
                const fullDescription = this.getAttribute('data-description');
                document.getElementById('fullDescription').textContent = fullDescription;
                openModal('descriptionModal');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

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

            function openModal(modalId) {
                console.log('Ouverture du modal:', modalId);
                document.getElementById(modalId).classList.add('active');
                document.body.style.overflow = 'hidden';
            }


            function closeModal(modalId) {
                console.log('Fermeture du modal:', modalId);
                document.getElementById(modalId).classList.remove('active');
                document.body.style.overflow = 'auto';
            }


            function closeAllModals() {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = 'auto';
            }


            document.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-modal');
                    closeModal(modalId);
                });
            });



            document.querySelectorAll('.modal-footer .btn-secondary').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal.id);
                });
            });


            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this.id);
                    }
                });
            });



            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllModals();
                }
            });

            document.getElementById('addCourseBtn').addEventListener('click', function() {
                openModal('addCourseModal');
            });

            const addImageInput = document.getElementById('courseImage');
            if (addImageInput) {
                const addFileLabel = addImageInput.nextElementSibling;
                const addPreview = document.getElementById('previewImage');


                addImageInput.addEventListener('change', function(e) {
                    const fileName = this.files[0]?.name || "Choisir un fichier";
                    if (addFileLabel) addFileLabel.textContent = fileName;


                    if (this.files && this.files[0] && addPreview) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            addPreview.src = e.target.result;
                            addPreview.style.display = 'block';
                        }
                        reader.readAsDataURL(this.files[0]);
                    } else if (addPreview) {
                        addPreview.style.display = 'none';
                    }
                });
            }

            document.querySelectorAll('.edit-course-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();


                    const coursId = this.getAttribute('data-cours-id');
                    const coursTitre = this.getAttribute('data-cours-titre');
                    const coursDescription = this.getAttribute('data-cours-description');
                    const coursLevel = this.getAttribute('data-cours-level');
                    const coursCategory = this.getAttribute('data-cours-category');
                    const coursImage = this.getAttribute('data-cours-image');
                    const coursEnseignantId = this.getAttribute('data-cours-enseignant-id');


                    console.log('Données récupérées pour modification:', {
                        coursId,
                        coursTitre,
                        coursDescription,
                        coursLevel,
                        coursCategory,
                        coursImage,
                        coursEnseignantId
                    });


                    // Remplir le formulaire d'édition
                    document.getElementById('editCoursId').value = coursId;
                    document.getElementById('editCourseName').value = coursTitre;
                    document.getElementById('editCourseDescription').value = coursDescription;
                    document.getElementById('editCourseLevel').value = coursLevel;
                    document.getElementById('editCourseCategory').value = coursCategory;
                    document.getElementById('editCourseTeacher').value = coursEnseignantId;

                    // Gérer l'image
                    const ancienneImageInput = document.getElementById('ancienne_image');
                    const editPreviewImage = document.getElementById('editPreviewImage');
                    const editFileInputLabel = document.getElementById('editFileInputLabel');

                    if (coursImage && coursImage !== 'null' && coursImage !== '' && coursImage !== 'undefined') {
                        if (ancienneImageInput) ancienneImageInput.value = coursImage;
                        const imagePath = '../../Images/image/' + coursImage;

                        const img = new Image();
                        img.onload = function() {
                            if (editPreviewImage) {
                                editPreviewImage.src = imagePath;
                                editPreviewImage.style.display = 'block';
                            }
                            if (editFileInputLabel) editFileInputLabel.textContent = 'Image actuelle: ' + coursImage;
                        };
                        img.onerror = function() {
                            if (editPreviewImage) editPreviewImage.style.display = 'none';
                            if (editFileInputLabel) editFileInputLabel.textContent = 'Image non trouvée: ' + coursImage;
                        };
                        img.src = imagePath;
                    } else {
                        if (ancienneImageInput) ancienneImageInput.value = '';
                        if (editPreviewImage) editPreviewImage.style.display = 'none';
                        if (editFileInputLabel) editFileInputLabel.textContent = 'Aucune image - Choisir un fichier';
                    }


                    // Ouvrir le modal
                    openModal('editCourseModal');
                });
            });


            // Prévisualisation de l'image dans l'édition
            const editImageInput = document.getElementById('editCourseImage');
            if (editImageInput) {
                const editFileInputLabel = document.getElementById('editFileInputLabel');
                const editPreviewImage = document.getElementById('editPreviewImage');


                editImageInput.addEventListener('change', function() {
                    const file = this.files[0];

                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (editPreviewImage) {
                                editPreviewImage.src = e.target.result;
                                editPreviewImage.style.display = 'block';
                            }
                        }
                        reader.readAsDataURL(file);
                        if (editFileInputLabel) editFileInputLabel.textContent = file.name;
                    } else {
                        // Si aucun fichier n'est sélectionné, revenir à l'image originale
                        const ancienneImage = document.getElementById('ancienne_image')?.value;
                        if (ancienneImage && ancienneImage !== 'null' && ancienneImage !== '' && editPreviewImage) {
                            editPreviewImage.src = '../../Uploads/Cours/' + ancienneImage;
                            editPreviewImage.style.display = 'block';
                            if (editFileInputLabel) editFileInputLabel.textContent = 'Image actuelle: ' + ancienneImage;
                        } else {
                            if (editPreviewImage) editPreviewImage.style.display = 'none';
                            if (editFileInputLabel) editFileInputLabel.textContent = 'Choisir un nouveau fichier';
                        }
                    }
                });
            }


            // Soumission du formulaire d'édition
            const updateCourseBtn = document.getElementById('updateCourseBtn');
            if (updateCourseBtn) {
                updateCourseBtn.addEventListener('click', function() {
                    const form = document.getElementById('editCourseForm');

                    // Validation
                    const titre = document.getElementById('editCourseName')?.value.trim();
                    const level = document.getElementById('editCourseLevel')?.value;
                    const category = document.getElementById('editCourseCategory')?.value.trim();
                    const enseignant = document.getElementById('editCourseTeacher')?.value;

                    if (!titre) {
                        Swal.fire('Erreur', 'Le titre du cours est obligatoire', 'error');
                        return;
                    }

                    if (!level) {
                        Swal.fire('Erreur', 'Le niveau est obligatoire', 'error');
                        return;
                    }

                    if (!category) {
                        Swal.fire('Erreur', 'La catégorie est obligatoire', 'error');
                        return;
                    }

                    if (!enseignant) {
                        Swal.fire('Erreur', 'L\'enseignant est obligatoire', 'error');
                        return;
                    }

                    console.log('Soumission du formulaire de modification');
                    form.submit();
                });
            }


            console.log('Initialisation de la gestion de suppression...');
            console.log('Boutons de suppression trouvés:', document.querySelectorAll('.delete-course-btn').length);

            // Ouvrir le modal de suppression
            document.querySelectorAll('.delete-course-btn').forEach(button => {
                console.log('Configuration du bouton de suppression:', button);

                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Clic sur le bouton de suppression');

                    // Récupérer les données directement des attributs data
                    const coursId = this.getAttribute('data-cours-id');
                    const coursTitre = this.getAttribute('data-cours-titre');
                    const nbEtudiants = parseInt(this.getAttribute('data-cours-nb-etudiants')) || 0;
                    const hasSeances = this.getAttribute('data-cours-has-seances') === 'true';

                    console.log('Données de suppression:', {
                        coursId,
                        coursTitre,
                        nbEtudiants,
                        hasSeances
                    });

                    if (!coursId || !coursTitre) {
                        console.error('Données manquantes pour la suppression');
                        Swal.fire('Erreur', 'Impossible de récupérer les informations du cours', 'error');
                        return;
                    }

                    // Appeler la fonction de confirmation
                    confirmSuppression(coursId, coursTitre, hasSeances, nbEtudiants);
                });
            });


            // Fonction de confirmation de suppression
            function confirmSuppression(coursId, coursTitre, hasSeances, nbEtudiants) {
                console.log('Confirmation de suppression pour:', coursTitre);

                // Mettre à jour le modal avec les informations
                const courseNameElement = document.getElementById('courseToDeleteName');
                if (courseNameElement) {
                    courseNameElement.textContent = coursTitre;
                }

                // Afficher les informations sur les étudiants
                const studentsInfo = document.getElementById('studentsInfo');
                if (studentsInfo) {
                    if (nbEtudiants > 0) {
                        studentsInfo.textContent = `Ce cours a ${nbEtudiants} étudiant(s) inscrit(s) qui seront désinscrits.`;
                        studentsInfo.style.display = 'block';
                    } else {
                        studentsInfo.style.display = 'none';
                    }
                }

                // Afficher/masquer l'avertissement des séances
                const seancesWarning = document.getElementById('seancesWarning');
                if (seancesWarning) {
                    if (hasSeances) {
                        seancesWarning.style.display = 'block';
                    } else {
                        seancesWarning.style.display = 'none';
                    }
                }

                // Mettre à jour le formulaire de suppression
                const deleteCourseId = document.getElementById('deleteCourseId');
                if (deleteCourseId) {
                    deleteCourseId.value = coursId;
                    console.log('ID du cours à supprimer défini:', coursId);
                }

                // Ouvrir le modal
                openModal('deleteCourseModal');
            }


            // Gestion de la soumission du formulaire de suppression
            const deleteCourseForm = document.getElementById('deleteCourseForm');
            if (deleteCourseForm) {
                deleteCourseForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const coursId = document.getElementById('deleteCourseId').value;
                    const coursTitre = document.getElementById('courseToDeleteName').textContent;

                    console.log('Soumission du formulaire de suppression pour:', coursTitre, 'ID:', coursId);

                    // Afficher un indicateur de chargement
                    Swal.fire({
                        title: 'Suppression en cours...',
                        text: 'Veuillez patienter',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });


                    this.submit();


                });
            }


            // Empêcher la soumission directe des anciens formulaires de suppression dans le tableau
            document.querySelectorAll('form[onsubmit]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const actionInput = this.querySelector('input[name="action"]');
                    if (actionInput && actionInput.value === 'supprimer_cours') {
                        e.preventDefault();
                        console.log('Ancien formulaire de suppression empêché');
                        return false;
                    }
                });
            });


            const generateReportBtn = document.getElementById('generateReportBtn');
            if (generateReportBtn) {
                generateReportBtn.addEventListener('click', function() {
                    openModal('generateReportModal');
                });
            }


            const confirmGenerateReportBtn = document.getElementById('confirmGenerateReportBtn');
            if (confirmGenerateReportBtn) {
                confirmGenerateReportBtn.addEventListener('click', function() {
                    const course = document.getElementById('reportCourse')?.value;
                    const reportType = document.querySelector('input[name="reportType"]:checked')?.value;
                    const format = document.getElementById('reportFormat')?.value;

                    if (!course) {
                        Swal.fire('Erreur', 'Veuillez sélectionner un cours', 'error');
                        return;
                    }

                    Swal.fire('Succès', `Rapport ${reportType} pour ${course} généré en format ${format}`, 'success');
                    closeModal('generateReportModal');
                });
            }



            const exportDataBtn = document.getElementById('exportDataBtn');
            if (exportDataBtn) {
                exportDataBtn.addEventListener('click', function() {
                    Swal.fire('Succès', 'Données exportées avec succès', 'success');
                });
            }


            console.log('JavaScript initialisé avec succès');
        });
    </script>
</body>

</html>