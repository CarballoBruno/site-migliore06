<?php
/* ==========================================================================
   inc/footer.php — Fin commune à toutes les pages du site
   --------------------------------------------------------------------------
   Pied de page : une bande bleue de la même largeur que le bandeau du haut
   (85 % de l'écran), en 3 colonnes :

     colonne 1                      ┃   colonne 2 (la plus large)     ┃ colonne 3
     SARL au capital de …  SIRET …  ┃ NOUVELLE CARROSSERIE MIGLIORE   ┃ Mentions légales  Confid.  © année
     Tél. …                 email   ┃        adresse postale          ┃

   (le numéro RCS figure dans la page des mentions légales)

   - colonnes 1 et 3 de même largeur : le nom (Impact, blanc, plus gros)
     est exactement au centre, aligné avec le titre du bandeau du haut ;
   - colonnes séparées par des traits verticaux blancs.
   Écrans moyens : le nom et l'adresse passent au-dessus, sur toute la
   largeur, les colonnes 1 et 3 en dessous. Téléphone : tout s'empile.

   Tout est écrit en vrai texte (pas en image) : facile à lire, à
   sélectionner, à copier, et lisible par Google.
   Les informations viennent de config.php (une seule source à modifier).
   ========================================================================== */
?>

<footer class="pied-de-page">

    <!-- Colonne 1 : informations juridiques et contact -->
    <div class="pied-de-page-colonne colonne-gauche">
        <p class="pied-de-page-ligne">
            <span><?= htmlspecialchars($formeJuridique) ?> au capital de <?= htmlspecialchars($capitalSocial) ?></span>
            <span>SIRET <?= htmlspecialchars($numeroSiret) ?></span>
        </p>
        <p class="pied-de-page-ligne">
            <span>Tél. <a href="tel:<?= htmlspecialchars($telephoneLien) ?>"><?= htmlspecialchars($telephoneAffiche) ?></a></span>
            <a href="mailto:<?= htmlspecialchars($emailContact) ?>"><?= htmlspecialchars($emailContact) ?></a>
        </p>
    </div>

    <!-- Colonne 2 (la plus large) : identité de l'entreprise.
         La balise <address> indique aux navigateurs et à Google qu'il
         s'agit des coordonnées officielles de l'entreprise. -->
    <address class="pied-de-page-colonne colonne-identite">
        <p class="pied-de-page-ligne pied-de-page-nom"><span><?= htmlspecialchars($nomEntreprise) ?></span></p>
        <p class="pied-de-page-ligne"><span><?= htmlspecialchars($adresseRue . ', ' . $adresseCodePostal . ' ' . $adresseVille) ?></span></p>
    </address>

    <!-- Colonne 3 : liens légaux et copyright, sur une seule ligne
         (l'année est calculée automatiquement par PHP) -->
    <div class="pied-de-page-colonne colonne-legale">
        <p class="pied-de-page-ligne">
            <a href="mentions-legales.php">Mentions légales</a>
            <!-- Petit tiret blanc décoratif (ignoré par les lecteurs d'écran) -->
            <span class="separateur-pied-de-page" aria-hidden="true">–</span>
            <a href="mentions-legales.php#confidentialite">Politique de confidentialité</a>
            <span>© <?= date('Y') ?></span>
        </p>
    </div>

</footer>

</body>
</html>
