# Plugin Jeedom V2C Trydan

Plugin pour piloter et superviser les bornes de recharge V2C Trydan via l'API Cloud V2C.

## API utilisées

Le plugin utilise les API V2C Cloud suivantes :
- /device/currentstatecharge - État actuel de la charge
- /device/connected - État de connexion de la borne
- /device/set_rfid - Activation/désactivation RFID
- /version - Version du firmware
- /device/startcharge - Démarrer la charge
- /device/pausecharge - Mettre en pause
- /device/locked - Verrouiller/déverrouiller
- /device/intensity - Régler l'intensité
- /device/dynamic - Mode dynamique
- /device/chargefvmode - Mode solaire
- /device/personalicepower - Gestion des profils
- /stadistic/global/me - Statistiques globales
- /stadistic/device - Sessions de charge

## Fonctionnalités

### Supervision et contrôle de base
- Supervision en temps réel de la charge
- Contrôle à distance (démarrer, arrêter, pause)
- Réglage de l'intensité de charge
- Verrouillage/déverrouillage de la borne
- Changement de mode de charge (Stop, Charge, Dynamique, Solaire)
- Historisation des données
- Compatible avec les scénarios Jeedom

### Monitoring avancé
- Tension du réseau
- Puissance de charge actuelle
- Puissance consommée par la maison
- Production solaire (si installée)
- État détaillé de la borne
- Temps et énergie de la session en cours
- Version du firmware

### Profils de puissance
- Création de profils de charge personnalisés
- Gestion des modes solaires et dynamiques
- Liste des profils disponibles
- Suppression de profils

### Configuration réseau
- État de la connexion

### Gestion du firmware
- Affichage de la version actuelle

### Statistiques détaillées
- Énergie totale consommée
- Nombre total de sessions de charge
- Historique des dernières sessions
- Coûts et économies réalisées
- Détails des 5 dernières sessions de charge avec pour chacune :
  - Heure de début et de fin
  - Durée de la charge
  - Énergie consommée
  - Coût de la session
  - Badge RFID utilisé (si applicable)
  - Message associé

## Installation

1. Téléchargez le fichier ZIP du plugin
2. Dans Jeedom, allez dans Plugins > Gestion des plugins
3. Cliquez sur "Ajouter un plugin" (icône avec le +)
4. Sélectionnez "Installer à partir d'une archive"
5. Uploadez le fichier ZIP
6. Activez le plugin

## Configuration

### Configuration générale
1. Accédez à l'interface de configuration du plugin
2. Renseignez votre token API V2C (disponible sur v2c.cloud)
3. Renseignez l'ID de votre chargeur
4. Choisissez la fréquence de rafraîchissement (5min, 15min ou horaire)




### Configuration des profils de puissance
1. Accédez à la section "Profils"
2. Créez un nouveau profil en spécifiant :
   - Nom du profil
   - Mode (solaire/dynamique)
   - Valeur de puissance
3. Les profils peuvent être appliqués via les scénarios Jeedom

Voir la [documentation complète](docs/fr_FR/index.md) pour plus de détails.

## Intégration Jeedom

Le plugin expose toutes les fonctionnalités sous forme de commandes Jeedom, permettant :
- La création de scénarios complexes
- L'automatisation basée sur la production solaire
- La gestion des accès par badges
- Le suivi des consommations
- Les notifications d'état et d'erreurs

## Support

- Forum Jeedom: https://community.jeedom.com
- Documentation V2C: https://v2charge.com/fr/support/
- API V2C: https://api.v2charge.com/

## Licence

AGPL-3.0
