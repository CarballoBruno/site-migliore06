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
     $donneesStructurees (facultatif) → fiche de l'entreprise pour Google (tableau)
     $nePasIndexer (facultatif) → true pour qu'une page ne soit pas dans Google
     $pageActive      → nom de la page en cours ('accueil', 'contact', 'bennes'…),
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


// Menu (choix du client, 15/10/2026) : maison · Nos aménagements ▾ (menu
// déroulant, dont « Travaux sur mesure » en dernier) ·
// Réparation · Entretien · À savoir
// (nouveautés et réglementation) · Contact.
// Chaque lien : nom affiché => fichier de sa page. Les cartes de l'accueil
// (contenu/amenagements.txt) renvoient vers ces mêmes pages.
// Tant qu'une page n'existe pas encore, son lien mène à la section
// « Nos aménagements » de l'accueil ; dès que la page est créée, le lien
// pointe vers elle tout seul (rien à changer ici).

// Liste du menu déroulant « Nos aménagements ».
$menuAmenagements = [
    'Bennes'                   => 'bennes.php',
    'Bras de levage'           => 'bras-de-levage.php',
    'Caisses'                  => 'caisses.php',
    'Hayons'                   => 'hayons.php',
    'Plateaux'                 => 'plateaux.php',
    'Grues'                    => 'grues.php',
    'Fourgons'                 => 'amenagements-fourgons.php',
    'Travaux sur mesure'       => 'amenagements-specifiques.php',
];

// Liens affichés directement dans la bande, après « Nos aménagements » :
// nom affiché => [fichier de la page, adresse de secours tant que la page
// n'existe pas].
$menuRubriques = [
    'Réparation'             => ['reparation.php', 'index.php#amenagements'],
    'Entretien'              => ['entretien.php', 'index.php#amenagements'],
    'À savoir'               => ['a-savoir.php', 'index.php#informations'],
];


/**
 * Donne l'adresse d'un lien du menu : la page si elle existe déjà dans le
 * site, sinon une adresse de secours sur l'accueil.
 *
 * @param string $fichierPage      nom du fichier de la page (ex. bennes.php)
 * @param string $adresseDeSecours adresse utilisée tant que la page n'existe
 *                                 pas (par défaut : section « Nos aménagements »)
 * @return string                  adresse à mettre dans le lien
 */
function donnerAdresseLienMenu(string $fichierPage, string $adresseDeSecours = 'index.php#amenagements'): string
{
    return is_file(__DIR__ . '/../' . $fichierPage) ? $fichierPage : $adresseDeSecours;
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
<?php
// Adresse officielle de cette page (ex. https://www.migliore06.fr/contact.php ;
// l'accueil est simplement https://www.migliore06.fr/).
$fichierPageEnCours = basename($_SERVER['SCRIPT_NAME']);
$adressePage = $adresseSite . ($fichierPageEnCours === 'index.php' ? '' : $fichierPageEnCours);
?>
    <!-- Adresse officielle de la page, pour Google (évite les doublons,
         ex. « /index.php » et « / » comptés comme deux pages). -->
    <link rel="canonical" href="<?= htmlspecialchars($adressePage) ?>">
<?php if (!empty($nePasIndexer)): ?>
    <!-- Page à ne pas enregistrer dans Google (ex. page « introuvable »). -->
    <meta name="robots" content="noindex">
<?php endif; ?>

    <!-- Icône du site : onglet du navigateur, favoris, écran d'accueil du
         téléphone (fichiers à la racine du site, faits d'après le logo NCM). -->
    <link rel="icon" href="favicon.ico" sizes="48x48">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <!-- Aperçu quand le lien du site est partagé (WhatsApp, Facebook,
         LinkedIn, e-mail…) : titre, phrase et photo (images/partage.jpg,
         1200 × 630 pixels). -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="<?= htmlspecialchars($nomEntreprise) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($titrePage) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($descriptionPage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($adressePage) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($adresseSite) ?>images/partage.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Caisse construite par Nouvelle Carrosserie Migliore devant l'atelier de La Trinité">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Feuilles de style, dans cet ordre : réglages, polices, mise en forme -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/polices.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Petits programmes du site (carte au clic, pause du carrousel).
         « defer » : chargés sans ralentir l'affichage de la page. -->
    <script src="js/main.js" defer></script>
<?php if (!empty($donneesStructurees)): ?>
    <!-- Données structurées pour les moteurs de recherche (invisibles à
         l'écran) : nom, adresse, téléphone, zone d'intervention…
         Ce bloc n'est pas un programme : il n'est jamais exécuté. -->
    <script type="application/ld+json"><?= json_encode($donneesStructurees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endif; ?>
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

        <p class="slogan">«&nbsp;Une Écoute, Un Conseil, Un Savoir-faire&nbsp;»</p>

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
            <!-- Accueil : symbole de la petite maison au lieu du mot.
                 « aria-label » donne le nom « Accueil » aux lecteurs d'écran,
                 « title » l'affiche en bulle au survol de la souris. -->
            <li>
                <a href="index.php" aria-label="Accueil" title="Accueil"<?= marquerLienActif('accueil', $pageActive) ?>>
                    <svg class="icone-accueil" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M12 3 2 11.5h3V21h5.5v-6h3v6H19v-9.5h3L12 3Z"/>
                    </svg>
                </a>
            </li>
            <!-- Contact juste après la maison (demande du client, 23/10/2026) : il reste
                 toujours au même endroit ; les autres liens viendront à la suite. -->
            <li><a href="contact.php"<?= marquerLienActif('contact', $pageActive) ?>>Contact</a></li>
            <?php if ($afficherMenuComplet): ?>
            <!-- Menu déroulant « Nos aménagements » : la liste s'ouvre au survol
                 de la souris, ou au toucher / clic sur le bouton (téléphone,
                 clavier). Le bouton est souligné quand on est sur l'une de
                 ces pages. -->
            <?php
            $pagesDuDeroulant = array_map(fn($fichier) => basename($fichier, '.php'), $menuAmenagements);
            $deroulantActif   = in_array($pageActive, $pagesDuDeroulant, true);
            ?>
            <li class="menu-deroulant">
                <button type="button" class="menu-deroulant-bouton<?= $deroulantActif ? ' lien-actif' : '' ?>"
                        aria-expanded="false" aria-controls="liste-amenagements">
                    Nos aménagements <span class="menu-deroulant-fleche" aria-hidden="true">&#9662;</span>
                </button>
                <ul id="liste-amenagements" class="menu-deroulant-liste">
                    <?php foreach ($menuAmenagements as $nomMenu => $fichierPage): ?>
                        <li><a href="<?= donnerAdresseLienMenu($fichierPage) ?>"<?= marquerLienActif(basename($fichierPage, '.php'), $pageActive) ?>><?= htmlspecialchars($nomMenu) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <!-- Rubriques affichées directement (liste plus haut, $menuRubriques) -->
            <?php foreach ($menuRubriques as $nomMenu => [$fichierPage, $adresseDeSecours]): ?>
                <li><a href="<?= donnerAdresseLienMenu($fichierPage, $adresseDeSecours) ?>"<?= marquerLienActif(basename($fichierPage, '.php'), $pageActive) ?>><?= htmlspecialchars($nomMenu) ?></a></li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>

</header>
