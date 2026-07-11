# AutoValue AI — Setup Guide

AI-Driven Vehicle Marketplace for the CSE6035 Development Project. Buyers, sellers and an admin
console, with an AI fair-price gate on every listing and a Gemini-powered buyer assistant.

## Stack

- Frontend: HTML, CSS, JavaScript, jQuery (CDN), FontAwesome (CDN)
- Backend: PHP (PDO) + MySQL
- AI service: Python Flask serving a pickle model (model-agnostic loader)

## Folder structure

```
vehicle-app/
├── ai_service/                 Flask AI microservice
│   ├── app.py                  Endpoints: /health /schema /reload /predict
│   ├── model_loader.py         Model-agnostic pickle loader
│   ├── custom_regressor.py     Fallback classes for custom pickles
│   ├── model/
│   │   ├── feature_schema.json Vehicle feature schema
│   │   └── vehicle_price_model.pkl  Drop your Kaggle model here
│   └── export/                 Example of the expected bundle format
├── database/
│   └── schema.sql              Tables + seed data
└── web/
    ├── config/                 config.php, database.php
    ├── includes/               functions.php, auth.php, header.php, footer.php
    ├── lib/                    Services and repositories (OOP)
    ├── public/                 App entry (point your web root here)
    │   ├── assets/css/js       Global reusable CSS + jQuery
    │   ├── api/                JSON endpoints (valuate, chat)
    │   ├── buyer/ seller/ admin/
    │   ├── login.php register.php logout.php index.php
    └── storage/uploads/        Uploaded vehicle images
```

## 1. Database

Import the schema (creates the `autovalue` database, tables and seed data):

```
mysql -u root -p < database/schema.sql
```

Update credentials in `web/config/config.php` if needed (`DB_USER`, `DB_PASS`).

## 2. AI service (Flask)

```
cd ai_service
python -m venv .venv && source .venv/bin/activate   # optional
pip install -r requirements.txt
python app.py
```

The service runs on `http://127.0.0.1:5000`.

### Using your own Kaggle model

Export your trained model from your Kaggle notebook as a pickle bundle and save it as
`ai_service/model/vehicle_price_model.pkl`. See `ai_service/export/kaggle_model_bundle_example.py`
for the exact format. The loader accepts any bundle exposing a `model` with a `predict` (or
`forward`) method, plus optional `feature_order`, `encoders`, `means`/`stds`, and `target_transform`.
No retraining happens in the app. After replacing the file, either restart Flask or POST to `/reload`.

The project ships with a working fallback model so the app runs before your model is added.

## 3. Web app (PHP)

Point your PHP server's web root at `web/public`:

```
cd web/public
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000`.

## Demo accounts

Password for all seeded accounts: `password123`

- Admin:  admin@autovalue.lk
- Seller: seller@autovalue.lk
- Buyer:  buyer@autovalue.lk

## Settings module

Log in as admin and open **Settings** to configure the fair-price band percent, the Gemini API key
and model (enables the buyer AI assistant), currency, listings per page, and contact details.
