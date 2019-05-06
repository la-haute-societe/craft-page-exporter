# TODO

- OK - Flat path : nom-image-hash.ext : 
- pageExporter.getUrl()
- OK - Prefix CDN - Mois de la page dans l'URL
- Interface de config par page
- Inline scripts


## Lifecycle

### 1. Création
Pour chaque asset, tant qu'il y des children
- Récupérer contenu
- Parser
- Créer les children

### 2. Inline
- Parcourir les assets, inline dans initiator, remplacer URL des children, puis supprimer l'asset

### 3. Export
- Remplacer URL : préfixe
- Mettre à jour l'exportPath


## Transform

### Comment récupérer le contenu
``getFileGetContentsPath``

### Où enregistrer le fichier
``getExportPath``

- flatten path
- rename file : hash

### Préfixer l'URL
``getExportUrl``

- CDN
- mois/année

### Inline
- styles
- scripts
- images ?