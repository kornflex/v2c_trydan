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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'testApiConnection') {
        $token = init('token');
        $chargerId = init('charger_id');
        
        if (empty($token)) {
            throw new Exception(__('Le token API est requis', __FILE__));
        }
        
        if (empty($chargerId)) {
            throw new Exception(__('L\'ID du chargeur est requis', __FILE__));
        }
        
        // Test de connexion
        $url = 'https://v2c.cloud/api/v1/chargers/' . $chargerId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Erreur de connexion: ' . $error);
        }
        
        if ($httpCode == 401) {
            throw new Exception(__('Token API invalide', __FILE__));
        }
        
        if ($httpCode == 404) {
            throw new Exception(__('Chargeur non trouvé', __FILE__));
        }
        
        if ($httpCode >= 400) {
            throw new Exception('Erreur API (HTTP ' . $httpCode . ')');
        }
        
        $data = json_decode($response, true);
        
        ajax::success([
            'message' => __('Connexion réussie!', __FILE__),
            'charger_name' => $data['name'] ?? 'Trydan',
            'status' => $data['status'] ?? 'unknown'
        ]);
    }

    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
?>
