# AutoValue — AI-Driven Vehicle Marketplace

Final year project build. PHP (OOP) + MySQL web app, Python Flask AI microservice running a Decision Tree Regressor written completely from scratch (no ML libraries), and an optional Gemini buyer assistant.

## What is inside

```
autovalue/
├── ai_service/
│   ├── decision_tree.py        Decision Tree Regressor built from scratch
│   ├── train.py                Trains the tree on car_price_dataset.csv
│   ├── app.py                  Flask API: /health and /predict
│   ├── model/model.json        Trained model (already trained, ready to serve)
│   └── car_price_dataset.csv   9,788 Sri Lankan vehicle listings
├── database/
│   ├── schema.sql              16 tables + 25 district seed rows
│   └── seed.php                Creates demo accounts (run once)
└── web/
    ├── config/                 config.php (DB creds, AI URL, Gemini key), database.php
    ├── includes/               bootstrap, helpers, header, footer
    ├── lib/                    13 OOP classes (repositories and services)
    └── public/                 Web root (point your server here)
```

## Model performance (already trained on the included dataset)

- Test R²: 0.845
- Test MAE: Rs 673,780
- 72.4% of test predictions within 15% of the actual price
- Tree depth 12, 421 leaves, trained on 8,256 rows / tested on 1,457

Retrain any time with `python train.py` inside `ai_service/`.

## Setup (XAMPP or any LAMP stack)

### 1. Database

```
mysql -u root -p
CREATE DATABASE autovalue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
mysql -u root -p autovalue < database/schema.sql
```

If your MySQL user or password differ from root with empty password, update `web/config/config.php`.

### 2. Demo accounts

```
php database/seed.php
```

Password for every demo account: `password123`

| Role | Email | Notes |
| --- | --- | --- |
| Admin | admin@autovalue.lk | Approves ads, manages users and reports |
| Seller | seller@autovalue.lk | MEMBER badge |
| Seller | agent@autovalue.lk | AUTHORIZED AGENT badge |
| Buyer | buyer@autovalue.lk | Chat, favourites, ratings, assistant |

### 3. AI service (start this before posting ads)

```
cd ai_service
pip install -r requirements.txt
python app.py
```

Runs at http://127.0.0.1:5000. Check http://127.0.0.1:5000/health in a browser.

### 4. Web app

```
cd web/public
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000

For XAMPP instead: place the project inside `htdocs` and point a virtual host document root at `autovalue/web/public`.

### 5. Optional: Gemini buyer assistant

Add your API key in `web/config/config.php`:

```
define('GEMINI_API_KEY', 'your-key-here');
```

Without a key the app still runs; the assistant page simply shows a friendly notice.

## How the AI price gate works

1. Seller fills the post-ad form and submits.
2. PHP calls the Flask `/predict` endpoint with brand, model, year, transmission, fuel, engine, mileage and feature count.
3. The Decision Tree returns a predicted price plus a fair range built from the leaf standard deviation (minimum band 8% of the prediction) and a confidence score.
4. If the asking price falls outside the range, the ad is blocked with a red verdict showing the predicted price and fair range. Nothing is saved.
5. If the price is fair, the ad is created as pending_review, the analysis is stored in the `analysis` table, and the admin approves it to go live.

Sellers can also press "Check fair price first" on the form for an instant AJAX check before submitting.

## Feature checklist

- Buyer, seller and admin roles with session auth, login attempt logging and 15 minute lockout after 5 failures
- Post ad with up to 6 photos, MIME validation, transactional insert across ads, vehicles, vehicle_specifications and vehicle_images
- AI fair-price gate with stored analysis rows and an "AI verified fair price" chip on live ads
- ikman-style listing page: district sidebar, search, poster type filter, promoted filter, price range, sorting, pagination, FEATURED cream cards
- Ad detail: gallery, phone reveal, MEMBER and AUTHORIZED AGENT badges, safety tips box, report modal, seller ratings
- Buyer chat with sellers, favourites, seller ratings with upsert
- Ad promotions (Top Ad, Featured, Urgent) with simulated payment and automatic expiry by date range
- Admin dashboard, ad review queue with AI analysis shown, user management (status and poster type), report handling
- Notifications with unread badge in the top bar
