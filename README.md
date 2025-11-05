# Guide d'Installation du Plugin V2C Trydan pour Jeedom

## 📦 Vue d'ensemble

Ce plugin permet de contrôler et superviser votre borne de recharge V2C Trydan via l'API Cloud V2C directement depuis Jeedom.

## 🎯 Fonctionnalités

- ✅ Supervision en temps réel (puissance, énergie, intensité, état)
- ✅ Contrôle à distance (démarrer, arrêter, pause, reprendre)
- ✅ Réglage de l'intensité de charge (6-32A)
- ✅ Changement de mode de charge (Stop/Charge/Dynamique/Solaire)
- ✅ Verrouillage/déverrouillage de la borne
- ✅ Historisation des données
- ✅ Compatible avec les scénarios Jeedom
- ✅ Support multilingue (Français, Anglais)

## 📋 Prérequis

- Jeedom 4.0 ou supérieur
- Une borne V2C Trydan connectée au Cloud V2C
- Un compte V2C Cloud actif
- Un token API V2C Cloud
- PHP avec support cURL

## 🔧 Installation dans Jeedom

1. Connectez-vous à votre Jeedom
2. Allez dans **Plugins** > **Gestion des plugins**
3. Cliquez sur le bouton **+** (Ajouter un plugin)
4. Sélectionnez **"Installer à partir d'une archive"**
5. Uploadez le fichier
6. Attendez la fin de l'installation
7. Activez le plugin

Si vous n'avez pas l'option "Fichier", il faut alors l'activer dans : **Réglages** > **Système** > **Configuration** > **Mises a jour/Market** > **Fichier** et cocher la case pour autoriser l'option.

## ⚙️ Configuration

### Obtenir votre token API V2C

