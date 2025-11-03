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
    
    const API_BASE_URL = 'https://v2c.cloud/api/v1';
    
    /*     * ***********************Méthodes statiques*************************** */
    
    public static function cronHourly() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            $eqLogic->refresh();
        }
    }
    
    public static function cron15() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('refresh_frequency', 'hourly') == '15min') {
                $eqLogic->refresh();
            }
        }
    }
    
    public static function cron5() {
        foreach (eqLogic::byType('v2c_trydan', true) as $eqLogic) {
            if ($eqLogic->getConfiguration('refresh_frequency', 'hourly') == '5min') {
                $eqLogic->refresh();
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
        // Commandes Info
        $this->createCommand('status', 'info', 'string', 'État', 'core::tile');
        $this->createCommand('power', 'info', 'numeric', 'Puissance', 'core::badge', 'W');
        $this->createCommand('energy', 'info', 'numeric', 'Énergie', 'core::badge', 'kWh');
        $this->createCommand('intensity', 'info', 'numeric', 'Intensité', 'core::badge', 'A');
        $this->createCommand('locked', 'info', 'binary', 'Verrouillé');
        $this->createCommand('paused', 'info', 'binary', 'En pause');
        $this->createCommand('dynamic_mode', 'info', 'binary', 'Mode dynamique');
        $this->createCommand('charge_time', 'info', 'numeric', 'Temps de charge', 'core::badge', 'min');
        $this->createCommand('charge_energy', 'info', 'numeric', 'Énergie session', 'core::badge', 'kWh');
        
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
            $data = $this->callAPI('GET', '/chargers/' . $this->getConfiguration('charger_id'));
            
            if ($data) {
                $this->updateCommand('status', $data['status'] ?? 'unknown');
                $this->updateCommand('power', $data['power'] ?? 0);
                $this->updateCommand('energy', $data['total_energy'] ?? 0);
                $this->updateCommand('intensity', $data['current'] ?? 0);
                $this->updateCommand('locked', $data['locked'] ?? false);
                $this->updateCommand('paused', $data['paused'] ?? false);
                $this->updateCommand('dynamic_mode', $data['dynamic_mode'] ?? false);
                
                // Informations de session de charge
                if (isset($data['session'])) {
                    $this->updateCommand('charge_time', $data['session']['duration'] ?? 0);
                    $this->updateCommand('charge_energy', $data['session']['energy'] ?? 0);
                }
                
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
            'Authorization: Bearer ' . $token,
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
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/start');
    }
    
    public function stopCharging() {
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/stop');
    }
    
    public function pauseCharging() {
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/pause');
    }
    
    public function resumeCharging() {
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/resume');
    }
    
    public function lockCharger() {
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/lock');
    }
    
    public function unlockCharger() {
        return $this->callAPI('POST', '/chargers/' . $this->getConfiguration('charger_id') . '/unlock');
    }
    
    public function setIntensity($value) {
        $value = max(6, min(32, intval($value)));
        return $this->callAPI('PUT', '/chargers/' . $this->getConfiguration('charger_id') . '/intensity', ['value' => $value]);
    }
    
    public function setMode($mode) {
        return $this->callAPI('PUT', '/chargers/' . $this->getConfiguration('charger_id') . '/mode', ['mode' => $mode]);
    }
}

class v2c_trydanCmd extends cmd {
    
    public function execute($_options = null) {
        $eqLogic = $this->getEqLogic();
        
        switch ($this->getLogicalId()) {
            case 'refresh':
                $eqLogic->refresh();
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
        }
    }
}
?>
