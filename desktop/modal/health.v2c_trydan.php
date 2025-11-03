<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$eqLogics = eqLogic::byType('v2c_trydan');
?>

<div style="padding: 20px;">
    <h3><i class="fas fa-heartbeat"></i> Santé V2C Trydan</h3>
    <hr>
    
    <table class="table table-bordered table-condensed">
        <thead>
            <tr>
                <th>{{Équipement}}</th>
                <th>{{État}}</th>
                <th>{{Actif}}</th>
                <th>{{Token API}}</th>
                <th>{{ID Chargeur}}</th>
                <th>{{Dernière communication}}</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($eqLogics as $eqLogic) {
                echo '<tr>';
                echo '<td>' . $eqLogic->getHumanName(true) . '</td>';
                
                // État
                $status = $eqLogic->getStatus();
                if ($status == 1) {
                    echo '<td><span class="label label-success">OK</span></td>';
                } else {
                    echo '<td><span class="label label-danger">NOK</span></td>';
                }
                
                // Actif
                if ($eqLogic->getIsEnable()) {
                    echo '<td><span class="label label-success">Oui</span></td>';
                } else {
                    echo '<td><span class="label label-default">Non</span></td>';
                }
                
                // Token API
                if (!empty($eqLogic->getConfiguration('api_token'))) {
                    echo '<td><span class="label label-success"><i class="fas fa-check"></i></span></td>';
                } else {
                    echo '<td><span class="label label-danger"><i class="fas fa-times"></i></span></td>';
                }
                
                // ID Chargeur
                if (!empty($eqLogic->getConfiguration('charger_id'))) {
                    echo '<td>' . $eqLogic->getConfiguration('charger_id') . '</td>';
                } else {
                    echo '<td><span class="label label-warning">Non configuré</span></td>';
                }
                
                // Dernière communication
                $cmd = $eqLogic->getCmd(null, 'status');
                if (is_object($cmd)) {
                    $lastUpdate = $cmd->getCollectDate();
                    if ($lastUpdate) {
                        echo '<td>' . $lastUpdate . '</td>';
                    } else {
                        echo '<td>{{Jamais}}</td>';
                    }
                } else {
                    echo '<td>-</td>';
                }
                
                echo '</tr>';
            }
            
            if (count($eqLogics) == 0) {
                echo '<tr><td colspan="6" class="text-center">{{Aucun équipement configuré}}</td></tr>';
            }
            ?>
        </tbody>
    </table>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Pour qu'un équipement soit considéré comme "OK", il doit être actif, avoir un token API configuré, 
        un ID de chargeur valide, et avoir communiqué récemment avec l'API V2C.
    </div>
</div>
