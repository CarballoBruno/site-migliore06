<?php
/* ==========================================================================
   mentions-legales.php — Mentions légales et politique de confidentialité
   --------------------------------------------------------------------------
   Page obligatoire (loi pour la confiance dans l'économie numérique, LCEN,
   et RGPD). Elle est accessible depuis le pied de page de toutes les pages :
     - « Mentions légales »            → haut de cette page
     - « Politique de confidentialité » → section #confidentialite

   Les informations de l'entreprise (adresse, SIRET, capital…) viennent de
   config.php : elles sont donc toujours identiques à celles du pied de page.
   ========================================================================== */

// Réglages généraux du site (coordonnées, informations juridiques…)
require __DIR__ . '/config.php';

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Mentions légales et politique de confidentialité - Nouvelle Carrosserie Migliore';
$descriptionPage = 'Mentions légales et politique de confidentialité du site de la Nouvelle Carrosserie Migliore, '
                 . 'carrossier constructeur à La Trinité (06).';
$pageActive      = 'mentions';

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';

// Adresse complète, réutilisée plusieurs fois dans la page
$adresseComplete = $adresseRue . ', ' . $adresseCodePostal . ' ' . $adresseVille;
?>

<main id="contenu" class="page-texte">

    <h1>Mentions légales</h1>

    <!-- ================================================================
         1. Éditeur du site
         ================================================================ -->
    <section>
        <h2>Éditeur du site</h2>
        <p>
            Le site migliore06.fr est édité par la société
            <strong><?= htmlspecialchars($formeJuridique . ' ' . $nomEntreprise) ?></strong>,
            société à responsabilité limitée au capital de <?= htmlspecialchars($capitalSocial) ?>.
        </p>
        <ul class="liste-infos">
            <li><span class="libelle">Siège social :</span> <?= htmlspecialchars($adresseComplete) ?></li>
            <li><span class="libelle">Immatriculation :</span> <?= htmlspecialchars($numeroRcs) ?></li>
            <li><span class="libelle">SIRET :</span> <?= htmlspecialchars($numeroSiret) ?></li>
            <li><span class="libelle">TVA intracommunautaire :</span> <?= htmlspecialchars($numeroTva) ?></li>
            <li><span class="libelle">Téléphone :</span> <a href="tel:<?= htmlspecialchars($telephoneLien) ?>"><?= htmlspecialchars($telephoneAffiche) ?></a></li>
            <li><span class="libelle">Email :</span> <a href="mailto:<?= htmlspecialchars($emailContact) ?>"><?= htmlspecialchars($emailContact) ?></a></li>
        </ul>
    </section>

    <!-- ================================================================
         2. Directeur de la publication
         ================================================================ -->
    <section>
        <h2>Directeur de la publication</h2>
        <p><?= htmlspecialchars($directeurPublication) ?>.</p>
    </section>

    <!-- ================================================================
         3. Hébergeur
         ================================================================ -->
    <section>
        <h2>Hébergement</h2>
        <p>
            Le site est hébergé par <?= htmlspecialchars($hebergeurNom) ?>,
            <?= htmlspecialchars($hebergeurAdresse) ?> —
            <a href="<?= htmlspecialchars($hebergeurSite) ?>"><?= htmlspecialchars(preg_replace('#^https?://#', '', $hebergeurSite)) ?></a>.
        </p>
    </section>

    <!-- ================================================================
         4. Propriété intellectuelle
         ================================================================ -->
    <section>
        <h2>Propriété intellectuelle</h2>
        <p>
            L'ensemble du site (textes, photographies, logo NCM, mise en page)
            est la propriété de la <?= htmlspecialchars($formeJuridique . ' ' . $nomEntreprise) ?>,
            sauf mention contraire. Toute reproduction, totale ou partielle,
            sans autorisation écrite préalable est interdite.
        </p>
        <p>
            Le logo UTAC est la propriété de l'UTAC ; il est reproduit au titre
            des certifications obtenues par l'entreprise (Poids Lourds,
            Véhicules Utilitaires Légers et Aménageur).
        </p>
    </section>

    <!-- ================================================================
         5. Responsabilité
         ================================================================ -->
    <section>
        <h2>Responsabilité</h2>
        <p>
            Les informations présentées sur ce site sont données à titre
            indicatif et peuvent être modifiées à tout moment. Elles ne
            constituent pas une offre contractuelle : chaque projet fait
            l'objet d'une étude et d'un devis personnalisés.
        </p>
    </section>


    <!-- ================================================================
         POLITIQUE DE CONFIDENTIALITÉ (lien « Politique de confidentialité »
         du pied de page → #confidentialite)
         ================================================================ -->
    <h1 id="confidentialite">Politique de confidentialité</h1>

    <section>
        <h2>Responsable du traitement</h2>
        <p>
            Le responsable du traitement des données personnelles est la
            <?= htmlspecialchars($formeJuridique . ' ' . $nomEntreprise) ?>,
            <?= htmlspecialchars($adresseComplete) ?>.
            Contact : <a href="mailto:<?= htmlspecialchars($emailContact) ?>"><?= htmlspecialchars($emailContact) ?></a>.
        </p>
    </section>

    <section>
        <h2>Données collectées</h2>
        <p>
            Le site ne collecte des données personnelles que lorsque vous
            remplissez le formulaire de la page Contact : nom, prénom, société,
            adresse postale, adresse email, numéro de téléphone (facultatif)
            et contenu de votre message.
        </p>
    </section>

    <section>
        <h2>Finalité et base légale</h2>
        <p>
            Ces données servent uniquement à répondre à votre demande
            (renseignement, étude de projet, devis). Leur traitement repose sur
            votre demande elle-même : il est nécessaire pour y répondre.
        </p>
    </section>

    <section>
        <h2>Destinataires</h2>
        <p>
            Vos données sont destinées exclusivement au personnel de
            l'entreprise chargé de traiter votre demande. Elles ne sont ni
            vendues, ni louées, ni transmises à des tiers.
        </p>
    </section>

    <section>
        <h2>Durée de conservation</h2>
        <p>
            Vos données sont conservées pendant 3 ans à compter de notre dernier
            échange, puis supprimées.
        </p>
    </section>

    <section>
        <h2>Vos droits</h2>
        <p>
            Conformément au Règlement général sur la protection des données
            (RGPD) et à la loi «&nbsp;Informatique et Libertés&nbsp;», vous disposez d'un
            droit d'accès, de rectification, d'effacement, d'opposition et de
            limitation du traitement de vos données, ainsi que d'un droit à leur
            portabilité.
        </p>
        <p>
            Pour exercer ces droits, écrivez-nous à
            <a href="mailto:<?= htmlspecialchars($emailContact) ?>"><?= htmlspecialchars($emailContact) ?></a>
            ou par courrier à l'adresse du siège ci-dessus. Si vous estimez,
            après nous avoir contactés, que vos droits ne sont pas respectés,
            vous pouvez adresser une réclamation à la CNIL
            (<a href="https://www.cnil.fr">www.cnil.fr</a>).
        </p>
    </section>

    <section>
        <h2>Cookies et mesure d'audience</h2>
        <p>
            Ce site n'utilise aucun cookie publicitaire ni outil de mesure
            d'audience. Les polices de caractères sont hébergées sur le site
            lui-même : aucune donnée n'est transmise à un service extérieur
            lors de votre visite.
        </p>
        <p>
            La page Contact propose un plan d'accès fourni par Google Maps. Ce
            plan ne s'affiche que si vous cliquez sur « Afficher la carte » :
            Google reçoit alors des informations de connexion et peut déposer
            ses propres cookies, selon sa politique de confidentialité
            (<a href="https://policies.google.com/privacy?hl=fr">policies.google.com</a>).
            Tant que vous n'avez pas cliqué, aucune donnée n'est transmise à Google.
        </p>
    </section>

    <p class="date-mise-a-jour">Dernière mise à jour : 2 octobre 2026.</p>

</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
