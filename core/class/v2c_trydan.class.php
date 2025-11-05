<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class v2c_trydan extends eqLogic {
    
    const API_BASE_URL = 'https://v2c.cloud/kong/v2c_service';
    
    /*     * ***********************Méthodes statiques*************************** */
    
    public static function cronHourly() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            $eqLogic->refresh();
            $eqLogic->getGlobalStats();
            $eqLogic->getSessionStats();
        }
    }
    
    public static function cron15() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('refresh_frequency', 'hourly') == '15min') {
                $eqLogic->refresh();
                if (date('i') == '00') { // Une fois par heure
                    $eqLogic->getGlobalStats();
                    $eqLogic->getSessionStats();
                }
            }
        }
    }
    
    public static function cron5() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('refresh_frequency', 'hourly') == '5min') {
                $eqLogic->refresh();
                if (date('i') == '00') { // Une fois par heure
                    $eqLogic->getGlobalStats();
                    $eqLogic->getSessionStats();
                }
            }
        }
    }
    
    /*     * *********************Méthodes d'instance************************* */
    
    public function preSave() {
        // Validation du token
        // if (empty($this->getConfiguration('api_token'))) {
        //     throw new Exception(__('Le token API est obligatoire', __FILE__));
        // }
        
        // // Validation du charger_id
        // if (empty($this->getConfiguration('charger_id'))) {
        //     throw new Exception(__('L\'ID du chargeur est obligatoire', __FILE__));
        // }
    }
    
    public function postSave() {
        // Commandes Info principales
        $this->createCommand('connected', 'info', 'binary', 'Connecté');
        $this->createCommand('status', 'info', 'string', 'État', 'core::tile');
        $this->createCommand('power', 'info', 'numeric', 'Puissance de charge', 'core::badge', 'kW');
        $this->createCommand('energy', 'info', 'numeric', 'Énergie', 'core::badge', 'kWh');
        $this->createCommand('intensity', 'info', 'numeric', 'Intensité', 'core::badge', 'A');
        $this->createCommand('voltage', 'info', 'numeric', 'Tension', 'core::badge', 'V');
        $this->createCommand('locked', 'info', 'binary', 'Verrouillé');
        $this->createCommand('paused', 'info', 'binary', 'En pause');
        $this->createCommand('dynamic_mode', 'info', 'binary', 'Mode dynamique');
        $this->createCommand('charge_time', 'info', 'numeric', 'Temps de charge', 'core::badge', 'min');
        $this->createCommand('charge_energy', 'info', 'numeric', 'Énergie session', 'core::badge', 'kWh');
        
        // Commandes Info pour le photovoltaïque
        $this->createCommand('house_power', 'info', 'numeric', 'Puissance maison', 'core::badge', 'kW');
        $this->createCommand('sun_power', 'info', 'numeric', 'Puissance solaire', 'core::badge', 'kW');
        
        // Commandes Action
        $refreshCmd = $this->createCommand('refresh', 'action', 'other', 'Rafraîchir');
        $this->createCommand('start', 'action', 'other', 'Démarrer');
        $this->createCommand('stop', 'action', 'other', 'Arrêter');
        $this->createCommand('pause', 'action', 'other', 'Pause');
        $this->createCommand('resume', 'action', 'other', 'Reprendre');
        $this->createCommand('lock', 'action', 'other', 'Verrouiller');
        $this->createCommand('unlock', 'action', 'other', 'Déverrouiller');
        
        // Commande slider pour l'intensité
        $intensityCmd = $this->createCommand('set_intensity', 'action', 'slider', 'Régler intensité');
        $intensityCmd->setConfiguration('minValue', 6);
        $intensityCmd->setConfiguration('maxValue', 32);
        $intensityCmd->save();
        
        // Commande select pour le mode de charge
        $modeCmd = $this->createCommand('set_mode', 'action', 'select', 'Mode de charge');
        $modeCmd->setConfiguration('listValue', 'stop|Stop;charge|Charge;dynamic|Dynamique;solar|Solaire');
        $modeCmd->save();

        // Commandes pour la gestion RFID
        $this->createCommand('rfid_enable', 'action', 'other', 'Activer RFID');
        $this->createCommand('rfid_disable', 'action', 'other', 'Désactiver RFID');

        // Commandes pour les profils de puissance
        $this->createCommand('power_profile_save', 'action', 'message', 'Sauver profil');
        $this->createCommand('power_profile_list', 'info', 'string', 'Liste profils');
        $this->createCommand('power_profile_delete', 'action', 'message', 'Supprimer profil');

        // Commande pour la version du firmware
        $this->createCommand('firmware_version', 'info', 'string', 'Version firmware');

        // Commandes pour les statistiques
        $this->createCommand('stats_total_energy', 'info', 'numeric', 'Énergie totale', 'core::badge', 'kWh');
        $this->createCommand('stats_total_charges', 'info', 'numeric', 'Charges totales');
        $this->createCommand('stats_last_sessions', 'info', 'string', 'Dernières sessions');

        // Commandes pour les 5 dernières sessions
        for ($i = 0; $i < 5; $i++) {
            $prefix = 'session_' . $i . '_';
            $label = ($i == 0) ? "Dernière charge" : "Charge J-" . $i;
            $this->createCommand($prefix . 'debut', 'info', 'string', $label . ' - Début');
            $this->createCommand($prefix . 'fin', 'info', 'string', $label . ' - Fin');
            $this->createCommand($prefix . 'duree', 'info', 'string', $label . ' - Durée');
            $this->createCommand($prefix . 'energie', 'info', 'numeric', $label . ' - Énergie', 'core::badge', 'kWh');
            $this->createCommand($prefix . 'cout', 'info', 'numeric', $label . ' - Coût', 'core::badge', '€');
            $this->createCommand($prefix . 'badge', 'info', 'string', $label . ' - Badge RFID');
            $this->createCommand($prefix . 'message', 'info', 'string', $label . ' - Message');
        }
        
        // Premier refresh après création
        if ($this->getConfiguration('first_sync', true)) {
            $this->refresh();
            $this->setConfiguration('first_sync', false);
            $this->save();
        }
    }
    
    public function postUpdate() {
        $this->refresh();
    }
    
    private function createCommand($logicalId, $type, $subType, $name, $template = null, $unit = null) {
        $cmd = $this->getCmd(null, $logicalId);
        if (!is_object($cmd)) {
            $cmd = new v2c_trydanCmd();
            $cmd->setLogicalId($logicalId);
            $cmd->setName(__($name, __FILE__));
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType($type);
            $cmd->setSubType($subType);
            
            if ($type == 'info') {
                $cmd->setIsVisible(1);
                $cmd->setIsHistorized(in_array($logicalId, ['power', 'energy', 'intensity', 'charge_energy']));
                if ($template) {
                    $cmd->setTemplate('dashboard', $template);
                    $cmd->setTemplate('mobile', $template);
                }
                if ($unit) {
                    $cmd->setUnite($unit);
                }
            } else {
                $cmd->setIsVisible(1);
            }
            
            $cmd->save();
        }
        return $cmd;
    }
    
    public function refresh() {
        try {
            $deviceId = $this->getConfiguration('charger_id');
            $this->isConnected(); // Vérifie d'abord l'état de connexion
            $data = $this->callAPI('POST', '/device/currentstatecharge?deviceId=' . $deviceId);
            
            if ($data) {
                // Mise à jour des statuts
                $charge_state = $data['charge_state'] ?? 0;
                $status = 'unknown';
                switch($charge_state) {
                    case 0: $status = 'disconnected'; break;
                    case 1: $status = 'connected'; break;
                    case 2: $status = 'charging'; break;
                    case 3: $status = 'paused'; break;
                    case 4: $status = 'error'; break;
                }
                
                $this->updateCommand('status', $status);
                $this->updateCommand('power', $data['power'] ?? 0);
                $this->updateCommand('energy', $data['energy'] ?? 0);
                $this->updateCommand('intensity', $data['intensity'] ?? 0);
                $this->updateCommand('locked', $data['error'] == '244' ? true : false);
                $this->updateCommand('paused', $charge_state == 3);
                $this->updateCommand('dynamic_mode', $data['photovoltaic_on'] ?? false);
                
                // Informations de session
                $this->updateCommand('charge_time', $data['seconds'] ? round($data['seconds'] / 60) : 0);
                $this->updateCommand('charge_energy', $data['energy'] ?? 0);
                
                // Informations additionnelles
                $this->updateCommand('voltage', $data['voltage'] ?? 0);
                $this->updateCommand('house_power', $data['house_power'] ?? 0);
                $this->updateCommand('sun_power', $data['sun_power'] ?? 0);
                
                log::add('v2c_trydan', 'debug', 'Refresh réussi pour ' . $this->getName());
            }
        } catch (Exception $e) {
            log::add('v2c_trydan', 'error', 'Erreur refresh: ' . $e->getMessage());
        }
    }
    
    private function updateCommand($logicalId, $value) {
        $cmd = $this->getCmd(null, $logicalId);
        if (is_object($cmd)) {
            $this->checkAndUpdateCmd($logicalId, $value);
        }
    }
    
    public function callAPI($method, $endpoint, $data = null) {
        $token = $this->getConfiguration('api_token');
        if (empty($token)) {
            throw new Exception(__('Token API non configuré', __FILE__));
        }
        
        $url = self::API_BASE_URL . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $token,
            'Content-Type: application/json'
        ]);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Erreur cURL: ' . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception('Erreur API (HTTP ' . $httpCode . '): ' . $response);
        }
        
        return json_decode($response, true);
    }
    
    public function startCharging() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/startcharge?deviceId=' . $deviceId);
    }
    
    public function stopCharging() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/pausecharge?deviceId=' . $deviceId);
    }
    
    public function pauseCharging() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/pausecharge?deviceId=' . $deviceId);
    }
    
    public function resumeCharging() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/startcharge?deviceId=' . $deviceId);
    }
    
    public function lockCharger() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/locked?deviceId=' . $deviceId . '&value=1');
    }
    
    public function unlockCharger() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/locked?deviceId=' . $deviceId . '&value=0');
    }
    
    public function setIntensity($value) {
        $deviceId = $this->getConfiguration('charger_id');
        $value = max(6, min(32, intval($value)));
        return $this->callAPI('POST', '/device/intensity?deviceId=' . $deviceId . '&value=' . $value);
    }
    
    public function setMode($mode) {
        $deviceId = $this->getConfiguration('charger_id');
        switch($mode) {
            case 'stop':
                return $this->stopCharging();
            case 'charge':
                return $this->startCharging();
            case 'dynamic':
                return $this->callAPI('POST', '/device/dynamic?deviceId=' . $deviceId . '&value=1');
            case 'solar':
                return $this->callAPI('POST', '/device/chargefvmode?deviceId=' . $deviceId . '&value=1');
        }
    }

    // Méthodes RFID
    public function enableRFID() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/set_rfid?deviceId=' . $deviceId . '&value=1');
    }

    public function disableRFID() {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('POST', '/device/set_rfid?deviceId=' . $deviceId . '&value=0');
    }


    // Méthodes Profils de puissance
    public function savePowerProfile($name, $mode, $value) {
        $deviceId = $this->getConfiguration('charger_id');
        $data = [
            'mode' => $mode,
            'value' => intval($value)
        ];
        return $this->callAPI('POST', '/device/savepersonalicepower/v2?deviceId=' . $deviceId . 
            '&name=' . urlencode($name) . '&updateAt=' . urlencode(date('c')), $data);
    }

    public function getPowerProfiles() {
        $deviceId = $this->getConfiguration('charger_id');
        $response = $this->callAPI('GET', '/device/personalicepower/all?deviceId=' . $deviceId);
        $this->updateCommand('power_profile_list', json_encode($response));
        return $response;
    }

    public function deletePowerProfile($name) {
        $deviceId = $this->getConfiguration('charger_id');
        return $this->callAPI('DELETE', '/device/personalicepower/v2?deviceId=' . $deviceId . 
            '&name=' . urlencode($name) . '&updateAt=' . urlencode(date('c')));
    }

    // Méthode Firmware
    public function getFirmwareVersion() {
        $deviceId = $this->getConfiguration('charger_id');
        $response = $this->callAPI('GET', '/version?deviceId=' . $deviceId);
        if (isset($response['versionId'])) {
            $this->updateCommand('firmware_version', $response['versionId']);
        }
        return $response;
    }

    // Méthode de connexion
    public function isConnected() {
        $deviceId = $this->getConfiguration('charger_id');
        $response = $this->callAPI('GET', '/device/connected?deviceId=' . $deviceId);
        $this->updateCommand('connected', $response['connected'] ?? false);
        return $response;
    }

    // Méthodes Statistiques
    public function getGlobalStats() {
        $response = $this->callAPI('GET', '/stadistic/global/me');
        if (isset($response[0])) {
            $this->updateCommand('stats_total_energy', $response[0]['totalEnergy'] ?? 0);
            $this->updateCommand('stats_total_charges', $response[0]['totalCharges'] ?? 0);
        }
        return $response;
    }

    public function getSessionStats() {
        $deviceId = $this->getConfiguration('charger_id');
        $response = $this->callAPI('GET', '/stadistic/device?deviceId=' . $deviceId);
        
        // Trier les sessions par date (la plus récente en premier)
        usort($response, function($a, $b) {
            $dateA = new DateTime($a['startChargeDate']);
            $dateB = new DateTime($b['startChargeDate']);
            return $dateB <=> $dateA;
        });
        
        // Limiter aux 5 dernières sessions
        $response = array_slice($response, 0, 5);
        
        // Mettre à jour les commandes d'information pour chaque session
        foreach ($response as $index => $session) {
            $prefix = 'session_' . $index . '_';
            $startDate = new DateTime($session['startChargeDate']);
            $endDate = new DateTime($session['endChargeDate']);
            
            // Mise à jour des commandes
            $this->updateCommand($prefix . 'debut', $startDate->format('H:i'));
            $this->updateCommand($prefix . 'fin', $endDate->format('H:i'));
            $this->updateCommand($prefix . 'duree', $endDate->diff($startDate)->format('%H:%I'));
            $this->updateCommand($prefix . 'energie', $session['energy']);
            $this->updateCommand($prefix . 'cout', $session['cost']);
            $this->updateCommand($prefix . 'badge', $session['rfidName'] ?? '');
            $this->updateCommand($prefix . 'message', $session['message'] ?? '');
        }
        
        // Effacer les valeurs des sessions plus anciennes si moins de 5 sessions
        for ($i = count($response); $i < 5; $i++) {
            $prefix = 'session_' . $i . '_';
            $this->updateCommand($prefix . 'debut', '');
            $this->updateCommand($prefix . 'fin', '');
            $this->updateCommand($prefix . 'duree', '');
            $this->updateCommand($prefix . 'energie', 0);
            $this->updateCommand($prefix . 'cout', 0);
            $this->updateCommand($prefix . 'badge', '');
            $this->updateCommand($prefix . 'message', '');
        }
        
        // Conserver aussi l'ancien format pour compatibilité
        $formattedSessions = array_map(function($session) {
            $startDate = new DateTime($session['startChargeDate']);
            $endDate = new DateTime($session['endChargeDate']);
            
            return [
                'debut' => $startDate->format('H:i'),
                'fin' => $endDate->format('H:i'),
                'duree' => $endDate->diff($startDate)->format('%H:%I'),
                'energie' => $session['energy'],
                'cout' => $session['cost'],
                'badge' => $session['rfidName'] ?? '',
                'message' => $session['message'] ?? ''
            ];
        }, $response);
        
        $this->updateCommand('stats_last_sessions', json_encode($formattedSessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $formattedSessions;
    }
}

class v2c_trydanCmd extends cmd {
    
    public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        
        switch ($this->getLogicalId()) {
            // Commandes de base
            case 'refresh':
                $eqLogic->refresh();
                $eqLogic->getFirmwareVersion();
                $eqLogic->getGlobalStats();
                $eqLogic->getSessionStats();
                break;
                
            case 'start':
                $eqLogic->startCharging();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'stop':
                $eqLogic->stopCharging();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'pause':
                $eqLogic->pauseCharging();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'resume':
                $eqLogic->resumeCharging();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'lock':
                $eqLogic->lockCharger();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'unlock':
                $eqLogic->unlockCharger();
                sleep(2);
                $eqLogic->refresh();
                break;
                
            case 'set_intensity':
                if (isset($_options['slider'])) {
                    $eqLogic->setIntensity($_options['slider']);
                    sleep(2);
                    $eqLogic->refresh();
                }
                break;
                
            case 'set_mode':
                if (isset($_options['select'])) {
                    $eqLogic->setMode($_options['select']);
                    sleep(2);
                    $eqLogic->refresh();
                }
                break;

            // Commandes RFID
            case 'rfid_enable':
                $eqLogic->enableRFID();
                sleep(2);
                break;

            case 'rfid_disable':
                $eqLogic->disableRFID();
                sleep(2);
                break;

            // Commandes profils de puissance
            case 'power_profile_save':
                if (isset($_options['title']) && !empty($_options['title']) && isset($_options['message'])) {
                    list($mode, $value) = explode('|', $_options['message']);
                    $eqLogic->savePowerProfile($_options['title'], $mode, $value);
                    sleep(2);
                    $eqLogic->getPowerProfiles();
                }
                break;

            case 'power_profile_delete':
                if (isset($_options['title']) && !empty($_options['title'])) {
                    $eqLogic->deletePowerProfile($_options['title']);
                    sleep(2);
                    $eqLogic->getPowerProfiles();
                }
                break;


        }
    }
}
?>
