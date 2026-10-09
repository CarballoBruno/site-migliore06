<?php
/* ==========================================================================
   inc/horaires.php — Tableau des horaires d'ouverture
   --------------------------------------------------------------------------
   Affiche les horaires définis dans config.php ($horairesOuverture et
   $mentionHoraires) : un jour par ligne, le jour à gauche, les plages
   horaires à droite (une plage par ligne).
   Utilisé par la page Contact et par la colonne de droite de l'accueil :
   les horaires sont donc toujours identiques sur les deux pages.
   ========================================================================== */
?>
<dl class="horaires">
    <?php foreach ($horairesOuverture as $jour => $plages): ?>
        <div class="horaires-ligne<?= $plages === ['Fermé'] ? ' horaires-ferme' : '' ?>">
            <dt><?= htmlspecialchars($jour) ?></dt>
            <dd>
                <?php foreach ($plages as $plage): ?>
                    <span><?= htmlspecialchars($plage) ?></span>
                <?php endforeach; ?>
            </dd>
        </div>
    <?php endforeach; ?>
</dl>
<?php if ($mentionHoraires !== ''): ?>
    <p class="horaires-mention"><?= htmlspecialchars($mentionHoraires) ?></p>
<?php endif; ?>
