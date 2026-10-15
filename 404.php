<?php
/* ==========================================================================
   404.php — Page « introuvable »
   --------------------------------------------------------------------------
   Affichée par le serveur IONOS quand un visiteur demande une adresse qui
   n'existe pas (lien erroné, ancienne page supprimée, faute de frappe…),
   grâce à la ligne « ErrorDocument 404 /404.php » du fichier .htaccess.
   Plutôt qu'une page d'erreur technique, le visiteur garde le bandeau et
   le menu du site, avec des liens pour repartir.
   ========================================================================== */

// Réglages généraux du site (coordonnées de l'entreprise…)
require __DIR__ . '/config.php';

// Code « 404 page introuvable » envoyé au navigateur et à Google (pour
// que cette page ne soit jamais enregistrée comme une vraie page du site).
http_response_code(404);

// Informations propres à cette page, utilisées par inc/header.php
$titrePage       = 'Page introuvable - Nouvelle Carrosserie Migliore';
$descriptionPage = 'Cette page n\'existe pas ou n\'existe plus sur le site de la Nouvelle Carrosserie Migliore.';
$pageActive      = '';
$nePasIndexer    = true;   // Google ne doit pas enregistrer cette page

// Bandeau du haut (commun à toutes les pages)
require __DIR__ . '/inc/header.php';
?>

<main id="contenu" class="page-texte">

    <h1>Page introuvable</h1>

    <p>
        La page que vous cherchez n'existe pas ou n'existe plus. Le lien que
        vous avez suivi est peut-être ancien, ou l'adresse contient une faute
        de frappe.
    </p>

    <p>Vous pouvez :</p>
    <ul>
        <li><a href="index.php">revenir à la page d'accueil</a> ;</li>
        <li><a href="index.php#amenagements">découvrir nos aménagements</a> ;</li>
        <li><a href="contact.php#formulaire">nous écrire</a> ou nous appeler au
            <a href="tel:<?= htmlspecialchars($telephoneLien) ?>"><?= htmlspecialchars($telephoneAffiche) ?></a>.</li>
    </ul>

</main>

<?php
// Pied de page (commun à toutes les pages)
require __DIR__ . '/inc/footer.php';
