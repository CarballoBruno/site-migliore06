<?php
/* ==========================================================================
   contact.php — Page Contact du site migliore06.fr
   --------------------------------------------------------------------------
   Contient :
     - le formulaire de contact (nom, prénom, société, adresse postale,
       email, téléphone facultatif, message), protégé contre les robots
       sans captcha ;
     - les coordonnées : téléphone en clair, email, adresse, horaires ;
     - la carte Google Maps, chargée seulement si le visiteur clique dessus
       (respect du RGPD : rien n'est envoyé à Google sans son accord).

   Fonctionnement du formulaire :
     1. le visiteur remplit le formulaire et clique sur « Envoyer » ;
     2. la page reçoit les données, les vérifie (voir inc/fonctions-contact.php) ;
     3. en cas d'erreur : la page se réaffiche avec les champs déjà remplis
        et les erreurs en rouge ;
     4. en cas de succès : l'email part, puis la page se recharge avec le
        message vert « Votre demande a bien été envoyée » (ce rechargement
        évite un double envoi si le visiteur actualise la page).
   ========================================================================== */

// Réglages généraux du site et fonctions du formulaire
require __DIR__ . '/config.php';
require __DIR__ . '/inc/fonctions-contact.php';


/* --------------------------------------------------------------------------
   Traitement du formulaire (avant tout affichage, pour pouvoir recharger
   la page après un envoi réussi)
   -------------------------------------------------------------------------- */

// Champs du formulaire (vides au premier affichage).
$champs = ['nom' => '', 'prenom' => '', 'societe' => '', 'adresse' => '', 'email' => '', 'telephone' => '', 'message' => ''];

// Erreurs par champ, et message d'erreur général (en haut du formulaire).
$erreurs        = [];
$erreurGenerale = '';

// Le message vert s'affiche quand la page est rechargée après un envoi réussi.
$envoiReussi = isset($_GET['envoye']);

// Fichier qui compte les envois récents (limite d'envois par heure).
$fichierCompteur = $dossierDonnees . '/envois-recents.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $champs = lireChampsFormulaire($_POST);

    // Protection 1 : champ piège. Il est invisible pour un humain ; s'il est
    // rempli, c'est un robot. On fait semblant que tout s'est bien passé
    // (le robot ne doit pas comprendre qu'il a été repéré), sans rien envoyer.
    if (trim((string) ($_POST['site_internet'] ?? '')) !== '') {
        header('Location: contact.php?envoye=1#formulaire');
        exit;
    }

    // Protection 2 : jeton signé (formulaire venant bien de notre site,
    // rempli en un temps humain).
    $etatJeton = verifierJetonFormulaire(
        (string) ($_POST['jeton'] ?? ''),
        $cleSecreteFormulaire,
        $delaiMinimumRemplissage,
        $delaiMaximumRemplissage
    );

    // Protection 3 : nombre d'envois par heure depuis cette connexion.
    $empreinte = calculerEmpreinteVisiteur($cleSecreteFormulaire);

    if ($etatJeton === 'trop-rapide') {
        $erreurGenerale = 'Votre demande a été envoyée trop rapidement. Merci de vérifier vos informations puis de cliquer à nouveau sur « Envoyer ».';
    } elseif ($etatJeton === 'invalide') {
        $erreurGenerale = 'La page était ouverte depuis trop longtemps. Merci de vérifier vos informations puis de cliquer à nouveau sur « Envoyer ».';
    } elseif (limiteEnvoisAtteinte($fichierCompteur, $empreinte, $nombreMaxEnvoisParHeure)) {
        $erreurGenerale = 'Plusieurs demandes ont déjà été envoyées depuis votre connexion. Merci de réessayer plus tard, ou de nous appeler au ' . $telephoneAffiche . '.';
    } else {
        $erreurs = verifierChampsFormulaire($champs);

        if ($erreurs === []) {
            if (envoyerMessageContact($champs, $adresseDestinataire, $adresseExpediteur, $dossierDonnees, $estEnLocal)) {
                enregistrerEnvoi($fichierCompteur, $empreinte);
                header('Location: contact.php?envoye=1#formulaire');
                exit;
            }
            $erreurGenerale = 'Votre demande n\'a pas pu être envoyée, suite à un problème technique. Merci de nous appeler au ' . $telephoneAffiche . ' ou d\'écrire à ' . $emailContact . '.';
        } else {
            $erreurGenerale = 'Certaines informations sont manquantes ou incorrectes : merci de vérifier les champs indiqués en rouge.';
        }
    }
}

