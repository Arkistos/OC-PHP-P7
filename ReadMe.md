# BileMo Api Documentation 

## Sommaire

1. [Accès à l'API](##1.Introduction)
2. [Connexion](##2.Connexion)
3. [Endpoints](##3.Endpoints)
4. [Réponses](##4.Réponses)


## 1.Accès à l'API

Pour accéder à l'API BileMo, vous devez obtenir des identifiants d'accès. Veuillez contacter notre équipe de support ou administrateurs pour obtenir ces informations.

## 2.Connexion

La connexion est requise pour toutes les requêtes à l'API. Nous utilisons le protocole JWToken pour garantir la sécurité de vos données. Vous devrez inclure un jeton d'accès valide dans l'en-tête de chaque requête. Les détails sur la façon d'obtenir un jeton d'accès sont disponibles dans la documentation d'authentification fournie avec vos identifiants.

## 3.Endpoints

- GET /api/products : Récupérer la liste des téléphones.
- GET /api/products/{id} : Récupérer les détails d'un téléphone.
- GET /api/users : Récupérer la liste des utilisateurs.
- GET /api/users/{id} : Récupérer les détails d'un utilisateur.
- POST /api/users : Ajouter un utilisateur.
- DELETE /api/users/{id} : Supprimer un utilisateur.

## 4.Réponses

Les réponses de l'API sont au format JSON. Lorsque vous effectuez des requêtes, assurez-vous de vérifier les codes d'état HTTP pour déterminer le résultat de votre requête. Les erreurs sont accompagnées de messages explicatifs pour vous aider à résoudre les problèmes.

