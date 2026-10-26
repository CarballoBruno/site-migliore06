<?php
/* ==========================================================================
   index.php — Page d'accueil du site migliore06.fr
   --------------------------------------------------------------------------
   Assemble la page : réglages (config.php), bandeau du haut (inc/header.php),
   contenu propre à l'accueil, puis pied de page (inc/footer.php).

   Contenu de l'accueil (de haut en bas) :
     1. carrousel : bande de photos qui défile en continu, sous le menu,
        suivie d'une bande bleue de séparation ;
     2. (sections suivantes à venir)
   ========================================================================== */

// Réglages généraux du site (coordonnées, email…)
require __DIR__ . '/config.php';

// Lecture des fichiers de contenu (photos du carrousel, annonces…)
require __DIR__ . '/inc/fonctions-contenu.php';

// Calcul « ouvert / fermé en ce moment » (horaires, jours fériés…)
require __DIR__ . '/inc/fonctions-horaires.php';

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Nouvelle Carrosserie Migliore - Carrossier constructeur à La Trinité (06)';
$descriptionPage = 'Carrosserie industrielle depuis 1948 : bennes, plateaux, caisses, hayons, grues '
                 . 'et aménagements pour utilitaires et poids lourds. Certifiée UTAC. '
                 . 'Alpes-Maritimes, Var, Alpes-de-Haute-Provence, Hautes-Alpes, Bouches-du-Rhône.';
$pageActive      = 'accueil';

// Données structurées : fiche d'identité de l'entreprise lue par Google
// (invisible à l'écran). Elle indique notamment notre zone d'intervention,
// sans l'afficher sur la page (choix du client, 15/10/2026). Voir
// https://developers.google.com/search/docs/appearance/structured-data/local-business
$donneesStructurees = [
    '@context'     => 'https://schema.org',
    '@type'        => 'AutoBodyShop',
    'name'         => $nomEntreprise,
    'description'  => 'Carrossier constructeur : bennes, caisses, hayons, bras de levage, plateaux, '
                    . 'grues et aménagements pour utilitaires et poids lourds. Opérateur qualifié UTAC.',
    'url'          => $adresseSite,
    'telephone'    => $telephoneLien,
    'email'        => $emailContact,
    'foundingDate' => '1948',
    'address'      => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $adresseRue,
        'postalCode'      => $adresseCodePostal,
        'addressLocality' => $adresseVille,
        'addressRegion'   => 'Alpes-Maritimes',
        'addressCountry'  => 'FR',
    ],
    'areaServed'   => array_map(
        fn($departement) => ['@type' => 'AdministrativeArea', 'name' => $departement],
        ['Alpes-Maritimes', 'Var', 'Alpes-de-Haute-Provence', 'Hautes-Alpes', 'Bouches-du-Rhône']
    ),
];

// Moment de la visite (sert au carrousel, aux annonces, à « ouvert / fermé »)
$maintenant = new DateTimeImmutable('now');

// Photos du carrousel : tirées au sort dans le stock (liste dans
// contenu/carrousel.txt, fichiers dans images/carrousel/), puis mélangées
// (voir tirerPhotosCarrousel() et melangerPhotosCarrousel()).
$stockCarrousel  = lirePhotosCarrousel(__DIR__ . '/contenu/carrousel.txt', __DIR__ . '/images/carrousel');
$photosCarrousel = melangerPhotosCarrousel(tirerPhotosCarrousel(
    $stockCarrousel,
    $nombrePhotosCarrousel,
    $partPhotosRecentes,
    $moisPhotoRecente,
    $maintenant
));

// Description de chaque photo (celle de contenu/carrousel.txt), reprise
// comme texte de remplacement des photos des cartes : lue par Google et
// par les personnes malvoyantes. Une photo hors carrousel est décrite dans
// la dernière colonne de contenu/amenagements.txt (sinon : titre de la carte).
$legendesPhotos = array_column($stockCarrousel, 'legende', 'fichier');

// Cartes « Nos aménagements » (contenu/amenagements.txt)
$cartesAmenagements = lireCartesAmenagements(
    __DIR__ . '/contenu/amenagements.txt',
    __DIR__ . '/images/carrousel',
    __DIR__
);

