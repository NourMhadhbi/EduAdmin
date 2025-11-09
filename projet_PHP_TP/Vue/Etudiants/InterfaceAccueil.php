<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../Authentification/connexion.php");

    exit;
}

if (strtolower($_SESSION['role']) !== 'etudiant') {
    header("Location: ../Authentification/connexion.php");
    exit;
}

$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'];
$photoProfil = $_SESSION['photoProfil'] ?? 'default_etudiant.png';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Espace Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="../../Css/accueilEtudiant.css" />
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



        /* Styles pour le reste de la page */
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
            /* background: linear-gradient(to right, var(--primary-color), var(--accent-color)); */
            background-color: rgba(2, 33, 83, 0.9);

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
            /* background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); */
            background-color: rgba(2, 33, 83, 0.9);
            ;
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

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: var(--transition);
            border-left: 3px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-card.present {
            border-left-color: var(--success-color);
        }

        .stat-card.absent {
            border-left-color: var(--danger-color);
        }

        .stat-card.rate {
            border-left-color: var(--primary-color);
        }

        .stat-card.courses {
            border-left-color: var(--accent-color);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.2rem;
        }

        .stat-card.present .stat-icon {
            background: rgba(6, 214, 160, 0.1);
            color: var(--success-color);
        }

        .stat-card.absent .stat-icon {
            background: rgba(239, 71, 111, 0.1);
            color: var(--danger-color);
        }

        .stat-card.rate .stat-icon {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }

        .stat-card.courses .stat-icon {
            background: rgba(255, 209, 102, 0.1);
            color: var(--warning-color);
        }

        .stat-number {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            line-height: 1.2;
        }

        .stat-card.present .stat-number {
            color: var(--success-color);
        }

        .stat-card.absent .stat-number {
            color: var(--danger-color);
        }

        .stat-card.rate .stat-number {
            color: var(--primary-color);
        }

        .stat-card.courses .stat-number {
            color: var(--warning-color);
        }

        .stat-label {
            color: var(--gray-medium);
            font-weight: 500;
            font-size: 0.8rem;
        }

        .presence-status {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .present {
            background: rgba(6, 214, 160, 0.12);
            color: #155724;
        }

        .pending {
            background: rgba(255, 209, 102, 0.12);
            color: #856404;
        }

        .absent {
            background: rgba(239, 71, 111, 0.12);
            color: #721c24;
        }

        .table {
            font-size: 0.85rem;
        }

        .table thead th {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.35);
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

        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
            font-size: 0.85rem;
        }



        @media (max-width: 768px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }


        }

        @media (max-width: 576px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }


        }
    </style>
</head>

