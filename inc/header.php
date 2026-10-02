<?php
/* ==========================================================================
   inc/header.php — Début commun à toutes les pages du site
   --------------------------------------------------------------------------
   Contient :
     - l'en-tête technique de la page (<head> : titre, description, CSS) ;
     - le bandeau du haut, identique sur toutes les pages, reproduit
       fidèlement d'après l'ancien site migliore06.fr :
         1. zone claire, en 3 colonnes : logo NCM (collé au bord gauche) ;
            au centre, titre en Impact bleu, sous-titre et slogan ; logo
            UTAC avec les domaines certifiés PL, VUL, Aménageur (collé au
            bord droit) ;
         2. bande bleue épaisse contenant le menu, en blanc.
       (La fine bande bleue de l'ancien site, tout en haut, a été retirée
       pour alléger le bandeau.)

   Avant d'inclure ce fichier, chaque page doit définir :
     $titrePage       → titre affiché dans l'onglet du navigateur et sur Google
     $descriptionPage → court résumé de la page, affiché sous le titre sur Google
     $pageActive      → nom de la page en cours ('accueil', 'contact'…),
                        pour souligner le bon lien dans le menu
   et avoir chargé config.php (coordonnées de l'entreprise).

   htmlspecialchars() : protège l'affichage d'un texte (les caractères
   spéciaux comme < > & " ne peuvent pas « casser » la page).
   ========================================================================== */


/**
 * Renvoie les attributs à ajouter au lien du menu correspondant à la page
 * en cours, pour qu'il apparaisse souligné (et soit annoncé comme « page
 * actuelle » aux personnes malvoyantes qui utilisent un lecteur d'écran).
 *
 * @param string $nomLien    nom de la page vers laquelle pointe le lien
 * @param string $pageActive nom de la page affichée en ce moment
 * @return string            les attributs à insérer, ou rien si ce n'est pas la page en cours
 */
function marquerLienActif(string $nomLien, string $pageActive): string
{
    if ($nomLien === $pageActive) {
        return ' class="lien-actif" aria-current="page"';
    }
    return '';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <!-- Adapte l'affichage à la largeur de l'écran (indispensable sur téléphone) -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($titrePage) ?></title>
    <meta name="description" content="<?= htmlspecialchars($descriptionPage) ?>">

    <!-- Feuilles de style, dans cet ordre : réglages, polices, mise en forme -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/polices.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Lien caché, visible seulement au clavier (touche Tab) : permet de sauter
     directement au contenu sans parcourir tout le menu (accessibilité). -->
<a class="lien-acces-contenu" href="#contenu">Aller au contenu</a>

<header class="bandeau-haut">

    <!-- 1. Zone claire : chaque élément est placé dans une grille
         (voir « .zone-identite » dans style.css) -->
    <div class="zone-identite">

        <!-- À l'extrême gauche : logo NCM (cliquer dessus ramène à l'accueil) -->
        <a class="logo-ncm" href="index.php">
            <img src="images/logos/logo-ncm.svg" alt="Logo NCM - Nouvelle Carrosserie Migliore" width="262" height="161">
        </a>

        <!-- Titre : cliquer dessus ramène à l'accueil -->
        <a class="titre-entreprise" href="index.php"><?= htmlspecialchars($nomEntreprise) ?></a>

        <p class="sous-titre-entreprise">- Carrossier Constructeur -</p>

        <p class="slogan">« Une Écoute, Un Conseil, Un Savoir-faire »</p>

        <!-- À l'extrême droite : certification UTAC (logo + domaines certifiés) -->
        <div class="certification-utac">
            <img src="images/logos/logo-utac.svg" alt="Logo UTAC" width="213" height="45">
            <p class="certification-utac-texte"><span>Certifié</span> <span>PL, VUL, Aménageur</span></p>
        </div>
    </div>

    <!-- 2. Bande bleue épaisse : contient le menu (texte blanc), et sépare
         la présentation de l'entreprise du contenu de la page -->
    <nav class="bande-bleue-epaisse menu-principal" aria-label="Menu principal">
        <ul class="menu-principal-liste">
            <li><a href="index.php"<?= marquerLienActif('accueil', $pageActive) ?>>Accueil</a></li>
            <li><a href="index.php#amenagements">Aménagements</a></li>
            <li><a href="index.php#realisations">Réalisations</a></li>
            <li><a href="index.php#actualites">Actualités</a></li>
            <li><a href="contact.php"<?= marquerLienActif('contact', $pageActive) ?>>Contact</a></li>
        </ul>
    </nav>

</header>