// Annonces « Informations importantes » à afficher aujourd'hui
// (un fichier par annonce dans contenu/infos/, mode d'emploi : _modele.txt)
$annoncesActives = lireAnnoncesActives(__DIR__ . '/contenu/infos', $maintenant);

// Ouvert ou fermé en ce moment ? (horaires de config.php, jours fériés,
// fermetures exceptionnelles indiquées dans contenu/infos/)
$etatOuverture = calculerEtatOuverture(
    $horairesOuverture,
    lireFermeturesExceptionnelles(__DIR__ . '/contenu/infos'),
    $maintenant
);

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';
?>

<main id="contenu">

    <?php if ($photosCarrousel !== []): ?>
    <!-- ================================================================
         1. CARROUSEL : les photos défilent lentement de droite à gauche,
            sans fin. Pour que la boucle soit invisible, la série de photos
            est écrite deux fois à la suite (la 2e série est ignorée par les
            lecteurs d'écran) : quand la dernière photo passe, la première
            revient aussitôt derrière, et cela tourne sans fin.
            Toutes les photos sont chargées dès l'ouverture de la page (pas
            de chargement « à la demande »), sinon des vides apparaîtraient
            dans la boucle. Le défilement s'arrête au survol de la souris,
            et avec le bouton « pause ».
         ================================================================ -->
    <section class="carrousel" aria-label="Photos de nos réalisations">
        <div class="carrousel-fenetre">
            <div class="carrousel-piste">
                <?php foreach ([false, true] as $estCopie): ?>
                    <?php foreach ($photosCarrousel as $photo): ?>
                        <!-- La description (alt) n'est pas affichée sur la photo : elle
                             est lue par Google et par les lecteurs d'écran. -->
                        <figure class="carrousel-photo"<?= $estCopie ? ' aria-hidden="true"' : '' ?>>
                            <img src="images/carrousel/<?= htmlspecialchars($photo['fichier']) ?>"
                                 alt="<?= $estCopie ? '' : htmlspecialchars($photo['legende']) ?>"
                                 width="<?= (int) $photo['largeur'] ?>" height="<?= (int) $photo['hauteur'] ?>">
                        </figure>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Bouton pause / lecture (affiché par js/main.js) -->
        <button type="button" class="carrousel-bouton-pause" hidden
                aria-label="Mettre le défilement des photos en pause" aria-pressed="false">
            <span aria-hidden="true">❚❚</span>
        </button>
    </section>
    <?php endif; ?>

    <!-- Bande bleue sous le carrousel (même hauteur que celle du menu),
         simple séparation avant la suite de la page. -->
    <div class="bande-bleue-carrousel"></div>

    <!-- ================================================================
         2. CORPS DE LA PAGE (disposition « hybride ») :

            ┌────────────────────────────┬─────────┐
            │ contenu principal (4/5)    │ colonne │  ← partie haute
            │                            │ (1/5)   │
            ├────────────────────────────┴─────────┤
            │ sections sur toute la largeur        │  ← suite de la page
            └──────────────────────────────────────┘

            Sur tablette et téléphone, la colonne passe sous le contenu
            principal.
         ================================================================ -->
    <div class="accueil-corps">

        <!-- 2.1 Partie haute : contenu principal + colonne de droite -->
        <div class="accueil-haut">

            <div class="accueil-principal">

                <!-- Présentation de l'entreprise : premier texte lu par le
                     visiteur (titre principal de la page pour Google). -->
                <section class="presentation" aria-labelledby="titre-presentation">
                    <h1 id="titre-presentation" class="presentation-titre">
                        Carrossier constructeur depuis 1948, pour l'utilitaire et le poids lourd
                    </h1>

                    <!-- Deux colonnes : le texte à gauche (avec une image insérée à
                         gauche, que le texte entoure), et une étroite colonne
                         d'images à droite. Le bord droit du texte reste ainsi
                         aligné de haut en bas. -->
                    <div class="presentation-corps">

                        <div class="presentation-texte">
                            <p>
                                Votre concessionnaire vous vend le véhicule. Nous nous occupons de
                                <strong>sa carrosserie</strong>&nbsp;: benne, caisse, plateau, hayon élévateur, grue,
                                bras de levage, et tous types d'aménagements.
                            </p>

                            <p>
                                <!-- Image insérée dans le texte, à gauche (le texte l'entoure) -->
                                <span class="image-dans-texte image-a-gauche">
                                    <img src="images/etude-prealable/plan-repartition-charges.png"
                                         alt="Plan de carrossage d'un plateau avec grue sur utilitaire, avec les cotes et la répartition des charges sur les essieux"
                                         width="900" height="663" loading="lazy">
                                    <span class="legende-image">Plan de carrossage et répartition des charges (logiciel TrailerWIN)</span>
                                </span>
                                Chaque métier a ses contraintes. Avant de construire, il faut savoir ce dont
                                vous avez besoin, et donc <strong>prendre le temps de vous écouter</strong>&nbsp;: quelle est
                                votre activité, comment vous travaillez, quelles sont vos spécificités, et ce
                                dont vous avez besoin au quotidien. Nous étudions alors votre projet selon le
                                <strong>savoir-faire de la carrosserie industrielle</strong>, dans le respect des directives
                                constructeurs, des normes réglementaires et du code de la route.
                            </p>

                            <p>
                                Nous établissons ensuite un devis, en vous présentant la <strong>répartition des
                                charges</strong> ainsi qu'un <strong>plan du carrossage</strong>. Nous définissons ensemble le
                                délai d'exécution des travaux et le projet n'est validé qu'après votre
                                <strong>approbation éclairée</strong>.
                            </p>

                            <!-- Qualification UTAC (essai du 26/10/2026, demande du client) : le
                                 paragraphe et l'ancienne bande « Opérateur qualifié UTAC » réunis
                                 ici, suivis du bouton vers qualification-utac.php et du logo UTAC. -->
                            <p>
                                <strong>Opérateur qualifié UTAC</strong> pour le poids lourd, l'utilitaire et l'aménagement,
                                nous réalisons nous-mêmes le <strong>contrôle de conformité</strong> de votre véhicule
                                neuf&nbsp;: il vous est livré avec son procès-verbal, prêt à être immatriculé, sans
                                démarche supplémentaire auprès de l'administration (DREAL). Nous travaillons depuis
                                notre atelier de La Trinité avec les concessionnaires de la région&nbsp;; vous pouvez
                                également nous consulter directement&nbsp;: <strong>nous ferons le lien avec votre
                                concessionnaire</strong>.
                            </p>
                            <div class="encart-utac-action">
                                <a class="bouton-principal bouton-encart-utac" href="qualification-utac.php">Découvrir notre démarche qualité</a>
                                <img class="encart-utac-logo" src="images/logos/logo-utac.svg" alt="UTAC" width="213" height="45">
                            </div>
                        </div>

                        <!-- Colonne de droite, sur toute la hauteur du texte : une image
                             en haut, une image en bas (alignée sur la dernière ligne du
                             texte), et le bouton au milieu de l'espace entre les deux. -->
                        <div class="presentation-images">
                            <figure class="image-dans-texte">
                                <img src="images/etude-prealable/configurateur-jpm.png"
                                     alt="Fiche de configuration d'une benne arrière avec coffre sur Iveco Daily : silhouette cotée et répartition des charges sur les essieux"
                                     width="900" height="572" loading="lazy">
                                <figcaption class="legende-image">Fiche de configuration d'une benne, établie par notre bureau d'études (configurateur JPM)</figcaption>
                            </figure>

                            <!-- Mène au formulaire de la page Contact -->
                            <a class="bouton-principal bouton-pleine-largeur bouton-colonne-images" href="contact.php#formulaire">Parlez-nous de votre projet</a>

                            <figure class="image-dans-texte">
                                <img src="images/etude-prealable/stabilite-grue.png"
                                     alt="Étude de stabilité d'une grue auxiliaire : secteurs de travail autorisés autour du véhicule et efforts sur les béquilles"
                                     width="900" height="532" loading="lazy">
                                <figcaption class="legende-image">Étude de stabilité d'une grue (logiciel CraneWIN)</figcaption>
                            </figure>
                        </div>

                    </div>
                </section>


            </div>

            <aside class="colonne-droite" aria-label="Informations pratiques">
                <!-- Horaires d'ouverture (modifiables dans config.php). Version 8003 : les
                     horaires et les informations restent dans deux encarts SÉPARÉS (demande
                     du client, 26/10/2026) ; c'est la présentation, avec des images plus
                     grandes, qui s'allonge pour que les deux colonnes finissent au même niveau. -->
                <section class="encart-colonne" aria-labelledby="titre-horaires">
                    <h2 id="titre-horaires" class="encart-colonne-titre">Horaires d'ouverture</h2>
                    <?php require __DIR__ . '/inc/horaires.php'; ?>
                </section>

                <!-- Informations importantes : d'abord « ouvert / fermé en ce moment »
                     (calculé à chaque visite), puis les titres des annonces de contenu/infos/
                     (affichées automatiquement entre leurs dates). -->
                <section id="informations" class="encart-colonne" aria-labelledby="titre-infos">
                    <h2 id="titre-infos" class="encart-colonne-titre">Informations importantes</h2>
                    <p class="etat-ouverture <?= $etatOuverture['ouvert'] ? 'etat-ouvert' : 'etat-ferme' ?>">
                        <span class="etat-ouverture-pastille" aria-hidden="true"></span>
                        <span><?= htmlspecialchars($etatOuverture['message']) ?></span>
                    </p>
                    <!-- Annonces repliées (version 8003, 26/10/2026, demande du client) : seul
                         le TITRE est affiché ; un clic ouvre l'annonce complète dans un encart
                         qui vient PAR-DESSUS la page (élément <dialog>, ouvert par js/main.js,
                         partie 5). Sans JavaScript, les annonces restent affichées en entier. -->
                    <?php foreach ($annoncesActives as $numeroAnnonce => $annonce):
                        $classesAnnonce = ($annonce['temporaire'] ? ' annonce-temporaire' : '') . ($annonce['fermeture'] ? ' annonce-fermeture' : '');
                        $idAnnonce = 'annonce-' . ($numeroAnnonce + 1); ?>
                        <button type="button" class="annonce-ouvrir<?= $classesAnnonce ?>" aria-haspopup="dialog"
                                aria-controls="<?= $idAnnonce ?>" hidden><?= htmlspecialchars($annonce['titre']) ?></button>
                        <dialog id="<?= $idAnnonce ?>" class="annonce annonce-dialogue<?= $classesAnnonce ?>" aria-labelledby="<?= $idAnnonce ?>-titre">
                            <button type="button" class="annonce-fermer" aria-label="Fermer" hidden>&times;</button>
                            <h3 id="<?= $idAnnonce ?>-titre" class="annonce-titre"><?= htmlspecialchars($annonce['titre']) ?></h3>
                            <?php foreach ($annonce['paragraphes'] as $paragraphe): ?>
                                <?php if ($paragraphe['liste'] !== []): ?>
                                    <ul class="annonce-liste">
                                        <?php foreach ($paragraphe['liste'] as $element): ?>
                                            <li><?= htmlspecialchars($element) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p<?= $paragraphe['en_valeur'] ? ' class="annonce-en-valeur"' : '' ?>><?= htmlspecialchars($paragraphe['texte']) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($annonce['bouton'] !== ''): ?>
                                <a class="bouton-principal bouton-annonce" href="<?= htmlspecialchars($annonce['bouton_lien']) ?>"><?= htmlspecialchars($annonce['bouton']) ?></a>
                            <?php endif; ?>
                        </dialog>
                    <?php endforeach; ?>
                </section>

            </aside>

        </div>

        <!-- 2.2 Suite de la page : sections sur toute la largeur -->
        <div class="accueil-sections">


            <?php if ($cartesAmenagements !== []): ?>
            <!-- Nos aménagements : une carte par type de carrosserie (contenu
                 dans contenu/amenagements.txt). Une carte devient un lien vers
                 sa page détaillée dès que cette page existe. -->
            <section id="amenagements" class="amenagements bande-teintee" aria-labelledby="titre-amenagements">
                <h2 id="titre-amenagements" class="titre-section">Nos aménagements</h2>
                <ul class="cartes-amenagements">
                    <?php foreach ($cartesAmenagements as $carte): ?>
                        <li class="carte-amenagement">
                            <?php if ($carte['page'] !== ''): ?><a class="carte-amenagement-lien" href="<?= htmlspecialchars($carte['page']) ?>"><?php endif; ?>
                            <img src="images/carrousel/<?= htmlspecialchars($carte['photo']) ?>"
                                 alt="<?= htmlspecialchars($carte['description_photo'] !== '' ? $carte['description_photo'] : ($legendesPhotos[$carte['photo']] ?? $carte['titre'])) ?>" width="<?= (int) $carte['largeur'] ?>" height="<?= (int) $carte['hauteur'] ?>" loading="lazy">
                            <div class="carte-amenagement-texte">
                                <h3><?= htmlspecialchars($carte['titre']) ?></h3>
                                <p><?= htmlspecialchars($carte['texte']) ?></p>
                                <?php if ($carte['page'] !== ''): ?><span class="carte-amenagement-suite">Découvrir</span><?php endif; ?>
                            </div>
                            <?php if ($carte['page'] !== ''): ?></a><?php endif; ?>
                            <!-- Partenaires : logos (liens vers leurs sites, nouvel onglet)
                                 et mention éventuelle. La zone est toujours présente,
                                 même vide, pour que le trait gris soit au même niveau
                                 sur toutes les cartes. -->
                            <div class="carte-partenaire">
                                <?php if ($carte['partenaire'] !== null): ?>
                                    <?php foreach ($carte['partenaire']['logos'] as $unPartenaire):
                                        // Texte de remplacement tiré du nom du fichier :
                                        // logo-hmf.png -> « HMF » (sigle), logo-palkit.png -> « Palkit ».
                                        $nomPartenaire = preg_replace('/^logo-|\.\w+$/', '', $unPartenaire['logo']);
                                        $nomPartenaire = strlen($nomPartenaire) <= 3 ? strtoupper($nomPartenaire) : ucfirst($nomPartenaire); ?>
                                        <?php if ($unPartenaire['lien'] !== ''): ?><a href="<?= htmlspecialchars($unPartenaire['lien']) ?>" target="_blank" rel="noopener"><?php endif; ?>
                                            <img src="images/logos/<?= htmlspecialchars($unPartenaire['logo']) ?>" class="logo-<?= $unPartenaire['format'] ?>"
                                                 alt="<?= htmlspecialchars($nomPartenaire) ?>" loading="lazy">
                                        <?php if ($unPartenaire['lien'] !== ''): ?></a><?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if ($carte['partenaire']['mention'] !== '' && $carte['partenaire']['logos'][0]['lien'] !== ''): ?>
                                        <a class="carte-partenaire-mention" href="<?= htmlspecialchars($carte['partenaire']['logos'][0]['lien']) ?>"
                                           target="_blank" rel="noopener"><?= htmlspecialchars($carte['partenaire']['mention']) ?></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <!-- Fin de la page : notre slogan à gauche, « Nous joindre » à droite
                 (il fait écho à celui du bandeau du haut). -->
            <div class="fin-de-page">
                <p class="fin-de-page-slogan">«&nbsp;Une Écoute, Un Conseil, Un Savoir-faire&nbsp;»</p>
                <section class="encart-colonne fin-de-page-joindre" aria-labelledby="titre-joindre">
                    <h2 id="titre-joindre" class="encart-colonne-titre">Nous joindre</h2>
                    <p class="encart-telephone">
                        <a href="tel:<?= htmlspecialchars($telephoneLien) ?>"><?= htmlspecialchars($telephoneAffiche) ?></a>
                    </p>
                    <address class="encart-adresse">
                        <?= htmlspecialchars($adresseRue) ?><br>
                        <?= htmlspecialchars($adresseCodePostal . ' ' . $adresseVille) ?>
                    </address>
                    <a class="bouton-principal" href="contact.php#formulaire">Parlez-nous de votre projet</a>
                </section>
            </div>
        </div>

    </div>

</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
