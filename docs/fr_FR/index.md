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
5. Uploadez le fichier **`v2c_trydan.zip`**
6. Attendez la fin de l'installation
7. Activez le plugin

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

### Commandes Info principales
- **Connecté** : État de connexion de la borne
- **RFID activé** : État d'activation du module RFID
- **Liste badges RFID** : Liste des badges RFID enregistrés
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

### Commandes Action
- **Rafraîchir** : Mise à jour manuelle
- **Activer RFID** : Active le module RFID
- **Désactiver RFID** : Désactive le module RFID
- **Ajouter badge RFID** : Mode apprentissage d'un nouveau badge
- **Supprimer badge RFID** : Supprime un badge existant
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

#### Notification fin de charge
```
SI [Borne Garage][État] == "completed"
ALORS Envoyer notification "🔋 Charge terminée : {[Borne Garage][Énergie session]} kWh"
```

#### Arrêt automatique si charge complète
```
SI [Borne Garage][Énergie session] >= 50
ALORS [Borne Garage][Arrêter]
ET Envoyer notification "Charge arrêtée à 50 kWh"
```

### Scénarios RFID avancés

#### Enregistrement automatique de badge
```
A PROGRAMMATION
# Le matin à 9h
[Borne Garage][Activer RFID]
[Borne Garage][Apprendre RFID] = "Badge Visiteur"
# Attendre 30 secondes que le badge soit présenté
PAUSE 30
[Borne Garage][Désactiver RFID]
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

### Scénarios RFID

#### Activation temporaire du RFID
```
SI [Borne Garage][État] == "connected"
ALORS
  [Borne Garage][Activer RFID]
  PAUSE 300 # 5 minutes
  [Borne Garage][Désactiver RFID]
```

#### Gestion des badges
```
A PROGRAMMATION
# Tous les lundis à 8h
[Borne Garage][Ajouter badge RFID] = "Badge Visiteur"
PAUSE 30
SI [Borne Garage][Liste badges RFID] contient "Badge Visiteur"
ALORS Envoyer notification "Badge ajouté avec succès"
```

### Scénarios de maintenance

#### Mise à jour firmware automatique
```
A PROGRAMMATION
# Tous les premiers du mois à 3h du matin
[Borne Garage][Mise à jour firmware]
PAUSE 300
SI [Borne Garage][Version firmware] a changé
ALORS Envoyer notification "✅ Mise à jour firmware réussie"
SINON Envoyer notification "❌ Échec mise à jour firmware"
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

### Scénarios de sécurité

#### Protection surcharge réseau
```
SI [Borne Garage][Puissance maison] > 9.0
ET [Borne Garage][État] == "charging"
ALORS
  [Borne Garage][Pause]
  Envoyer notification "⚠️ Pause charge - Surcharge réseau"
```



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

## 📁 Structure du plugin

```
v2c_trydan/
├── plugin_info/
│   ├── info.json                  # Métadonnées du plugin
│   ├── install.php                # Scripts d'installation/mise à jour
│   ├── configuration.php          # Page de configuration globale
│   └── v2c_trydan_icon.png       # Icône du plugin (512x512)
├── core/
│   ├── ajax/
│   │   └── v2c_trydan.ajax.php   # Appels AJAX
│   ├── class/
│   │   └── v2c_trydan.class.php  # Classe principale
│   └── i18n/
│       ├── fr_FR.json             # Traductions françaises
│       └── en_US.json             # Traductions anglaises
├── desktop/
│   ├── php/
│   │   └── v2c_trydan.php        # Interface principale
│   ├── js/
│   │   └── v2c_trydan.js         # JavaScript frontend
│   ├── css/
│   │   └── v2c_trydan.css        # Styles personnalisés
│   └── modal/
│       ├── info.v2c_trydan.php   # Modal d'informations
│       └── health.v2c_trydan.php # Modal de santé
├── docs/
│   ├── fr_FR/
│   │   ├── index.md              # Documentation française
│   │   └── changelog.md          # Historique des versions
│   └── en_US/
│       └── index.md              # Documentation anglaise
└── README.md                      # Fichier README
```

## 🔄 Mise à jour du plugin

Pour mettre à jour le plugin :

1. Modifiez le fichier `plugin_info/info.json` pour incrémenter la version
2. Mettez à jour le fichier `docs/fr_FR/changelog.md`
3. Recréez le ZIP :
```bash
bash create_plugin_zip.sh
```
4. Réinstallez via Jeedom

## 🌐 API V2C utilisée

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

## 🔐 Sécurité

- ✅ Le token API est stocké de manière sécurisée dans Jeedom
- ✅ Toutes les communications utilisent HTTPS
- ✅ Validation des données avant envoi à l'API
- ✅ Gestion des erreurs et timeout

## 📈 Historisation

Pour historiser les données :

1. Allez dans l'onglet **Commandes** de votre équipement
2. Cochez **Historiser** pour les commandes souhaitées (recommandé : puissance, énergie, intensité)
3. Configurez la durée de rétention dans **Configuration** > **Historique**

## 🔔 Notifications

Exemples de notifications :

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

## 🎛️ Widget personnalisé

Le plugin utilise des templates par défaut, mais vous pouvez les personnaliser :

