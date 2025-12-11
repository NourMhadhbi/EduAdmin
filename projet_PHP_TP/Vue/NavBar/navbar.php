<?php
require_once("../../Model/notification.php");

$idUtilisateur = $_SESSION['id'] ?? null;
$prenomUtilisateur = $_SESSION['prenom'] ?? '';
$nomUtilisateur = $_SESSION['nom'] ?? '';
$photoProfil = $_SESSION['photoProfil'] ?? 'default.png';
$role = $_SESSION['role'] ?? 'etudiant';

// Logic to mark notifications as read
if (isset($_GET['markAllRead']) && $idUtilisateur) {
    Notification::markAllAsRead($idUtilisateur);
    echo "<script>window.location.href='" . strtok($_SERVER["REQUEST_URI"], '?') . "';</script>";
    exit;
}

$notifications = $idUtilisateur ? Notification::findByUser($idUtilisateur) : [];
$nombreNonLues = count(array_filter($notifications, fn($n) => $n['lu'] == 0));
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Include Global Theme -->
<link rel="stylesheet" href="../../Css/global-theme.css">

<!-- Top Bar (Removed as per request) -->


<!-- Main Navbar (White, Fixed) -->
<nav class="navbar">
    <!-- Logo/Brand -->
    <a href="#" class="navbar-brand">
        <i class="fas fa-graduation-cap"></i>
        <span>Gestion Presence</span>
    </a>

    <!-- Navigation Menu (Centered) -->
    <ul class="nav-menu">
        <?php if ($role === 'etudiant'): ?>
            <li>
                <a href="../Profil/GestionDuProfil.php" class="nav-link <?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>">
                    Mon Profil
                </a>
            </li>
            <li>
                <a href="../Etudiants/ParticipationAuxCours.php" class="nav-link <?= $current_page == 'ParticipationAuxCours.php' ? 'active' : '' ?>">
                    Mes Cours
                </a>
            </li>
            <li>
                <a href="../Etudiants/inscritCours.php" class="nav-link <?= $current_page == 'inscritCours.php' ? 'active' : '' ?>">
                    Cours Suivis
                </a>
            </li>
            <li>
                <a href="../Etudiants/historiquePresences.php" class="nav-link <?= $current_page == 'historiquePresences.php' ? 'active' : '' ?>">
                    Historique
                </a>
            </li>
            <li>
                <a href="../Etudiants/InterfaceAccueil.php" class="nav-link <?= $current_page == 'InterfaceAccueil.php' ? 'active' : '' ?>">
                    Tableau de Bord
                </a>
            </li>

        <?php elseif ($role === 'enseignant'): ?>
            <li>
                <a href="../Profil/GestionDuProfil.php" class="nav-link <?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>">
                    Mon Profil
                </a>
            </li>
            <li>
                <a href="../Enseignant/GestionDesCoursEns.php" class="nav-link <?= $current_page == 'GestionDesCoursEns.php' ? 'active' : '' ?>">
                    Mes Cours
                </a>
            </li>
            <li>
                <a href="../Enseignant/GestionDesPrésencesEns.php" class="nav-link <?= $current_page == 'GestionDesPrésencesEns.php' ? 'active' : '' ?>">
                    Présences
                </a>
            </li>
            <li>
                <a href="../Enseignant/TableaudeBordEnseignant.php" class="nav-link <?= $current_page == 'TableaudeBordEnseignant.php' ? 'active' : '' ?>">
                    Tableau de Bord
                </a>
            </li>

        <?php elseif ($role === 'admin'): ?>
            <li>
                <a href="../Profil/GestionDuProfil.php" class="nav-link <?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>">
                    Mon Profil
                </a>
            </li>
            <li>
                <a href="../Administrateur/ListeUtilisateursAd.php" class="nav-link <?= $current_page == 'ListeUtilisateursAd.php' ? 'active' : '' ?>">
                    Utilisateurs
                </a>
            </li>
            <li>
                <a href="../Administrateur/suivi&controleAd.php" class="nav-link <?= $current_page == 'suivi&controleAd.php' ? 'active' : '' ?>">
                    Suivi
                </a>
            </li>
            <li>
                <a href="../Administrateur/DashboardAdmin.php" class="nav-link <?= $current_page == 'DashboardAdmin.php' ? 'active' : '' ?>">
                    Tableau de Bord
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Right Side Actions (Notifications & Profile) -->
    <div class="navbar-actions">
        
        <!-- Notifications -->
        <div class="dropdown">
            <button class="nav-icon" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="far fa-bell"></i>
                <?php if ($nombreNonLues > 0): ?>
                    <span class="notification-badge"><?= $nombreNonLues ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                 <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h6 class="mb-0 fw-bold">Notifications</h6>
                    <?php if ($nombreNonLues > 0): ?>
                        <a href="?markAllRead=1" class="text-primary text-decoration-none small">Tout lire</a>
                    <?php endif; ?>
                </div>
                <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center p-3 text-muted small">Aucune notification</div>
                    <?php else: ?>
                        <?php foreach (array_slice($notifications, 0, 4) as $notif): ?>
                            <div class="p-3 border-bottom notification-item <?= $notif['lu'] == 0 ? 'bg-light' : '' ?>">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="text-primary"><i class="fas <?= htmlspecialchars($notif['icone'] ?? 'fa-info-circle') ?>"></i></div>
                                    <div>
                                        <div class="small fw-bold"><?= htmlspecialchars($notif['titre']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($notif['message']) ?></div>
                                        <div class="small text-muted mt-1" style="font-size: 0.75rem;"><?= date('d/m H:i', strtotime($notif['date_creation'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="p-2 text-center border-top">
                    <button class="btn btn-link btn-sm text-decoration-none" data-bs-toggle="modal" data-bs-target="#allNotificationsModal">Voir tout</button>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="dropdown">
            <button class="user-profile-nav" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../../Assets/Images/known/<?= htmlspecialchars($photoProfil) ?>" alt="Profile">
                <span><?= htmlspecialchars($prenomUtilisateur) ?></span>
                <i class="fas fa-chevron-down ms-1" style="font-size: 0.8rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../Profil/GestionDuProfil.php"><i class="fas fa-user me-2 text-muted"></i> Mon Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../../Model/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Notifications Modal -->
<div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toutes les notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <?php if (empty($notifications)): ?>
                    <div class="text-center p-4 text-muted">Aucune notification</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><i class="fas <?= htmlspecialchars($notif['icone'] ?? 'fa-bell') ?> me-2 text-primary"></i> <?= htmlspecialchars($notif['titre']) ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($notif['date_creation'])) ?></small>
                                </div>
                                <p class="mb-1 small"><?= htmlspecialchars($notif['message']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="?markAllRead=1" class="btn btn-primary">Tout marquer comme lu</a>
            </div>
        </div>
    </div>
</div>