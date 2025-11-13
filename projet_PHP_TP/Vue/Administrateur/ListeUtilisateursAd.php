<?php
session_start();
include("../../Model/utilisateur.php");
include("../../Model/enseignant.php");
include("../../Model/etudiant.php");
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
$stats = utilisateur::getStatsByAdmin();
$activeTab = $_POST['activeTab'] ?? 'students';
$activeTab = $_POST['activeTab'] ?? 'students';

$enseignants = Enseignant::getAllEnseignants();
$etudiants = etudiant::getAllEtudiants();
$studentSearch = $_POST['studentSearch'] ?? '';
$studentStatus = $_POST['studentStatus'] ?? 'all';
$etudiants = Utilisateur::getEtudiantsFiltrés($studentSearch, $studentStatus);

$teacherSearch = $_POST['teacherSearch'] ?? '';
$teacherStatus = $_POST['teacherStatus'] ?? 'all';
$enseignants = Utilisateur::getEnseignantsFiltrés($teacherSearch, $teacherStatus);
$snackbar = $_SESSION['snackbar'] ?? null;
unset($_SESSION['snackbar']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduAdmin - Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            gap: 8px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #3ab8e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 201, 240, 0.3);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e3166a;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(247, 37, 133, 0.3);
        }

        .btn-danger {
            background-color: #e63946;
            color: white;
        }

        .btn-danger:hover {
            background-color: #d32f3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 57, 70, 0.3);
        }

        /* Search and Filters */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            background-color: white;
            cursor: pointer;
        }

        /* Modals - Version améliorée */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 500px;
            max-width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            animation: slideUp 0.3s ease forwards;
            overflow: hidden;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            transition: transform 0.3s;
        }

        .modal-close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #f9fafb;
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

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Confirmation Modal */
        .confirmation-modal .modal-content {
            width: 400px;
        }

        .confirmation-icon {
            text-align: center;
            font-size: 3rem;
            color: var(--warning);
            margin-bottom: 15px;
        }

        .confirmation-text {
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 0.9rem;
            color: var(--gray);
            margin: 0;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1050;
            backdrop-filter: blur(3px);
        }

        .modal-content-custom {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-header-custom {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .modal-title-custom {
            font-weight: 600;
            color: #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-body-custom {
            padding: 1.5rem;
        }

        .modal-footer-custom {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .reason-textarea {
            resize: vertical;
            min-height: 100px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .reason-textarea:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            outline: 0;
        }

        .btn-cancel {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-1px);
        }

        .btn-confirm {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-confirm:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-1px);
        }


        .modal-overlay.show {
            display: flex;
        }

        .modal-overlay.show .modal-content-custom {
            transform: translateY(0);
            opacity: 1;
        }

        @media (max-width: 576px) {
            .modal-content-custom {
                margin: 0 1rem;
            }
        }

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

            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 10px;
                width: calc(100% - 20px);
            }

            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .content {
                padding: 20px;
            }

            .header {
                padding: 15px 20px;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn-sm {
                padding: 8px 12px;
                justify-content: center;
            }
        }
    </style>
</head>

