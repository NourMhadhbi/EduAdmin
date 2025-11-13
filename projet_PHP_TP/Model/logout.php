<?php

session_start();
session_unset();
session_destroy();
header("Location: ../Vue/Authentification/connexion.php");
