<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <h3><i class="fas fa-charging-station"></i> Plugin V2C Trydan</h3>
    <hr>
    
    <div class="alert alert-info">
        <h4><i class="fas fa-info-circle"></i> À propos</h4>
        <p>
            Ce plugin vous permet de piloter et superviser votre borne de recharge V2C Trydan 
            directement depuis Jeedom via l'API Cloud V2C.
        </p>
    </div>
    
    <h4><i class="fas fa-star"></i> Fonctionnalités principales</h4>
    <ul>
        <li>Supervision en temps réel de la charge</li>
        <li>Contrôle à distance (démarrer, arrêter, pause, reprendre)</li>
        <li>Réglage de l'intensité de charge (6-32A)</li>
        <li>Changement de mode de charge</li>
        <li>Verrouillage/déverrouillage de la borne</li>
        <li>Historisation des données de consommation</li>
        <li>Compatible avec les scénarios Jeedom</li>
    </ul>
    
    <h4><i class="fas fa-cog"></i> Configuration requise</h4>
    <ul>
        <li>Une borne V2C Trydan connectée au Cloud V2C</li>
        <li>Un compte V2C Cloud actif</li>
        <li>Un token API V2C (disponible sur v2c.cloud)</li>
    </ul>
    
    <h4><i class="fas fa-book"></i> Documentation</h4>
    <p>
        Pour plus d'informations, consultez la 
        <a href="#" onclick="window.open('https://github.com/votre-repo/v2c_trydan/blob/master/docs/fr_FR/index.md', '_blank')">
            documentation complète
        </a>
    </p>
    
    <h4><i class="fas fa-life-ring"></i> Support</h4>
    <p>
        <a href="https://community.jeedom.com" target="_blank">Forum Jeedom</a> |
        <a href="https://v2charge.com/fr/support/" target="_blank">Support V2C</a>
    </p>
</div>
