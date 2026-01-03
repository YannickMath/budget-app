# 📅 Tâches planifiées automatiques

Ce document liste toutes les tâches planifiées (cron) qui s'exécutent automatiquement dans l'application.

---

## 🔧 Architecture

```
Container Scheduler (budget_app_scheduler)
└─ Surveille 2 schedules :
   ├─ clean_expired_tokens (quotidien)
   └─ clean_old_audits (mensuel)
       ↓
   Crée des messages dans Redis
       ↓
Container Worker (budget_app_worker)
└─ Consomme les messages et exécute les tâches
```

---

## 📋 Liste des tâches planifiées

### 1. Nettoyage des tokens expirés

| Propriété | Valeur |
|-----------|--------|
| **Nom** | `clean_expired_tokens` |
| **Fréquence** | Tous les jours à minuit (00:00 UTC) |
| **Message** | `CleanupExpiredTokensMessage` |
| **Handler** | `CleanupExpiredTokensHandler` |
| **Fichier** | `src/Scheduler/CleanTokenTaskProvider.php` |

**Ce qui est nettoyé** :
- ✅ Tokens de vérification d'email expirés (`email_verification_token`)
- ✅ Tokens de réinitialisation de mot de passe expirés (`password_reset_token`)
- ✅ Demandes de changement d'email expirées (`email_change_requests`)

**Tables concernées** :
- `users` (colonnes : `email_verification_token*`, `password_reset_token*`)
- `email_change_requests` (suppression des entrées expirées non confirmées)

---

### 2. Nettoyage des anciens audits

| Propriété | Valeur |
|-----------|--------|
| **Nom** | `clean_old_audits` |
| **Fréquence** | 1er du mois à minuit (00:00 UTC) |
| **Message** | `CleanupOldAuditsMessage` |
| **Handler** | `CleanupOldAuditsHandler` |
| **Fichier** | `src/Scheduler/CleanAuditTaskProvider.php` |
| **Rétention** | 180 jours (6 mois) |

**Ce qui est nettoyé** :
- ✅ Entrées d'audit de plus de 6 mois dans `users_audit`

**Table concernée** :
- `users_audit`

---

## 🔍 Vérifier les schedules

### Lister toutes les tâches planifiées
```bash
docker exec budget_app_api php bin/console debug:scheduler
```

**Résultat attendu** :
```
clean_old_audits
----------------
Trigger     Provider                              Next Run
0 0 1 * *   App\Message\CleanupOldAuditsMessage   Sun, 01 Feb 2026 00:00:00 +0000

clean_expired_tokens
--------------------
Trigger     Provider                                  Next Run
0 0 * * *   App\Message\CleanupExpiredTokensMessage   Sun, 04 Jan 2026 00:00:00 +0000
```

### Vérifier les logs du scheduler
```bash
docker logs budget_app_scheduler --tail 50 -f
```

### Vérifier les logs du worker
```bash
docker logs budget_app_worker --tail 50 -f
```

---

## 🧪 Tester manuellement les tâches

### Tester le nettoyage des tokens

```bash
# Compter les tokens expirés
docker exec budget_app_api php bin/console dbal:run-sql "
SELECT COUNT(*) FROM users
WHERE (password_reset_token_expires_at IS NOT NULL AND password_reset_token_expires_at < NOW())
   OR (email_verification_token_expires_at IS NOT NULL AND email_verification_token_expires_at < NOW());
"

# Déclencher manuellement (pour test)
docker exec budget_app_api php -r "
require 'vendor/autoload.php';
\$kernel = new \App\Kernel('dev', true);
\$kernel->boot();
\$bus = \$kernel->getContainer()->get('messenger.default_bus');
\$bus->dispatch(new \App\Message\CleanupExpiredTokensMessage());
echo 'Cleanup triggered!' . PHP_EOL;
"
```

### Tester le nettoyage des audits

```bash
# Compter les audits de plus de 6 mois
docker exec budget_app_api php bin/console dbal:run-sql "
SELECT COUNT(*) FROM users_audit
WHERE created_at < NOW() - INTERVAL '180 days';
"

# Déclencher manuellement (pour test)
docker exec budget_app_api php -r "
require 'vendor/autoload.php';
\$kernel = new \App\Kernel('dev', true);
\$kernel->boot();
\$bus = \$kernel->getContainer()->get('messenger.default_bus');
\$bus->dispatch(new \App\Message\CleanupOldAuditsMessage(180));
echo 'Audit cleanup triggered!' . PHP_EOL;
"
```

---

## ⚙️ Modifier la configuration

### Changer la fréquence de nettoyage

Éditer le fichier correspondant dans `src/Scheduler/` :

```php
// Exemple : Nettoyage des audits tous les 15 jours au lieu de tous les mois
->add(RecurringMessage::cron('0 0 */15 * *', new CleanupOldAuditsMessage(180)));
```

### Changer la rétention des audits

Modifier le nombre de jours dans `src/Scheduler/CleanAuditTaskProvider.php` :

```php
// Garder 1 an au lieu de 6 mois
->add(RecurringMessage::cron('0 0 1 * *', new CleanupOldAuditsMessage(365)));
```

Redémarrer le scheduler après modification :
```bash
docker-compose restart scheduler
```

---

## 📊 Format des cron expressions

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (Sunday=0)
│ │ │ │ │
* * * * *
```

**Exemples** :
- `0 0 * * *` : Tous les jours à minuit
- `0 0 1 * *` : 1er du mois à minuit
- `0 2 * * 0` : Tous les dimanches à 2h du matin
- `0 0 */15 * *` : Tous les 15 jours à minuit
- `@daily` : Tous les jours à minuit (alias)
- `@monthly` : 1er du mois à minuit (alias)

---

## 🚀 En production

Les tâches planifiées fonctionnent automatiquement car :
- ✅ Le scheduler démarre avec `docker-compose up -d`
- ✅ Le worker consomme automatiquement les messages
- ✅ Policy de restart : `unless-stopped` (redémarre automatiquement)

**Aucune configuration cron système nécessaire** ! Tout est géré par Symfony Scheduler + Messenger + Docker.

---

## 📝 Ajouter une nouvelle tâche planifiée

1. Créer le Message : `src/Message/MyNewMessage.php`
2. Créer le Handler : `src/MessageHandler/MyNewMessageHandler.php`
3. Créer le ScheduleProvider : `src/Scheduler/MyNewTaskProvider.php`
4. Ajouter le routing dans `config/packages/messenger.yaml`
5. Mettre à jour le scheduler dans `docker-compose.yaml` :
   ```yaml
   command: php bin/console messenger:consume scheduler_clean_expired_tokens scheduler_clean_old_audits scheduler_my_new_task -vv
   ```
6. Redémarrer : `docker-compose restart scheduler`

---

## ✅ Checklist de santé

- [ ] Le scheduler tourne : `docker-compose ps | grep scheduler`
- [ ] Le worker tourne : `docker-compose ps | grep worker`
- [ ] Les schedules sont visibles : `docker exec budget_app_api php bin/console debug:scheduler`
- [ ] Pas d'erreurs dans les logs : `docker logs budget_app_scheduler` et `docker logs budget_app_worker`
