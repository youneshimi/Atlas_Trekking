<p align="center">
  <img src="https://i.imgur.com/b9zsbzn.png" alt="Logo Flutter" width="200px">
</p>

# Atlas Trekking 🏔️

Atlas Trekking est un projet Symfony 6 qui permet aux gestionnaires d'une agence d'ajouter les dernières activités de l'agence dans trois catégories différentes : Activité culturelle, Activité de montagne et Activité de bien-être.

Le but principal de ce projet est d'appliquer la méthode CRUD en Symfony pour les activités publiées et les commentaires. Le projet est également configuré avec un serveur SMTP pour l'inscription de nouveaux adresses mail.

## Technologies Utilisées 💻

Le projet Atlas Trekking utilise les technologies suivantes:

- PHP 🐘
- JavaScript 🚀
- Bootstrap 🔧
- HTML 🌐
- CSS 🎨
- Twig 🌳

## Fonctionnalités ⚙️

- Ajout, modification et suppression des activités de l'agence dans trois catégories différentes : Activité culturelle, Activité de montagne et Activité de bien-être
- Commentaires pour chaque activité
- Serveur SMTP pour l'inscription de nouveaux adresses mail
- Gestion des utilisateurs

## Comment exécuter le projet 🐙

1. Clonez le projet depuis GitHub en exécutant la commande suivante dans votre terminal :

```
https://github.com/youneshimi/Atlas_Trekking.git
```

2. Installez les dépendances requises en exécutant la commande suivante :

```
composer install
```

3. Configurez la base de données et Smtp dans le fichier `.env` en modifiant la variable `DATABASE_URL` & `MAILER_DSN`.
4. Créez la base de données en exécutant la commande suivante :

```
php bin/console doctrine:database:create
```

5. Exécutez les migrations pour créer les tables de base de données en exécutant la commande suivante :

```
php bin/console doctrine:migrations:migrate
```

6. Démarrez le serveur en exécutant la commande suivante :

```
symfony server:start
```

7. Accédez à l'application en ouvrant votre navigateur et en visitant l'URL suivante :

```
http://localhost:8000
```

## Conclusion 📜 

Atlas Trekking est un projet Symfony 6 qui permet aux gestionnaires d'une agence de mettre à jour les activités de l'agence. Avec les fonctionnalités CRUD, les utilisateurs peuvent facilement ajouter, modifier et supprimer des activités. Le projet est également configuré avec un serveur SMTP 📤 pour l'inscription de nouveaux adresses mail.



[![LinkedIn](https://img.shields.io/badge/-LinkedIn-blue?style=flat-square&logo=linkedin&logoColor=white&link=https://https://www.linkedin.com/in/younes-shimi/)](https://www.linkedin.com/in/younes-shimi/)

[![Email](https://img.shields.io/badge/-Email-red?style=flat-square&logo=Mail.Ru&logoColor=white&link=mailto:ounesshimi@gmail.com)](mailto:ounesshimi@gmail.com)

