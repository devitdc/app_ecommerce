# Le Dressing Français

Application e-commerce avec Symfony 5.4 permettant la vente d'articles en ligne avec :
* une gestion des articles (*ajout, modification, suppression*),
* gestion de stock
* gestion des catégories (*une par article*)

Paiement avec l'API Stripe et envoi de notification mail avec l'API MailJet (activation du compte, confirmation de commande, etc.).

## Environnement de développement

### Pré-requis

* Symfony 5.4
* EasyAdmin 3
* PHP 8.0
* Symfony CLI 5.4.19
* Composer 2.4.4
* MariaDB 10.10.2
* BootStrap 5.2
* FontAwesome
* npm 9.5.0

Pour vérifier que les pré-requis sont respectés :
```bash
symfony check:requirements
```

Pour vérifier qu'aucun packages ne présentent des vulnérabilités :
```bash
symfony security:check
```

### Pour démarrer l'environnement de développement

```bash
composer install
npm force install
npm run build
symfony server:start
```

### Pour démarrer l'environnement de production

```bash
composer install --no-dev --optimize-autloader
npm force install
npm run build
```

## Lancer les tests

```bash
php bun/phpunit --testdox
```