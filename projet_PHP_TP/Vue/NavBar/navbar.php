<?php
require_once("../../Model/notification.php");

$idUtilisateur = $_SESSION['id'] ?? null;
$prenomUtilisateur = $_SESSION['prenom'] ?? 'Jean';
$nomUtilisateur = $_SESSION['nom'] ?? 'Dupont';
$photoProfil = $_SESSION['photoProfil'] ?? 'default.png';
$role = $_SESSION['role'] ?? 'etudiant';


if (isset($_GET['markAllRead']) && $idUtilisateur) {
    Notification::markAllAsRead($idUtilisateur);
    //sert à recharger la page courante, mais en supprimant les paramètres GET (?markAllRead=1) de l’URL.
    echo "<script>window.location.href='" . strtok($_SERVER["REQUEST_URI"], '?') . "';</script>";
    exit;
}

$notifications = $idUtilisateur ? Notification::findByUser($idUtilisateur) : [];

$nombreNonLues = count(array_filter($notifications, fn($n) => $n['lu'] == 0));
$current_page = basename($_SERVER['PHP_SELF']);

?>
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

    /* Header Styles */
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
        color: var(--primary-color);
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
        color: var(--gray-medium);
    }

    /* Navigation Styles */
    .nav {
        background-color: rgba(2, 33, 83, 0.9);
        padding: 0 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .nav-menu {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
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
        border-bottom: 3px solid var(--accent-color);
    }

    .nav-menu i {
        margin-right: 8px;
        font-size: 1rem;
    }

    /* Main Content */
    .main-content {
        min-height: 100vh;
        width: 100%;
    }

    /* Content Area */
    .content {
        padding: 25px;
    }

    .page-title {
        margin-bottom: 20px;
    }

    .page-title h2 {
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 5px;
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        font-size: 0.9rem;
        color: var(--gray-medium);
        padding: 0;
    }

    .breadcrumb li:not(:last-child)::after {
        content: "/";
        margin: 0 10px;
    }

    /* Styles pour les notifications */
    .nav-icons {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-left: 20px;
    }

    .nav-icon {
        position: relative;
        color: var(--dark-color);
        background: rgba(67, 97, 238, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        cursor: pointer;
        text-decoration: none;
        border: none;
    }

    .nav-icon:hover {
        background: rgba(67, 97, 238, 0.2);
        transform: translateY(-2px);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--danger-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .user-profile-nav {
        display: flex;
        align-items: center;
        color: var(--dark-color);
        font-weight: 500;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        background: rgba(67, 97, 238, 0.1);
        transition: var(--transition);
        font-size: 0.85rem;
        cursor: pointer;
        text-decoration: none;
        border: none;
    }

    .user-profile-nav:hover {
        background: rgba(67, 97, 238, 0.15);
        color: var(--dark-color);
        text-decoration: none;
    }

    .user-profile-nav i {
        margin-right: 6px;
        font-size: 1rem;
    }

    .dropdown-menu {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 0.5rem;
        border: 1px solid var(--gray-light);
    }

    .dropdown-item {
        border-radius: 8px;
        padding: 0.7rem 1rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
    }

    .dropdown-item:hover {
        background: rgba(67, 97, 238, 0.1);
    }

    .dropdown-item i {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    /* Styles pour les notifications */
    .notification-dropdown {
        width: 380px;
        max-width: 90vw;
        padding: 0;
        margin-top: 0.5rem;
    }

    .notification-header {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-title {
        font-weight: 600;
        color: var(--secondary-color);
        margin: 0;
        font-size: 1rem;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
    }

    .notification-action-btn {
        background: none;
        border: none;
        color: var(--primary-color);
        font-size: 0.8rem;
        cursor: pointer;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        transition: var(--transition);
    }

    .notification-action-btn:hover {
        background: rgba(67, 97, 238, 0.1);
    }

    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 0.8rem 1rem;
        border-bottom: 1px solid var(--gray-light);
        transition: var(--transition);
        cursor: pointer;
        position: relative;
    }

    .notification-item:hover {
        background: rgba(67, 97, 238, 0.05);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item.unread {
        background: rgba(67, 97, 238, 0.03);
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--primary-color);
        border-radius: 0 2px 2px 0;
    }

    .notification-content {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
    }

    .notification-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notification-info {
        flex: 1;
    }

    .notification-message {
        font-weight: 500;
        color: var(--secondary-color);
        margin-bottom: 0.2rem;
        font-size: 0.85rem;
        line-height: 1.3;
    }

    .notification-preview {
        color: var(--gray-medium);
        font-size: 0.8rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notification-time {
        color: var(--gray-medium);
        font-size: 0.75rem;
        margin-top: 0.3rem;
    }

    .notification-info .notification-icon {
        background: rgba(67, 97, 238, 0.1);
        color: var(--primary-color);
    }

    .notification-warning .notification-icon {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .notification-success .notification-icon {
        background: rgba(6, 214, 160, 0.1);
        color: var(--success-color);
    }

    .notification-danger .notification-icon {
        background: rgba(239, 71, 111, 0.1);
        color: var(--danger-color);
    }

    .notification-footer {
        padding: 0.8rem;
        text-align: center;
        border-top: 1px solid var(--gray-light);
    }

    /* Modals personnalisés */
    .notification-modal .modal-dialog {
        max-width: 600px;
    }

    .notification-modal .modal-header {
        background: var(--light-color);
        border-bottom: 1px solid var(--gray-light);
        padding: 1.2rem 1.5rem;
    }

    .notification-modal .modal-title {
        font-weight: 600;
        color: var(--secondary-color);
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .modal-title-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .notification-modal .modal-body {
        padding: 1.5rem;
    }

    .notification-detail-content {
        line-height: 1.6;
        color: var(--dark-color);
    }

    .notification-detail-content p {
        margin-bottom: 1rem;
    }

    .notification-detail-content ul {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }

    .notification-detail-content li {
        margin-bottom: 0.5rem;
    }

    .notification-modal .modal-footer {
        border-top: 1px solid var(--gray-light);
        padding: 1rem 1.5rem;
    }

    .notification-time-large {
        color: var(--gray-medium);
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    /* Correction pour le dropdown */
    .dropdown {
        position: static;
    }

    .dropdown-menu.show {
        position: absolute;
        right: 1rem;
        left: auto;
        transform: translateX(0) !important;
    }

    @media (max-width: 768px) {
        .notification-dropdown {
            width: 320px;
            right: 1rem;
            left: auto;
        }

        .dropdown-menu.show {
            right: 0.5rem;
        }

        .nav-menu {
            flex-direction: column;
        }

        .nav-menu a {
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .notification-dropdown {
            width: 280px;
            right: 0.5rem;
        }

        .nav-icons {
            gap: 0.5rem;
        }

        .dropdown-menu.show {
            right: 0.5rem;
            left: auto;
        }

        .header {
            padding: 15px 20px;
        }
    }
    
</style>
<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <h1>Tableau de bord</h1>
    </div>

    <div class="header-right">
        <div class="nav-icons">


            <div class="dropdown">
                <button class="nav-icon dropdown-toggle" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php if ($nombreNonLues > 0): ?>
                        <span class="notification-badge"><?= $nombreNonLues ?></span>
                    <?php endif; ?>
                </button>

                <div class="dropdown-menu notification-dropdown dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <div class="notification-header d-flex justify-content-between align-items-center">
                        <h6 class="notification-title mb-0">Notifications</h6>
                        <?php if ($nombreNonLues > 0): ?>
                            <a href="?markAllRead=1" class="notification-action-btn">
                                <i class="fas fa-check-double me-1"></i>Tout lire
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="notification-list">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center p-3 text-muted">Aucune notification</div>
                        <?php else: ?>
                            <?php $notificationsAffichees = array_slice($notifications, 0, 4);
                            foreach ($notificationsAffichees as $notif):  ?>
                                <?php
                                $icon = $notif['icone'] ?? 'fa-bell';
                                $classLu = $notif['lu'] == 0 ? 'unread' : '';
                                ?>
                                <div class="notification-item notification-<?= htmlspecialchars($notif['icone']) ?> <?= $classLu ?>">
                                    <div class="notification-content">
                                        <div class="notification-icon">
                                            <i class="fas <?= $icon ?>"></i>
                                        </div>
                                        <div class="notification-info">
                                            <div class="notification-message"><?= htmlspecialchars($notif['titre']) ?></div>
                                            <div class="notification-preview"><?= htmlspecialchars($notif['message']) ?></div>
                                            <div class="notification-time"><?= date('d/m/Y H:i', strtotime($notif['date_creation'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="notification-footer text-center">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#allNotificationsModal">
                            <i class="fas fa-list me-1"></i>Voir toutes les notifications
                        </button>
                    </div>
                </div>
            </div>


            <div class="modal fade notification-modal" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title d-flex align-items-center gap-2">
                                <i class="fas fa-bell"></i> Toutes les notifications
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>

                        <div class="modal-body">
                            <?php if (empty($notifications)): ?>
                                <div class="text-center p-3 text-muted">Aucune notification</div>
                            <?php else: ?>
                                <div class="notification-list">
                                    <?php foreach ($notifications as $notif): ?>
                                        <?php
                                        $icon = $notif['icone'] ?? 'fa-bell';
                                        $classLu = $notif['lu'] == 0 ? 'unread' : '';
                                        ?>
                                        <div class="notification-item notification-<?= htmlspecialchars($notif['icone']) ?> <?= $classLu ?>">
                                            <div class="notification-content">
                                                <div class="notification-icon">
                                                    <i class="fas <?= $icon ?>"></i>
                                                </div>
                                                <div class="notification-info">
                                                    <div class="notification-message"><?= htmlspecialchars($notif['titre']) ?></div>
                                                    <div class="notification-preview"><?= htmlspecialchars($notif['message']) ?></div>
                                                    <div class="notification-time"><?= date('d/m/Y H:i', strtotime($notif['date_creation'])) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="modal-footer">
                            <a href="?markAllRead=1" class="btn btn-primary">
                                <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="dropdown">
                <button class="user-profile-nav dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($prenomUtilisateur . ' ' . $nomUtilisateur) ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="profil.php"><i class="fas fa-user"></i> Mon profil</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="../../Model/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="nav">
    <ul class="nav-menu">
        <?php if ($role === 'etudiant'): ?>
            <li><a href="../Etudiants/InterfaceAccueil.php" class="<?= $current_page == 'InterfaceAccueil.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Tableau de Bord</a></li>
            <li><a href="../Etudiants/ParticipationAuxCours.php" class="<?= $current_page == 'ParticipationAuxCours.php' ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Mes Cours</a></li>
            <li><a href="../Etudiants/historiquePresences.php" class="<?= $current_page == 'Historique.php' ? 'active' : '' ?>"><i class="fas fa-history"></i> Historique</a></li>
            <li><a href="../Profil/GestionDuProfil.php" class="<?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Mon Profil</a></li>

        <?php elseif ($role === 'enseignant'): ?>
            <li><a href="../Enseignant/TableaudeBordEnseignant.php" class="<?= $current_page == 'TableaudeBordEnseignant.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Tableau de Bord</a></li>
            <li><a href="../Enseignant/GestionDesCoursEns.php" class="<?= $current_page == 'GestionDesCoursEns.php' ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> Mes Cours</a></li>
            <li><a href="gestion_etudiants.php" class="<?= $current_page == 'gestion_etudiants.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Gestion Étudiants</a></li>
            <li><a href="../Enseignant/GestionDesPrésencesEns.php" class="<?= $current_page == 'GestionDesPrésencesEns.php' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> Présences</a></li>
            <li><a href="statistiques.php" class="<?= $current_page == 'statistiques.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Statistiques</a></li>
            <li><a href="exports.php" class="<?= $current_page == 'exports.php' ? 'active' : '' ?>"><i class="fas fa-file-export"></i> Exports</a></li>
            <li><a href="../Profil/GestionDuProfil.php" class="<?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Mon Profil</a></li>

        <?php elseif ($role === 'admin'): ?>
            <li><a href="../Administrateur/DashboardAdmin.php" class="<?= $current_page == 'DashboardAdmin.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Tableau de Bord</a></li>
            <li><a href="../Administrateur/ListeUtilisateursAd.php" class="<?= $current_page == 'ListeUtilisateursAd.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Gestion Utilisateurs</a></li>
            <li><a href="../Administrateur/suivi&controleAd.php" class="<?= $current_page == 'suivi&controleAd.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Suivi & Contrôle</a></li>
            <li><a href="../Profil/GestionDuProfil.php" class="<?= $current_page == 'GestionDuProfil.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Mon Profil</a></li>
        <?php endif; ?>
    </ul>
</div>