// Nouveau jeton à chaque affichage du formulaire.
$jeton = creerJetonFormulaire($cleSecreteFormulaire);


/* --------------------------------------------------------------------------
   Affichage de la page
   -------------------------------------------------------------------------- */

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Contact - Nouvelle Carrosserie Migliore, carrossier constructeur à La Trinité (06)';
$descriptionPage = 'Contactez la Nouvelle Carrosserie Migliore : formulaire de demande de devis, téléphone '
                 . $telephoneAffiche . ', adresse et horaires de l\'atelier à La Trinité (06).';
$pageActive      = 'contact';

// Adresse complète, et lien vers la carte Google Maps
$adresseComplete = $adresseRue . ', ' . $adresseCodePostal . ' ' . $adresseVille;
$adresseCarte    = rawurlencode($nomEntreprise . ', ' . $adresseComplete);

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';
?>

<main id="contenu" class="page-contact">

    <h1 class="page-contact-titre">Contact</h1>
    <p class="page-contact-intro">
        Un projet d'aménagement, une demande de devis, une question sur un
        contrôle de hayon ou de grue ? Écrivez-nous avec le formulaire
        ci-dessous ou appelez-nous : nous vous répondrons rapidement.
    </p>

    <div class="page-contact-grille">

        <!-- ============================================================
             Colonne gauche : formulaire de contact
             ============================================================ -->
        <section class="carte-contact" id="formulaire" aria-labelledby="titre-formulaire">
            <h2 id="titre-formulaire">Nous écrire</h2>

            <?php if ($envoiReussi): ?>
                <!-- Message vert affiché après un envoi réussi -->
                <p class="message-succes" role="status">
                    Votre demande a bien été envoyée. Nous vous répondrons dans les meilleurs délais.
                </p>
            <?php endif; ?>

            <?php if ($erreurGenerale !== ''): ?>
                <!-- Message rouge en cas de problème -->
                <p class="message-erreur" role="alert"><?= htmlspecialchars($erreurGenerale) ?></p>
            <?php endif; ?>

            <form class="formulaire-contact" method="post" action="contact.php#formulaire" novalidate>

                <p class="mention-obligatoire">Les champs marqués d'une étoile (<span class="etoile" aria-hidden="true">*</span>) sont obligatoires.</p>

                <div class="formulaire-ligne">
                    <div class="formulaire-champ">
                        <label for="champ-nom">Nom <span class="etoile" aria-hidden="true">*</span></label>
                        <input type="text" id="champ-nom" name="nom" required maxlength="80" autocomplete="family-name"
                               value="<?= htmlspecialchars($champs['nom']) ?>"<?= marquerChampEnErreur('nom', $erreurs) ?>>
                        <?= afficherErreurChamp('nom', $erreurs) ?>
                    </div>
                    <div class="formulaire-champ">
                        <label for="champ-prenom">Prénom <span class="etoile" aria-hidden="true">*</span></label>
                        <input type="text" id="champ-prenom" name="prenom" required maxlength="80" autocomplete="given-name"
                               value="<?= htmlspecialchars($champs['prenom']) ?>"<?= marquerChampEnErreur('prenom', $erreurs) ?>>
                        <?= afficherErreurChamp('prenom', $erreurs) ?>
                    </div>
                </div>

                <div class="formulaire-ligne">
                    <div class="formulaire-champ">
                        <label for="champ-email">Email <span class="etoile" aria-hidden="true">*</span></label>
                        <input type="email" id="champ-email" name="email" required maxlength="150" autocomplete="email"
                               value="<?= htmlspecialchars($champs['email']) ?>"<?= marquerChampEnErreur('email', $erreurs) ?>>
                        <?= afficherErreurChamp('email', $erreurs) ?>
                    </div>
                    <div class="formulaire-champ">
                        <!-- Facultatif : la demande part aussi sans téléphone -->
                        <label for="champ-telephone">Téléphone (fixe ou portable)</label>
                        <input type="tel" id="champ-telephone" name="telephone" maxlength="30" autocomplete="tel"
                               value="<?= htmlspecialchars($champs['telephone']) ?>">
                    </div>
                </div>

                <div class="formulaire-champ">
                    <label for="champ-societe">Société</label>
                    <input type="text" id="champ-societe" name="societe" maxlength="120" autocomplete="organization"
                           value="<?= htmlspecialchars($champs['societe']) ?>">
                </div>

                <div class="formulaire-champ">
                    <label for="champ-adresse">Adresse postale</label>
                    <textarea id="champ-adresse" name="adresse" rows="2" maxlength="300" autocomplete="street-address"><?= htmlspecialchars($champs['adresse']) ?></textarea>
                </div>

                <!-- « champ-message-extensible » : la case s'agrandit pour que le cadre du
                     formulaire finisse au même niveau que celui des coordonnées. -->
                <div class="formulaire-champ champ-message-extensible">
                    <label for="champ-message">Votre message <span class="etoile" aria-hidden="true">*</span></label>
                    <textarea id="champ-message" name="message" rows="7" required maxlength="5000"
                              placeholder="Type de véhicule, aménagement souhaité, délais…"<?= marquerChampEnErreur('message', $erreurs) ?>><?= htmlspecialchars($champs['message']) ?></textarea>
                    <?= afficherErreurChamp('message', $erreurs) ?>
                </div>

                <!-- Champ piège anti-robots : invisible et ignoré par les
                     humains (et par les lecteurs d'écran). À NE PAS SUPPRIMER. -->
                <div class="champ-piege" aria-hidden="true">
                    <label for="champ-site-internet">Ne pas remplir ce champ</label>
                    <input type="text" id="champ-site-internet" name="site_internet" tabindex="-1" autocomplete="off">
                </div>

                <!-- Jeton anti-robots (heure d'affichage signée). À NE PAS SUPPRIMER. -->
                <input type="hidden" name="jeton" value="<?= htmlspecialchars($jeton) ?>">

                <button type="submit" class="bouton-envoyer">Envoyer ma demande</button>

                <p class="mention-rgpd">
                    Les informations saisies servent uniquement à répondre à votre demande.
                    Voir notre <a href="mentions-legales.php#confidentialite">politique de confidentialité</a>.
                </p>
            </form>
        </section>

        <!-- ============================================================
             Colonne droite : coordonnées, horaires, carte
             ============================================================ -->
        <aside class="carte-contact carte-coordonnees" aria-labelledby="titre-coordonnees">
            <h2 id="titre-coordonnees">Nous appeler, nous rendre visite</h2>

            <!-- Téléphone en clair, bien visible -->
            <p class="telephone-en-clair">
                <a href="tel:<?= htmlspecialchars($telephoneLien) ?>"><?= htmlspecialchars($telephoneAffiche) ?></a>
            </p>

            <address class="coordonnees">
                <!-- Nom de l'entreprise : en Impact bleu, comme partout sur le site -->
                <span class="nom-entreprise-coordonnees"><?= htmlspecialchars($nomEntreprise) ?></span>
                <span><?= htmlspecialchars($adresseRue) ?></span>
                <span><?= htmlspecialchars($adresseCodePostal . ' ' . $adresseVille) ?></span>
                <a href="mailto:<?= htmlspecialchars($emailContact) ?>"><?= htmlspecialchars($emailContact) ?></a>
            </address>

            <h3>Horaires d'ouverture</h3>
            <?php require __DIR__ . '/inc/horaires.php'; ?>

            <h3>Plan d'accès</h3>
            <!-- La carte Google Maps n'est chargée qu'après un clic (voir
                 js/main.js) : avant ce clic, aucune donnée n'est envoyée à
                 Google. Sans JavaScript, le lien ouvre Google Maps. -->
            <div class="emplacement-carte" data-carte-src="https://www.google.com/maps?q=<?= $adresseCarte ?>&amp;output=embed">
                <p>La carte est fournie par Google Maps. En l'affichant, vous acceptez que Google reçoive des informations de connexion.</p>
                <button type="button" class="bouton-carte">Afficher la carte</button>
                <a class="lien-carte" href="https://www.google.com/maps/search/?api=1&amp;query=<?= $adresseCarte ?>">Ouvrir dans Google Maps</a>
            </div>
        </aside>

    </div>
</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