<body>


    <!-- Navigation -->
    <?php include("../NavBar/navbar.php") ?>

    <!-- Content Area -->
    <div class="content">

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #4cc9f0;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($stats['totalEtudiant']) ?></h3>
                    <p>Étudiants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #4361ee;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($stats['totalEnseignant']) ?></h3>
                    <p>Enseignants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #3a0ca3;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars($stats['totalAdmin']) ?></h3>
                    <p>Administrateurs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background-color: #f72585;">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= htmlspecialchars(utilisateur::getEnAttenteCount()) ?></h3>
                    <p>En attente</p>
                </div>
            </div>
        </div>

        <div class="tabs">

            <div class="tab <?= ($activeTab === 'students') ? 'active' : '' ?>" data-tab="students">Étudiants</div>
            <div class="tab <?= ($activeTab === 'teachers') ? 'active' : '' ?>" data-tab="teachers">Enseignants</div>

        </div>

        <!-- Étudiants Tab -->
        <div class="tab-content <?= ($activeTab === 'students') ? 'active' : '' ?>" id="students-tab">

            <div class="table-container">
                <form method="POST" id="studentFilterForm">
                    <div class="table-controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="studentSearch" value="<?= htmlspecialchars($studentSearch) ?>" placeholder="Rechercher un étudiant...">
                        </div>
                        <div class="filters">
                            <select name="studentStatus" class="filter-select" onchange="document.getElementById('studentFilterForm').submit()">
                                <option value="all" <?= $studentStatus === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                                <option value="Actif" <?= $studentStatus === 'Actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="Inactif" <?= $studentStatus === 'Inactif' ? 'selected' : '' ?>>Inactif</option>
                            </select>
                        </div>
                        <input type="hidden" name="activeTab" id="activeTabInput" value="<?= $activeTab ?>">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                    </div>
                </form>

                <div class="table-header">
                    <div class="table-title">Liste des Étudiants</div>

                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        ?>
                        <?php foreach ($etudiants as $et): ?>
                            <tr data-status="<?= $et['isActive'] ?>">
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($et['prenom'] . ' ' . $et['nom']) ?></td>
                                <td><?= htmlspecialchars($et['email']) ?></td>
                                <td>
                                    <span class="badge <?= $et['isActive'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $et['isActive'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <!-- <td>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="toggleStatus(<?= $et['id'] ?>, <?= $et['isActive'] ?>)">
                                        <?= $et['isActive'] ? '<i class="fas fa-toggle-off"></i> Désactiver' : '<i class="fas fa-toggle-on"></i> Activer' ?>
                                    </button>
                                </td> -->
                                <td>
                                    <?php if ($et['isActive']): ?>
                                        <!-- Bouton Désactiver -->
                                        <button type="button" class="btn btn-sm btn-warning" onclick="openReasonModal(<?= $et['id'] ?>, '<?= htmlspecialchars($et['prenom'] . ' ' . $et['nom']) ?>')">
                                            <i class="fas fa-toggle-off"></i> Désactiver
                                        </button>
                                    <?php else: ?>
                                        <!-- Activer -->
                                        <form method="POST" action="../../Controller/utilisateurController.php" class="activateForm">
                                            <input type="hidden" name="action" value="changerStatut">
                                            <input type="hidden" name="statusAction" value="activate">
                                            <input type="hidden" name="id" value="<?= $et['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-toggle-on"></i> Activer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>


                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enseignants Tab -->
        <div class="tab-content <?= ($activeTab === 'teachers') ? 'active' : '' ?>" id="teachers-tab">
            <div class="table-container">
                <form method="POST" id="teacherFilterForm">
                    <div class="table-controls">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="teacherSearch" value="<?= htmlspecialchars($teacherSearch) ?>" placeholder="Rechercher un enseignant...">
                        </div>
                        <div class="filters">
                            <select name="teacherStatus" class="filter-select" onchange="document.getElementById('teacherFilterForm').submit()">
                                <option value="all" <?= $teacherStatus === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                                <option value="Actif" <?= $teacherStatus === 'Actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="Inactif" <?= $teacherStatus === 'Inactif' ? 'selected' : '' ?>>Inactif</option>
                            </select>
                        </div>
                        <input type="hidden" name="activeTab" id="activeTabInput" value="<?= $activeTab ?>">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                    </div>
                </form>

                <div class="table-header">
                    <div class="table-title">Liste des Enseignants</div>

                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>

                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        ?>
                        <?php foreach ($enseignants as $e): ?>
                            <tr data-status="<?= $et['isActive'] ?>">
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></td>
                                <td><?= htmlspecialchars($e['email']) ?></td>
                                <td>
                                    <span class="badge <?= $e['isActive'] ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $e['isActive'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($et['isActive']): ?>
                                        <!-- Bouton Désactiver -->
                                        <button type="button" class="btn btn-sm btn-warning" onclick="openReasonModal(<?= $et['id'] ?>, '<?= htmlspecialchars($et['prenom'] . ' ' . $et['nom']) ?>')">
                                            <i class="fas fa-toggle-off"></i> Désactiver
                                        </button>
                                    <?php else: ?>
                                        <!-- Activer -->
                                        <form method="POST" action="../../Controller/utilisateurController.php" class="activateForm">

                                            <input type="hidden" name="action" value="changerStatut">
                                            <input type="hidden" name="statusAction" value="activate">
                                            <input type="hidden" name="id" value="<?= $et['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-toggle-on"></i> Activer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Modal pour raison -->
    <div class="modal-overlay" id="reasonModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title-custom">
                    <i class="fas fa-exclamation-triangle"></i>
                    Désactiver l'utilisateur <span id="modalUserName" class="fw-bold"></span>
                </h5>
            </div>
            <form method="POST" action="../../Controller/utilisateurController.php">
                <div class="modal-body-custom">
                    <input type="hidden" name="action" value="changerStatut">
                    <input type="hidden" name="statusAction" value="deactivate">
                    <input type="hidden" name="id" id="modalUserId">

                    <div class="mb-3">
                        <label for="reasonText" class="form-label">Raison de la désactivation :</label>
                        <textarea id="reasonText" name="reason" class="form-control reason-textarea" rows="4" placeholder="Veuillez saisir la raison de la désactivation..." required></textarea>
                      
                    </div>
                </div>
                <div class="modal-footer-custom d-flex justify-content-between">
                    <button type="button" class="btn btn-cancel" onclick="closeReasonModal()">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-confirm">
                        <i class="fas fa-check me-1"></i> Confirmer la désactivation
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php if ($snackbar): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div id="snackbarToast"
                class="toast align-items-center text-bg-<?= htmlspecialchars($snackbar['type']) ?> border-0"
                role="alert" aria-live="assertive" aria-atomic="true"
                data-bs-autohide="true" data-bs-delay="3500">
                <div class="d-flex">
                    <div class="toast-body">
                        <?= htmlspecialchars($snackbar['message']) ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <?php include("../../Footer/footer.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', () => {
            let activeTab = localStorage.getItem('activeTab');
            if (activeTab !== 'students' && activeTab !== 'teachers') {
                activeTab = 'students';
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
                // Retirer active sur tous les onglets
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                const tab = document.querySelector(`.tab[data-tab="${tabId}"]`);
                if (tab) tab.classList.add('active');

                // Retirer active sur tous les contenus
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                const tabContent = document.getElementById(tabId + '-tab');
                if (tabContent) tabContent.classList.add('active');
            }

        });

        // Modal functionality
        function openReasonModal(userId, userName) {
            document.getElementById('modalUserId').value = userId;
            document.getElementById('modalUserName').innerText = userName;

            const modal = document.getElementById('reasonModal');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                document.getElementById('reasonText').focus();
            }, 300);
        }

        function closeReasonModal() {
            const modal = document.getElementById('reasonModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';


            document.getElementById('reasonText').value = '';
        }

        document.getElementById('reasonModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReasonModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReasonModal();
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('snackbarToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
        document.querySelectorAll('.activateForm').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const userName = "<?= htmlspecialchars($et['prenom'] . ' ' . $et['nom']) ?>";

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: `Voulez-vous vraiment activer ${userName} ?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, activer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>

</html>