1. Allez dans **Outils** > **Widgets**
2. Créez un nouveau widget pour le type `v2c_trydan`
3. Personnalisez l'affichage selon vos besoins

## 📊 Intégration avec d'autres plugins

### Plugin Mode (pour les heures creuses)
```javascript
SI [Mode][Mode actuel] == "Heures Creuses"
ALORS [Borne Garage][Mode de charge] = "charge"
ET [Borne Garage][Régler intensité] = 32
SINON [Borne Garage][Mode de charge] = "stop"
```

### Plugin Suivi Conso (pour le monitoring)
```javascript
TOUS LES 15 MINUTES
[Suivi Conso][Enregistrer] = [Borne Garage][Puissance]
```

### Plugin Telegram/Pushover (pour les notifications)
```javascript
SI [Borne Garage][Énergie session] >= 50
ALORS [Telegram][Envoyer] = "Charge complète atteinte!"
```

## 🧪 Tests et validation

### Test de connexion API

Après configuration, testez la connexion :

1. Sauvegardez l'équipement
2. Vérifiez que les commandes info se remplissent
3. Testez une commande action (ex: Rafraîchir)
4. Consultez les logs en cas de problème

### Vérification de santé

Utilisez le modal de santé :
- **Plugins** > **V2C Trydan** > Icône santé (cœur)
- Vérifiez l'état de tous vos équipements

## 🛠️ Développement et contribution

### Modifications du code

Si vous souhaitez modifier le plugin :

1. **Classe principale** : `core/class/v2c_trydan.class.php`
   - Méthodes API
   - Logique de rafraîchissement
   - Création des commandes

2. **Interface** : `desktop/php/v2c_trydan.php`
   - Formulaire de configuration
   - Affichage des équipements

3. **JavaScript** : `desktop/js/v2c_trydan.js`
   - Interactions utilisateur
   - Gestion des commandes

### Ajout de nouvelles commandes

Pour ajouter une commande :

1. Modifiez la méthode `postSave()` dans `v2c_trydan.class.php`
2. Ajoutez le cas dans la méthode `execute()` de `v2c_trydanCmd`
3. Testez la nouvelle commande

### Debug

Activez le mode debug :
1. **Configuration** > **Logs**
2. Niveau de log : **Debug** pour v2c_trydan
3. Consultez : **Analyse** > **Logs** > **v2c_trydan**

## 📝 Checklist avant publication

- [ ] Tous les fichiers créés
- [ ] Icône présente (512x512 PNG)
- [ ] Structure vérifiée (`verify_structure.sh`)
- [ ] Documentation complète
- [ ] Traductions FR/EN
- [ ] Tests réalisés sur Jeedom
- [ ] Changelog à jour
- [ ] README clair
- [ ] Licence AGPL-3.0

## 🤝 Support et communauté

- **Forum Jeedom** : [community.jeedom.com](https://community.jeedom.com)
- **Documentation V2C** : [v2charge.com/support](https://v2charge.com/fr/support/)
- **API V2C** : Contactez le support V2C pour accès API

## 📜 Licence

Ce plugin est distribué sous licence **AGPL-3.0**.

## 🙏 Remerciements

- Équipe Jeedom pour le framework
- V2C pour l'API Cloud
- Communauté Jeedom pour le support

## 📞 Contact

Pour toute question ou suggestion, utilisez :
- Le forum Jeedom
- Les issues GitHub (si le plugin est publié)

---

## 🚀 Récapitulatif des étapes

### Installation rapide (3 minutes)

```bash
# 1. Télécharger tous les scripts
# 2. Rendre exécutables
chmod +x *.sh

# 3. Créer l'icône (optionnel avec ImageMagick)
cd v2c_trydan
bash create_icon.sh
cd ..

# 4. Ou créer manuellement l'icône PNG 512x512
# Placer dans: v2c_trydan/plugin_info/v2c_trydan_icon.png

# 5. Vérifier la structure
bash verify_structure.sh

# 6. Créer le ZIP
bash create_plugin_zip.sh

# 7. Installer dans Jeedom
# Upload du fichier v2c_trydan.zip via l'interface
```

### Configuration rapide (2 minutes)

1. **Obtenir le token** : [v2c.cloud](https://v2c.cloud) > API
2. **Créer équipement** : Plugins > V2C Trydan > Ajouter
3. **Configurer** : Token + ID Chargeur
4. **Sauvegarder** : Les commandes se créent automatiquement
5. **Tester** : Cliquer sur "Rafraîchir"

### Premier scénario (1 minute)

```
SI [Heure] == "02:00"
ALORS [Borne Garage][Démarrer]
```

**Et voilà ! Votre borne est maintenant pilotée par Jeedom ! 🎉**

---

## 📧 Support rapide

**Problème courant** | **Solution**
---|---
Borne non trouvée | Vérifier token et ID
Pas de mise à jour | Vérifier fréquence refresh
Erreur API | Régénérer le token
Icône manquante | Créer PNG 512x512

**Logs à consulter** : Analyse > Logs > v2c_trydan

**Forum Jeedom** : Chercher "V2C Trydan" ou créer un sujet

---

**Version du guide** : 1.0  
**Dernière mise à jour** : 2025  
**Compatible** : Jeedom 4.0+
