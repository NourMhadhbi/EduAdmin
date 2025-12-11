<footer class="footer bg-dark text-light pt-5 pb-3">
    <div class="container-fluid px-5">
        <!-- Newsletter Subscribe Section -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h3 class="fw-bold mb-3 text-white">Restez Informé</h3>
                <p class="text-white mb-4">Abonnez-vous à notre newsletter pour recevoir les dernières actualités</p>
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <form class="input-group shadow-sm">
                            <input type="email" class="form-control form-control-lg border-0" 
                                   placeholder="Votre adresse email" 
                                   aria-label="Email">
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fas fa-paper-plane me-2"></i>S'abonner
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-3 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-fingerprint fa-2x text-primary me-3"></i>
                    <h5 class="fw-bold mb-0">Système de Présence</h5>
                </div>
                <p class="small text-muted mb-3" style="color: white;">
                    Solution innovante basée sur la reconnaissance faciale pour la gestion 
                    intelligente des présences académiques.
                </p>
                <!-- Social Media Icons -->
                <div class="social-icons d-flex gap-2">
                    <a href="https://www.facebook.com/profile.php?id=61583802896687" target="_blank" class="social-icon" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/adam-ghorbel-768808326/" target="_blank" class="social-icon" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://www.instagram.com/ademghorbel_/" target="_blank" class="social-icon" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- Sitemap -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">Plan du Site</h5>
                <ul class="list-unstyled footer-links">
                    <?php
                    $role = strtolower($_SESSION['role'] ?? '');
                    
                    if ($role === 'etudiant'): ?>
                        <li><a href="../Etudiants/InterfaceAccueil.php"><i class="fas fa-angle-right me-2"></i>Accueil</a></li>
                        <li><a href="../Etudiants/inscritCours.php"><i class="fas fa-angle-right me-2"></i>Mes Cours</a></li>
                        <li><a href="../Etudiants/ParticipationAuxCours.php"><i class="fas fa-angle-right me-2"></i>Présence</a></li>
                        <li><a href="../Profil/GestionDuProfil.php"><i class="fas fa-angle-right me-2"></i>Mon Profil</a></li>
                    <?php elseif ($role === 'enseignant'): ?>
                        <li><a href="../Enseignant/TableaudeBordEnseignant.php"><i class="fas fa-angle-right me-2"></i>Tableau de Bord</a></li>
                        <li><a href="../Enseignant/GestionDesCoursEns.php"><i class="fas fa-angle-right me-2"></i>Mes Cours</a></li>
                        <li><a href="../Enseignant/GestionDesPrésencesEns.php"><i class="fas fa-angle-right me-2"></i>Présences</a></li>
                        <li><a href="../Profil/GestionDuProfil.php"><i class="fas fa-angle-right me-2"></i>Mon Profil</a></li>
                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="../Administrateur/DashboardAdmin.php"><i class="fas fa-angle-right me-2"></i>Tableau de Bord</a></li>
                        <li><a href="../Administrateur/ListeUtilisateursAd.php"><i class="fas fa-angle-right me-2"></i>Utilisateurs</a></li>
                        <li><a href="../Profil/GestionDuProfil.php"><i class="fas fa-angle-right me-2"></i>Mon Profil</a></li>
                    <?php else: ?>
                        <li><a href="../Authentification/connexion.php"><i class="fas fa-angle-right me-2"></i>Connexion</a></li>
                        <li><a href="../Authentification/inscription.php"><i class="fas fa-angle-right me-2"></i>Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Useful Links -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">Liens Utiles</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="#"><i class="fas fa-angle-right me-2"></i>Nous Trouver</a></li>
                    <li><a href="#"><i class="fas fa-angle-right me-2"></i>Politique de Confidentialité</a></li>
                    <li><a href="#"><i class="fas fa-angle-right me-2"></i>Conditions d'Utilisation</a></li>
                    <li><a href="#"><i class="fas fa-angle-right me-2"></i>FAQ</a></li>
                    <li><a href="#"><i class="fas fa-angle-right me-2"></i>Support Technique</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-3">Nous Contacter</h5>
                <ul class="list-unstyled contact-info">
                    <li class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <div class="d-inline-block">
                            <strong class="text-white">Adresse:</strong><br>
                            <span class="text-white small">Sfax, Tunis<br>Rue teboulbi km 5.5</span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <div class="d-inline-block">
                            <strong class="text-white">Téléphone:</strong><br>
                            <a href="tel:+21650367500" class="text-white small">+216 50 367 500</a>
                        </div>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <div class="d-inline-block">
                            <strong class="text-white">Email:</strong><br>
                            <a href="mailto:adamghorbel@ieee.org" class="text-white small">adamghorbel@ieee.org</a>
                        </div>
                    </li>
                </ul>
                <a href="#" class="btn btn-outline-light btn-sm mt-2">
                    <i class="fas fa-paper-plane me-2"></i>Contactez-nous
                </a>
            </div>
        </div>

        <!-- Bottom Bar -->
        <hr class="border-secondary my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small">
                <i class="fas fa-graduation-cap me-2 text-primary"></i>
                &copy; <?= date('Y') ?> Système de Présence Intelligente - Tous droits réservés
            </div>
            <div class="col-md-6 text-center text-md-end small">
                <i class="fas fa-code me-2 text-primary"></i>
                Développé par -  Mohamed Adam Ghorbel  
            </div>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-top: 3px solid var(--accent-color);
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
        margin-top: 3rem;
    }

    .footer .form-control {
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 25px 0 0 25px;
    }

    .footer .form-control:focus {
        background-color: #fff;
        box-shadow: none;
    }

    .footer .btn-primary {
        border-radius: 0 25px 25px 0;
        background: linear-gradient(135deg, var(--accent-color), #059669);
        border: none;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .footer-links a:hover {
        color: var(--accent-color);
        transform: translateX(5px);
    }

    .footer-links i {
        color: var(--accent-color);
        font-size: 0.8rem;
    }

    .contact-info li {
        color: rgba(255, 255, 255, 0.9);
    }

    .contact-info a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-info a:hover {
        color: var(--accent-color);
    }

    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .social-icon:hover {
        background-color: var(--accent-color);
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    }

    .footer h3, .footer h5 {
        color: #fff;
    }

    .footer .text-primary {
        color: var(--accent-color) !important;
    }

    .btn-outline-light:hover {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .footer .row > div {
            margin-bottom: 2rem;
        }
        
        .footer .text-center {
            text-align: center !important;
        }
    }
</style>