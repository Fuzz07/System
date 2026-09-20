#Pasikat ka boi 
#Capstone-Project namon

## PayMongo enrollment payments

Configure the following environment variables before enabling online checkout:

```env
PAYMONGO_ENABLED=true
PAYMONGO_SECRET_KEY=sk_test_your_key
PAYMONGO_WEBHOOK_SECRET=whsk_your_webhook_secret
PAYMONGO_PAYMENT_METHODS=gcash,qrph,card
```

Then run the database migration:

```bash
php artisan migrate
```

In the PayMongo Dashboard, register this public webhook endpoint:

```text
https://your-domain.example/api/paymongo/webhook
```

Subscribe it to `checkout_session.payment.paid`, then copy that endpoint's signing secret into `PAYMONGO_WEBHOOK_SECRET`. Use test keys first and replace them with live keys only when the account and payment methods are ready for production.