<body>

    <?php include("../NavBar/navbar.php") ?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content">
            <div class="page-title">
                <h2>Tableau de Bord Étudiant</h2>
                <ul class="breadcrumb">
                    <li>Accueil</li>
                    <li>Tableau de Bord</li>
                </ul>
            </div>

            <!-- Cartes de statistiques -->
            <div class="stats-overview">
                <div class="stat-card rate">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number">85%</div>
                    <div class="stat-label">Taux de présence</div>
                </div>
                <div class="stat-card present">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-number">42</div>
                    <div class="stat-label">Présences</div>
                </div>
                <div class="stat-card absent">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-number">8</div>
                    <div class="stat-label">Absences</div>
                </div>
                <div class="stat-card courses">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Cours suivis</div>
                </div>
            </div>

            <!-- Cours du jour -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-calendar-alt me-2"></i> Mes cours du jour
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Cours</th>
                                            <th>Heure</th>
                                            <th>Enseignant</th>
                                            <th>Statut</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Développement Web Dynamique</td>
                                            <td>09:00 - 11:00</td>
                                            <td>Prof. Martin</td>
                                            <td><span class="presence-status present"><i class="fas fa-check me-1"></i>Présent</span></td>
                                            <td><button class="btn btn-sm btn-outline-primary" disabled>Marqué</button></td>
                                        </tr>
                                        <tr>
                                            <td>Base de données avancées</td>
                                            <td>11:30 - 13:30</td>
                                            <td>Prof. Dubois</td>
                                            <td><span class="presence-status pending"><i class="fas fa-clock me-1"></i>En attente</span></td>
                                            <td><a href="participation.html" class="btn btn-sm btn-primary">Marquer présence</a></td>
                                        </tr>
                                        <tr>
                                            <td>Intelligence Artificielle</td>
                                            <td>14:30 - 16:30</td>
                                            <td>Prof. Lefebvre</td>
                                            <td><span class="presence-status absent"><i class="fas fa-times me-1"></i>Non commencé</span></td>
                                            <td><button class="btn btn-sm btn-outline-secondary" disabled>Indisponible</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Navigation rapide -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-rocket me-2"></i> Navigation rapide
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="participation.html" class="btn btn-primary">
                                    <i class="fas fa-camera me-2"></i>Marquer présence
                                </a>
                                <a href="presences.html" class="btn btn-outline-primary">
                                    <i class="fas fa-history me-2"></i>Historique des présences
                                </a>
                                <a href="profil.html" class="btn btn-outline-primary">
                                    <i class="fas fa-user-edit me-2"></i>Mon profil
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Prochain cours -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <i class="fas fa-clock me-2"></i> Prochain cours
                        </div>
                        <div class="card-body">
                            <h6>Base de données avancées</h6>
                            <p class="mb-1"><strong>Prof.:</strong> Dubois</p>
                            <p class="mb-1"><strong>Heure:</strong> 11:30 - 13:30</p>
                            <p class="mb-0"><strong>Salle:</strong> Virtuelle - Teams</p>
                            <div class="mt-2">
                                <span class="badge bg-warning">Commence dans 45 min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour notification détaillée -->
    <div class="modal fade notification-modal" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">
                        <div class="modal-title-icon notification-info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <div id="modalNotificationTitle">Titre de la notification</div>
                            <div class="notification-time-large" id="modalNotificationTime">Il y a 2 heures</div>
                        </div>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="notification-detail-content" id="modalNotificationContent">
                        <p>Contenu détaillé de la notification apparaîtra ici.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                    <button type="button" class="btn btn-outline-primary mark-as-read-btn">
                        <i class="fas fa-check me-2"></i>Marquer comme lu
                    </button>
                    <button type="button" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Pied de page -->
   <?php include("../../Footer/footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script>
        // Données des notifications
        const notificationsData = {
            1: {
                title: "Mise à jour de la photo de profil",
                time: "Il y a 2 heures",
                type: "info",
                content: `
                    <p>Votre photo de référence doit être mise à jour pour améliorer la précision du système de reconnaissance faciale.</p>
                    <p><strong>Recommandations :</strong></p>
                    <ul>
                        <li>Utilisez une photo récente et bien éclairée</li>
                        <li>Assurez-vous que votre visage est bien visible</li>
                        <li>Évitez les accessoires qui cachent votre visage</li>
                    </ul>
                    <p>Vous pouvez mettre à jour votre photo depuis la page de votre profil.</p>
                `
            },
            2: {
                title: "Absences non justifiées",
                time: "Il y a 1 jour",
                type: "warning",
                content: `
                    <p>Vous avez 2 absences non justifiées dans le cours d'Intelligence Artificielle.</p>
                    <p><strong>Détails des absences :</strong></p>
                    <ul>
                        <li>10 mai 2023 - Séance de 14h30</li>
                        <li>3 mai 2023 - Séance de 14h30</li>
                    </ul>
                    <p>Veuillez justifier ces absences auprès de votre enseignant dans les plus brefs délais.</p>
                    <p>En cas de problème, contactez le service administratif.</p>
                `
            },
            3: {
                title: "Excellent taux de présence",
                time: "Il y a 3 jours",
                type: "success",
                content: `
                    <p>Félicitations ! Votre taux de présence est de 85% ce semestre.</p>
                    <p><strong>Statistiques détaillées :</strong></p>
                    <ul>
                        <li>Développement Web Dynamique : 90%</li>
                        <li>Base de données avancées : 85%</li>
                        <li>Intelligence Artificielle : 75%</li>
                    </ul>
                    <p>Continuez vos efforts ! Votre assiduité est remarquable.</p>
                    <p>N'oubliez pas que la régularité est essentielle pour réussir votre année.</p>
                `
            },
            4: {
                title: "Problème de reconnaissance faciale",
                time: "Il y a 5 jours",
                type: "danger",
                content: `
                    <p>Votre dernière tentative de reconnaissance faciale a échoué.</p>
                    <p><strong>Détails de l'incident :</strong></p>
                    <ul>
                        <li>Date : 8 mai 2023 à 11h35</li>
                        <li>Cours : Base de données avancées</li>
                        <li>Raison : Qualité d'image insuffisante</li>
                    </ul>
                    <p><strong>Solutions recommandées :</strong></p>
                    <ul>
                        <li>Vérifiez l'éclairage de votre environnement</li>
                        <li>Assurez-vous que votre caméra fonctionne correctement</li>
                        <li>Évitez les reflets et les ombres sur votre visage</li>
                    </ul>
                    <p>Si le problème persiste, contactez le support technique.</p>
                `
            }
        };

        // Gestion des modals
        const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
        const allNotificationsModal = new bootstrap.Modal(document.getElementById('allNotificationsModal'));

        // Ouvrir le modal de détail d'une notification
        function openNotificationModal(notificationId) {
            const notification = notificationsData[notificationId];
            if (notification) {
                document.getElementById('modalNotificationTitle').textContent = notification.title;
                document.getElementById('modalNotificationTime').textContent = notification.time;
                document.getElementById('modalNotificationContent').innerHTML = notification.content;

                // Mettre à jour l'icône
                const icon = document.querySelector('#notificationModal .modal-title-icon');
                icon.className = `modal-title-icon notification-${notification.type}`;
                icon.innerHTML = `<i class="fas fa-${getIconForType(notification.type)}"></i>`;

                // Mettre à jour le bouton "Marquer comme lu"
                const markAsReadBtn = document.querySelector('.mark-as-read-btn');
                markAsReadBtn.onclick = function() {
                    markAsRead(notificationId);
                    notificationModal.hide();
                };

                // Marquer comme lu si c'est une notification non lue
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem && notificationItem.classList.contains('unread')) {
                    markAsRead(notificationId);
                }

                notificationModal.show();
            }
        }

        // Marquer une notification comme lue
        function markAsRead(notificationId) {
            const notificationItems = document.querySelectorAll(`[data-notification-id="${notificationId}"]`);
            notificationItems.forEach(item => {
                item.classList.remove('unread');
            });
            updateBadgeCount();
        }

        // Marquer toutes les notifications comme lues
        function markAllAsRead() {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateBadgeCount();
        }

        // Mettre à jour le badge de compteur
        function updateBadgeCount() {
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            const badge = document.querySelector('.notification-badge');
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }

        // Obtenir l'icône selon le type
        function getIconForType(type) {
            const icons = {
                'info': 'info-circle',
                'warning': 'exclamation-triangle',
                'success': 'check-circle',
                'danger': 'times-circle'
            };
            return icons[type] || 'bell';
        }

        // Événements
        document.addEventListener('DOMContentLoaded', function() {
            // Clic sur une notification dans le dropdown
            document.querySelectorAll('.notification-item[data-notification-id]').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationId = this.getAttribute('data-notification-id');
                    openNotificationModal(notificationId);
                });
            });

            // Bouton "Tout marquer comme lu" dans le dropdown
            const markAllReadBtn = document.querySelector('.mark-all-read');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    markAllAsRead();

                    // Fermer le dropdown
                    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationDropdown'));
                    dropdown.hide();
                });
            }

            // Bouton "Tout marquer comme lu" dans le modal
            const markAllReadModalBtn = document.querySelector('.mark-all-read-modal');
            if (markAllReadModalBtn) {
                markAllReadModalBtn.addEventListener('click', function() {
                    markAllAsRead();
                    allNotificationsModal.hide();
                });
            }

            // Gérer l'ouverture du modal depuis le modal "Toutes les notifications"
            document.querySelectorAll('#allNotificationsModal .notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notificationId = this.getAttribute('data-notification-id');
                    allNotificationsModal.hide();
                    setTimeout(() => {
                        openNotificationModal(notificationId);
                    }, 300);
                });
            });
        });

        // Correction du positionnement du dropdown
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script> -->
</body>

</html>