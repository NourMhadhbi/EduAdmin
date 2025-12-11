<?php
session_start();
include("../../Model/seance.php");
include("../../Model/presence.php");
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
$seanceProchaine = null;

if ($indexEnCours !== null) {
    if (isset($seances[$indexEnCours + 1])) {
        $seanceProchaine = $seances[$indexEnCours + 1];
    }
} else {
    foreach ($seances as $s) {
        if ($s['heureDebut'] > $heureActuelle) {
            $seanceProchaine = $s;
            break;
        }
    }
}
$stats = Presence::getStatsByEtudiant($idEtudiant);

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Espace Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Global Theme -->
    <link rel="stylesheet" href="../../Css/global-theme.css" />
    <link rel="stylesheet" href="../../Css/accueilEtudiant.css" />
    <style>
        .card {
            margin-bottom: 1.5rem;
        }
    </style>

</head>

<body>

    <?php include("../NavBar/navbar.php") ?>
    <!-- Main Content -->
    <div class="container py-4" id="mainContent">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                        <li class="breadcrumb-item active">Tableau de Bord</li>
                    </ol>
                </nav>
                <h1 class="page-title">Tableau de Bord Étudiant</h1>
            </div>
        </div>

            <!-- Cartes de statistiques  -->
            <div class="stats-overview">
                <div class="stat-card rate">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number"><?= htmlspecialchars($stats['tauxPresence']) ?>%</div>
                    <div class="stat-label">Taux de présence</div>
                </div>

                <div class="stat-card present">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-number"><?= htmlspecialchars($stats['nbPresence']) ?></div>
                    <div class="stat-label">Présences</div>
                </div>

                <div class="stat-card absent">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-number"><?= htmlspecialchars($stats['nbAbsence']) ?></div>
                    <div class="stat-label">Absences</div>
                </div>

                <div class="stat-card courses">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?= htmlspecialchars($stats['totalCours']) ?></div>
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
                                        <?php if (!empty($seances)): ?>
                                            <?php foreach ($seances as $seance): ?>
                                                <?php
                                                $etat = '';
                                                $badgeClass = '';
                                                $action = '';

                                                // Déterminer le statut selon l'heure
                                                if ($seance['heureDebut'] <= $heureActuelle && $seance['heureFin'] >= $heureActuelle) {
                                                    $etat = 'En cours';
                                                    $badgeClass = 'pending';
                                                    $action = '<a href="ParticipationAuxCours.php?id=' . $seance['id'] . '" class="btn btn-sm btn-primary">Marquer présence</a>';
                                                } elseif ($seance['heureDebut'] > $heureActuelle) {
                                                    $etat = 'À venir';
                                                    $badgeClass = 'absent';
                                                    $action = '<button class="btn btn-sm btn-outline-secondary" disabled>Indisponible</button>';
                                                } else {
                                                    $etat = 'Terminée';
                                                    $badgeClass = 'present';
                                                    $action = '<button class="btn btn-sm btn-outline-primary" disabled>Marquée</button>';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($seance['titre']) ?></td>
                                                    <td><?= htmlspecialchars(substr($seance['heureDebut'], 0, 5)) ?> - <?= htmlspecialchars(substr($seance['heureFin'], 0, 5)) ?></td>
                                                    <td><?= htmlspecialchars($seance['nom'] . ' ' . $seance['prenom']) ?></td>
                                                    <td><span class="presence-status <?= $badgeClass ?>"><i class="fas fa-clock me-1"></i><?= $etat ?></span></td>
                                                    <td><?= $action ?></td>
                                                </tr>

                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Aucune séance prévue aujourd’hui</td>
                                            </tr>
                                        <?php endif; ?>
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
                                <a href="./ParticipationAuxCours.php" class="btn btn-primary">
                                    <i class="fas fa-camera me-2"></i>Marquer présence
                                </a>
                                <a href="./historiquePresences.php" class="btn btn-outline-primary">
                                    <i class="fas fa-history me-2"></i>Historique des présences
                                </a>
                                <a href="../Profil/GestionDuProfil.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user-edit me-2"></i>Mon profil
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Prochain cours -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <i class="fas fa-clock me-2"></i>
                            Prochain cours
                        </div>
                        <div class="card-body">
                            <?php if ($seanceProchaine): ?>
                                <h6><?= htmlspecialchars($seanceProchaine['titre']) ?></h6>
                                <p class="mb-1"><strong>Prof.:</strong><?= htmlspecialchars($seanceProchaine['nom'] . ' ' . $seanceProchaine['prenom']) ?></p>
                                <p class="mb-1">
                                    <strong>Heure:</strong>
                                    <?= substr($seanceProchaine['heureDebut'], 0, 5) ?> - <?= substr($seanceProchaine['heureFin'], 0, 5) ?>
                                </p>
                                <p class="mb-0"><strong>Salle:</strong> <?= htmlspecialchars($seanceProchaine['salle'] ?? 'Virtuelle - Teams') ?></p>

                                <?php
                                $timestampDebut = strtotime($seanceProchaine['heureDebut']);
                                $timestampActuel = strtotime($heureActuelle);
                                $minutesRestantes = round(($timestampDebut - $timestampActuel) / 60);

                                if ($minutesRestantes > 0) {
                                    echo '<div class="mt-2"><span class="badge bg-warning">Commence dans ' . $minutesRestantes . ' min</span></div>';
                                } else {
                                    echo '<div class="mt-2"><span class="badge bg-success">En cours</span></div>';
                                }
                                ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">Aucun cours à venir pour aujourd’hui.</p>
                            <?php endif; ?>
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