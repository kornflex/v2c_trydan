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

$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = { configuration: {} };
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td style="min-width:50px;width:70px;">';
    tr += '<span class="cmdAttr" data-l1key="id"></span>';
    tr += '</td>';
    
    tr += '<td style="min-width:200px;width:350px;">';
    tr += '<div class="input-group">';
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">';
    tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>';
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>';
    tr += '</div>';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">';
    tr += '<option value="">{{Aucune}}</option>';
    tr += '</select>';
    tr += '</td>';
    
    tr += '<td style="width:130px;">';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    
    tr += '<td style="min-width:260px;">';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> ';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/>{{Historiser}}</label> ';
    tr += '<div style="margin-top:5px;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;margin-right:2px;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;margin-right:2px;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="{{Unité}}" style="width:30%;display:inline-block;">';
    tr += '</div>';
    tr += '</td>';
    
    tr += '<td style="min-width:80px;width:200px;">';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure" title="{{Configuration avancée}}"><i class="fas fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test" title="{{Tester}}"><i class="fas fa-rss"></i></a> ';
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i>';
    tr += '</td>';
    tr += '</tr>';
    
    $('#table_cmd tbody').append(tr);
    var tr = $('#table_cmd tbody tr').last();
    jeedom.eqLogic.buildSelectCmd({
        id: $('.eqLogicAttr[data-l1key=id]').value(),
        filter: { type: 'info' },
        error: function (error) {
            $('#div_alert').showAlert({ message: error.message, level: 'danger' });
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result);
            tr.setValues(_cmd, '.cmdAttr');
            jeedom.cmd.changeType(tr, init(_cmd.subType));
        }
    });
}

$('.eqLogicAttr[data-l1key=configuration][data-l2key=api_token]').on('change', function() {
    if ($(this).val() != '') {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=charger_id]').closest('.form-group').show();
    }
});

$('#bt_healthv2c_trydan').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé V2C Trydan}}"});
    $('#md_modal').load('index.php?v=d&plugin=v2c_trydan&modal=health').dialog('open');
});

$('.pluginDisplayCard[data-plugin_id=v2c_trydan]').on('click', function () {
    $('#md_modal').dialog({title: "{{V2C Trydan}}"});
    $('#md_modal').load('index.php?v=d&plugin=v2c_trydan&modal=info').dialog('open');
});
