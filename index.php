<?php
/* ==========================================================================
   index.php — Page d'accueil du site migliore06.fr
   --------------------------------------------------------------------------
   Assemble la page : réglages (config.php), bandeau du haut (inc/header.php),
   contenu propre à l'accueil, puis pied de page (inc/footer.php).
   ========================================================================== */

// Réglages généraux du site (coordonnées, email…)
require __DIR__ . '/config.php';

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Nouvelle Carrosserie Migliore - Carrossier constructeur à La Trinité (06)';
$descriptionPage = 'Carrosserie industrielle depuis 1948 : bennes, plateaux, caisses, hayons, grues '
                 . 'et aménagements pour utilitaires et poids lourds. Certifiée UTAC. '
                 . 'Alpes-Maritimes, Var, Alpes-de-Haute-Provence, Hautes-Alpes, Bouches-du-Rhône.';
$pageActive      = 'accueil';

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';
?>

<main id="contenu">
    <!-- Les sections de la page d'accueil seront ajoutées ici, une par une. -->
</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
