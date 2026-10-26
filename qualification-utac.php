<?php
/* ==========================================================================
   qualification-utac.php — Page « Qualification UTAC »
   --------------------------------------------------------------------------
   Créée le 23/10/2026 (demande du client, branche d'essai « essai-nouvel-aspect ») :
   le long texte « Opérateur qualifié UTAC » quitte l'accueil pour avoir sa
   propre page ; l'accueil n'en garde qu'un court encadré avec un lien ici.
   Contenu repris tel quel de l'accueil (d'après le Manuel Qualité de
   l'entreprise) : ce que la qualification change pour un véhicule neuf, les
   7 étapes de la démarche qualité, les audits de l'UTAC.
   ========================================================================== */

// Réglages généraux du site (coordonnées, informations juridiques…)
require __DIR__ . '/config.php';

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Qualification UTAC - Nouvelle Carrosserie Migliore';
$descriptionPage = 'Opérateur qualifié UTAC : votre véhicule neuf carrossé à La Trinité (06) est livré avec '
                 . 'son procès-verbal, prêt à être immatriculé.';   // ≤ 155 caractères (26/10/2026)
$pageActive      = 'qualification-utac';

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';
?>

<main id="contenu" class="page-utac">

    <!-- Même texte que l'ancien bloc de l'accueil ; les styles « certification »
         et « etapes » sont ceux de l'accueil (css/style.css). -->
    <section class="certification" aria-labelledby="titre-certification">
        <h1 id="titre-certification" class="certification-titre page-utac-titre">
            Opérateur qualifié UTAC&nbsp;: ce que cela change pour votre véhicule neuf
        </h1>

        <p>
            Un véhicule neuf que l'on carrosse ou que l'on aménage doit être reconnu
            conforme au code de la route avant sa première immatriculation. Opérateur
            qualifié UTAC pour le poids lourd, l'utilitaire et l'aménagement, nous
            réalisons nous-mêmes ce <strong>contrôle de conformité initial</strong>. Votre
            véhicule est livré avec le procès-verbal qui permet de l'immatriculer
            directement, sans passer par une réception à titre isolé auprès de la
            <strong>DREAL</strong> (Direction régionale de l'environnement, de l'aménagement
            et du logement), le service de l'État chargé de la réception des véhicules.
        </p>

        <p>
            Cette qualification repose sur une démarche qualité appliquée à chaque
            véhicule, de votre demande jusqu'à la livraison&nbsp;:
        </p>

        <!-- Les 7 étapes (le numéro est ajouté par la feuille de style) -->
        <ol class="etapes">
            <li>
                <h2>L'étude de votre demande</h2>
                <p>Répartition des charges, faisabilité, conformité au code de la route&nbsp;: tout est vérifié avant le devis.</p>
            </li>
            <li>
                <h2>Des équipements homologués</h2>
                <p>Chaque équipement réglementé est accompagné de son procès-verbal d'homologation.</p>
            </li>
            <li>
                <h2>La vérification de votre véhicule à son arrivée</h2>
                <p>Nous contrôlons qu'il correspond exactement au véhicule étudié.</p>
            </li>
            <li>
                <h2>La réalisation dans notre atelier</h2>
                <p>Dans le respect des directives de carrossage du constructeur.</p>
            </li>
            <li>
                <h2>Les essais des appareils de levage</h2>
                <p>Hayons, grues et bras de levage sont essayés avant la livraison.</p>
            </li>
            <li>
                <h2>Le contrôle de conformité et la pesée</h2>
                <p>Chaque véhicule terminé est contrôlé, photographié et pesé.</p>
            </li>
            <li>
                <h2>Un dossier technique complet</h2>
                <p>Conservé au moins 10&nbsp;ans.</p>
            </li>
        </ol>

        <p>
            Cette démarche est vérifiée <strong>tous les deux ans par les audits de
            l'UTAC</strong>, qui conditionnent le maintien de notre qualification.
        </p>

        <!-- Mène au formulaire de la page Contact -->
        <a class="bouton-principal" href="contact.php#formulaire">Parlez-nous de votre projet</a>
    </section>

</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
