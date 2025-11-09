  <footer class="footer bg-dark text-light pt-5 pb-3 position-relative overflow-hidden">
        <div class="container position-relative">
            <!-- Éléments décoratifs -->
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
                <div class="position-absolute top-0 end-0 w-50 h-100 bg-gradient-to-left"></div>
            </div>

            <div class="row align-items-center mb-4">
                <!-- Section texte principal -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-fingerprint fa-2x text-primary me-3"></i>
                        <h4 class="fw-bold mb-0">Système de Présence Intelligente</h4>
                    </div>
                    <p class="mb-2">Solution innovante pour la gestion des présences académiques</p>
                    <p class="small text-muted mb-0">&copy; <?= date('Y') ?> Tous droits réservés.</p>
                </div>

                <!-- Liens rapides -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-3 border-bottom border-secondary pb-2 d-inline-block">Navigation</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="../Accueil/accueil.php" class="text-light text-decoration-none d-flex align-items-center footer-link">
                                <i class="fas fa-home me-2"></i>
                                <span>Accueil</span>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="../Etudiants/ParticipationAuxCours.php" class="text-light text-decoration-none d-flex align-items-center footer-link">
                                <i class="fas fa-book me-2"></i>
                                <span>Cours</span>
                            </a>
                        </li>
                        <li>
                            <a href="../Authentification/connexion.php" class="text-light text-decoration-none d-flex align-items-center footer-link">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                <span>Connexion</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Réseaux sociaux -->
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start">
                    <h5 class="fw-bold mb-3 border-bottom border-secondary pb-2 d-inline-block">Réseaux sociaux</h5>
                    <p class="text-muted mb-3">Suivez-nous pour les dernières actualités</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon me-3" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon me-3" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon me-3" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ligne séparatrice -->
            <hr class="border-secondary my-4">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start small">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>
                    Développement web dynamique - Projet académique
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <i class="fas fa-envelope me-2 text-primary"></i>
                    Contact: <a href="mailto:support@example.com" class="text-light footer-link">support@example.com</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-top: 1px solid rgba(6, 214, 160, 0.3);
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #06d6a0, #118ab2, #ef476f);
        }

        .footer-link {
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .footer-link:hover {
            color: #06d6a0 !important;
            transform: translateX(5px);
            background-color: rgba(6, 214, 160, 0.1);
            text-decoration: none;
        }

        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background-color: #06d6a0;
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(6, 214, 160, 0.3);
        }

        .bg-gradient-to-left {
            background: linear-gradient(90deg, transparent, rgba(6, 214, 160, 0.05));
        }

        @media (max-width: 768px) {
            .footer .row>div {
                text-align: center !important;
                margin-bottom: 1.5rem;
            }

            .footer .d-inline-block {
                display: block !important;
                width: 100%;
            }
        }
    </style>