1. Connectez-vous sur [v2c.cloud](https://v2c.cloud)
2. Allez dans **Paramètres** > **API**
3. Générez un nouveau token ou copiez votre token existant
4. Notez également l'**ID de votre chargeur** (visible dans les détails de votre borne)

### Configurer l'équipement

1. Dans Jeedom, allez dans **Plugins** > **Energie** > **V2C Trydan**
2. Cliquez sur **Ajouter**
3. Donnez un **nom** à votre équipement (ex: "Borne Garage")
4. Sélectionnez l'**objet parent**
5. Cochez **Activer** et **Visible**
6. Dans **Configuration V2C Cloud** :
   - Collez votre **Token API**
   - Entrez l'**ID du chargeur**
   - Choisissez la **fréquence de rafraîchissement** :
     - Toutes les 5 minutes (suivi en temps quasi-réel)
     - Toutes les 15 minutes (recommandé)
     - Toutes les heures (surveillance basique)
7. Cliquez sur **Sauvegarder**

Le plugin créera automatiquement toutes les commandes nécessaires.

## 📊 Commandes disponibles

Certaines commandes peuvent ne pas être remontées dans l'immédiat. Cela dépend du cron que vous avez choisi dans la configuration.

### Commandes Info principales
- **Connecté** : État de connexion de la borne
- **État** : État actuel de la borne
- **Puissance** : Puissance de charge (kW)
- **Énergie** : Énergie totale (kWh)
- **Intensité** : Intensité de charge (A)
- **Tension** : Tension réseau (V)
- **Verrouillé** : État du verrouillage
- **En pause** : État de pause
- **Mode dynamique** : Mode dynamique actif
- **Temps de charge** : Durée session (min)
- **Énergie session** : Énergie session (kWh)

### Commandes Info photovoltaïques
- **Puissance maison** : Consommation totale maison (kW)
- **Puissance solaire** : Production solaire (kW)

### Commandes Info profils
- **Liste profils** : Liste des profils de puissance (JSON)

### Commandes Info firmware
- **Version firmware** : Version actuelle du firmware

### Commandes Info statistiques
- **Énergie totale** : Total kWh chargés
- **Charges totales** : Nombre total de sessions
- **Dernières sessions** : Historique des charges (JSON)

### Commandes Info sessions de charge
Pour chacune des 5 dernières sessions (de 0 à 4, où 0 est la plus récente) :
- **session_X_debut** : Heure de début de la charge (HH:mm)
- **session_X_fin** : Heure de fin de la charge (HH:mm)
- **session_X_duree** : Durée de la charge (HH:MM)
- **session_X_energie** : Énergie consommée (kWh)
- **session_X_cout** : Coût de la session (€)
- **session_X_badge** : Badge RFID utilisé
- **session_X_message** : Message associé à la session

Exemple d'utilisation dans les scénarios :
```
SI [Borne][session_0_energie] > 10
ALORS Envoyer notification "Charge importante : [Borne][session_0_energie] kWh"
```

### Commandes Action
- **Rafraîchir** : Mise à jour manuelle
- **Activer RFID** : Active le module RFID
- **Désactiver RFID** : Désactive le module RFID
- **Démarrer** : Démarrer la charge
- **Arrêter** : Arrêter la charge
- **Pause** : Mettre en pause
- **Reprendre** : Reprendre la charge
- **Verrouiller** : Verrouiller la borne
- **Déverrouiller** : Déverrouiller la borne
- **Régler intensité** : Définir l'intensité (6-32A)
- **Mode de charge** : Changer le mode (Stop/Charge/Dynamique/Solaire)

### Commandes Action RFID
- **Activer RFID** : Active le lecteur RFID
- **Désactiver RFID** : Désactive le lecteur RFID

### Commandes Action profils
- **Sauver profil** : Crée un profil (title=nom, message=mode|valeur)
- **Supprimer profil** : Supprime un profil (message=nom)



## 🎬 Exemples de scénarios

### Scénarios de charge basiques

#### Démarrer la charge en heures creuses
```
SI [Tarif EDF][Mode] == "Heures Creuses"
ALORS [Borne Garage][Démarrer]
```

#### Charge intelligente selon production solaire
```
SI [Borne Garage][Puissance solaire] > 3.0
ALORS [Borne Garage][Régler intensité] = 20
SINON SI [Borne Garage][Puissance solaire] > 1.5
ALORS [Borne Garage][Régler intensité] = 10
SINON [Borne Garage][Pause]
```

#### Arrêt automatique si charge complète
```
SI [Borne Garage][Énergie session] >= 50
ALORS [Borne Garage][Arrêter]
ET Envoyer notification "Charge arrêtée à 50 kWh"
```

### Scénarios de gestion de puissance

#### Profil été/hiver
```
SI [Système][Mois] >= "04" ET [Système][Mois] <= "09"
ALORS [Borne Garage][Sauver profil] = "Été|solar|32"
SINON [Borne Garage][Sauver profil] = "Hiver|dynamic|16"
```

#### Adaptation à la consommation maison
```
SI [Borne Garage][Puissance maison] > 8.0
ALORS [Borne Garage][Régler intensité] = 16
SINON [Borne Garage][Régler intensité] = 32
```

#### Rapport hebdomadaire
```
A PROGRAMMATION
# Chaque dimanche soir
VAR sessions = [Borne Garage][Dernières sessions]
VAR total = [Borne Garage][Énergie totale]
Envoyer notification "📊 Rapport hebdo :\n
Total : {total} kWh\n
Sessions : {sessions}"
```

## 🔔 Notifications
### Notification de début de charge
```javascript
SI [Borne Garage][État] == "charging"
ET [Borne Garage][État] != "charging" (il y a 1 minute)
ALORS Envoyer notification "⚡ Charge démarrée"
```

### Alerte en cas d'erreur
```javascript
SI [Borne Garage][État] == "error"
ALORS Envoyer notification "⚠️ Erreur détectée sur la borne"
```

### Rapport quotidien
```javascript
TOUS LES JOURS à 23:55
Envoyer notification "📊 Rapport du jour : {[Borne Garage][Énergie]} kWh chargés"
```

## 🔐 Scénarios de sécurité
### Protection surcharge réseau
```
SI [Borne Garage][Puissance maison] > 9.0
ET [Borne Garage][État] == "charging"
ALORS
  [Borne Garage][Pause]
  Envoyer notification "⚠️ Pause charge - Surcharge réseau"
```

## 📈 Historisation

Pour historiser les données :

1. Allez dans l'onglet **Commandes** de votre équipement
2. Cochez **Historiser** pour les commandes souhaitées (recommandé : puissance, énergie, intensité)
3. Configurez la durée de rétention dans **Configuration** > **Historique**

## 🐛 Dépannage

### La borne n'apparaît pas
- ✅ Vérifiez le token API
- ✅ Vérifiez l'ID du chargeur
- ✅ Assurez-vous que la borne est connectée au Cloud V2C
- ✅ Consultez les logs : **Analyse** > **Logs** > **v2c_trydan**

### Les données ne se mettent pas à jour
- ✅ Vérifiez la fréquence de rafraîchissement
- ✅ Essayez un rafraîchissement manuel
- ✅ Vérifiez les crons Jeedom : **Configuration** > **Moteur de tâches**

### Erreur d'authentification
- ✅ Générez un nouveau token sur v2c.cloud
- ✅ Mettez à jour le token dans la configuration

### Commandes qui ne fonctionnent pas
- ✅ Vérifiez que la borne est bien en ligne
- ✅ Vérifiez les permissions de votre token API
- ✅ Consultez les logs pour les messages d'erreur

## 🎛️ Widget personnalisé

Le plugin utilise des templates par défaut, mais vous pouvez les personnaliser :

1. Allez dans **Outils** > **Widgets**
2. Créez un nouveau widget pour le type `v2c_trydan`
3. Personnalisez l'affichage selon vos besoins



# Rentrons dans la technique
## 🌐 APIs V2C utilisées

Le plugin utilise les endpoints suivants de l'API V2C Cloud :

- `POST /device/currentstatecharge` - État actuel de la charge
- `GET /device/connected` - État de connexion de la borne
- `GET /version` - Version du firmware
- `POST /device/startcharge` - Démarrer la charge
- `POST /device/pausecharge` - Mettre en pause
- `POST /device/locked` - Verrouiller/déverrouiller
- `POST /device/intensity` - Régler l'intensité
- `POST /device/dynamic` - Mode dynamique
- `POST /device/chargefvmode` - Mode solaire
- `POST /device/personalicepower/v2` - Gestion des profils
- `GET /stadistic/global/me` - Statistiques globales
- `GET /stadistic/device` - Sessions de charge
- `POST /chargers/{id}/pause` - Mettre en pause
- `POST /chargers/{id}/resume` - Reprendre la charge
- `POST /chargers/{id}/lock` - Verrouiller
- `POST /chargers/{id}/unlock` - Déverrouiller
- `PUT /chargers/{id}/intensity` - Régler l'intensité
- `PUT /chargers/{id}/mode` - Changer le mode


## 🛠️ Développement et contribution
Si vous découvrez un bug ou une fonctionnalité manquante, vous pouvez contribuer à son amélioration.

## 🤝 Support et communauté

- **Forum Jeedom** : [community.jeedom.com](https://community.jeedom.com)
- **Documentation V2C** : [v2charge.com/support](https://v2charge.com/fr/support/)
- **API V2C** : Contactez le support V2C pour accès API

## 📜 Licence

Ce plugin est distribué sous licence **AGPL-3.0**.