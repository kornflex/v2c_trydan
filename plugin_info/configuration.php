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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>

<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Configuration globale}}</label>
            <div class="col-lg-8">
                <div class="alert alert-info">
                    {{La configuration s'effectue au niveau de chaque équipement}}
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Timeout API (secondes)}}</label>
            <div class="col-lg-2">
                <input type="number" class="configKey form-control" data-l1key="api_timeout" placeholder="30" />
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mode debug}}</label>
            <div class="col-lg-8">
                <input type="checkbox" class="configKey" data-l1key="debug" />
                <span class="help-block">{{Active les logs détaillés pour le débogage}}</span>
            </div>
        </div>
    </fieldset>
</form>
