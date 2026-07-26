import csv
import json
import math
import random

from decision_tree import DecisionTreeRegressor

CSV_PATH = "car_price_dataset.csv"
MODEL_PATH = "model/model.json"
LAKH = 100000.0
CURRENT_YEAR = 2026


class FeatureEncoder:
    def __init__(self):
        self.brand_means = {}
        self.model_means = {}
        self.fuel_map = {}
        self.gear_map = {"Manual": 0.0, "Automatic": 1.0}
        self.global_mean = 0.0

    def fit(self, rows):
        prices = [r["price"] for r in rows]
        self.global_mean = sum(prices) / len(prices)

        def group_mean(key):
            sums, counts = {}, {}
            for r in rows:
                k = r[key]
                sums[k] = sums.get(k, 0.0) + r["price"]
                counts[k] = counts.get(k, 0) + 1
            smoothing = 10.0
            return {
                k: (sums[k] + smoothing * self.global_mean) / (counts[k] + smoothing)
                for k in sums
            }

        self.brand_means = group_mean("brand")
        self.model_means = group_mean("model")

        fuel_sums, fuel_counts = {}, {}
        for r in rows:
            f = r["fuel"]
            fuel_sums[f] = fuel_sums.get(f, 0.0) + r["price"]
            fuel_counts[f] = fuel_counts.get(f, 0) + 1
        self.fuel_map = {f: fuel_sums[f] / fuel_counts[f] for f in fuel_sums}

    def transform(self, row):
        return [
            self.brand_means.get(row["brand"], self.global_mean),
            self.model_means.get(row["model"], self.brand_means.get(row["brand"], self.global_mean)),
            float(CURRENT_YEAR - row["yom"]),
            float(row["engine_cc"]),
            self.gear_map.get(row["gear"], 0.5),
            self.fuel_map.get(row["fuel"], self.global_mean),
            math.log(row["mileage"] + 1.0),
            float(row["feature_count"]),
        ]

    def to_dict(self):
        return {
            "brand_means": self.brand_means,
            "model_means": self.model_means,
            "fuel_map": self.fuel_map,
            "gear_map": self.gear_map,
            "global_mean": self.global_mean,
            "current_year": CURRENT_YEAR,
        }


def load_rows(path):
    rows = []
    with open(path, newline="", encoding="utf-8") as fh:
        for rec in csv.DictReader(fh):
            try:
                yom = int(rec["YOM"])
                engine = float(rec["Engine (cc)"])
                mileage = float(rec["Millage(KM)"])
                price = float(rec["Price"]) * LAKH
            except (ValueError, KeyError):
                continue
            if not (1980 <= yom <= CURRENT_YEAR):
                continue
            if not (0 < price < 200000000):
                continue
            if engine < 0 or mileage < 0:
                continue
            feature_count = sum(
                1
                for col in ("AIR CONDITION", "POWER STEERING", "POWER MIRROR", "POWER WINDOW")
                if str(rec.get(col, "")).strip().lower() == "available"
            )
            rows.append(
                {
                    "brand": rec["Brand"].strip().upper(),
                    "model": rec["Model"].strip().upper(),
                    "yom": yom,
                    "engine_cc": engine,
                    "gear": rec["Gear"].strip().title(),
                    "fuel": rec["Fuel Type"].strip().title(),
                    "mileage": mileage,
                    "feature_count": feature_count,
                    "price": price,
                }
            )
    return rows


def evaluate(tree, X, y):
    preds = tree.predict(X)
    n = len(y)
    mae = sum(abs(p - t) for p, t in zip(preds, y)) / n
    mean_y = sum(y) / n
    ss_res = sum((t - p) ** 2 for p, t in zip(preds, y))
    ss_tot = sum((t - mean_y) ** 2 for t in y)
    r2 = 1.0 - ss_res / ss_tot if ss_tot else 0.0
    within = sum(1 for p, t in zip(preds, y) if abs(p - t) <= 0.15 * t) / n
    return mae, r2, within


def main():
    random.seed(42)
    rows = load_rows(CSV_PATH)
    print(f"Loaded {len(rows)} clean rows")

    random.shuffle(rows)
    split = int(len(rows) * 0.85)
    train_rows, test_rows = rows[:split], rows[split:]

    encoder = FeatureEncoder()
    encoder.fit(train_rows)

    X_train = [encoder.transform(r) for r in train_rows]
    y_train = [r["price"] for r in train_rows]
    X_test = [encoder.transform(r) for r in test_rows]
    y_test = [r["price"] for r in test_rows]

    tree = DecisionTreeRegressor(max_depth=12, min_samples_split=20, min_samples_leaf=8)
    tree.fit(X_train, y_train)

    mae_tr, r2_tr, w_tr = evaluate(tree, X_train, y_train)
    mae_te, r2_te, w_te = evaluate(tree, X_test, y_test)
    print(f"Depth {tree.depth()}  Leaves {tree.leaf_count()}")
    print(f"Train  MAE Rs {mae_tr:,.0f}  R2 {r2_tr:.3f}  within15% {w_tr:.1%}")
    print(f"Test   MAE Rs {mae_te:,.0f}  R2 {r2_te:.3f}  within15% {w_te:.1%}")

    bundle = {
        "model_type": "decision_tree_regressor_scratch",
        "trained_rows": len(train_rows),
        "test_r2": round(r2_te, 4),
        "test_mae": round(mae_te, 2),
        "feature_order": [
            "brand_encoded",
            "model_encoded",
            "vehicle_age",
            "engine_cc",
            "gear_encoded",
            "fuel_encoded",
            "log_mileage",
            "feature_count",
        ],
        "encoder": encoder.to_dict(),
        "tree": tree.to_dict(),
    }
    with open(MODEL_PATH, "w") as fh:
        json.dump(bundle, fh)
    print(f"Saved {MODEL_PATH}")


if __name__ == "__main__":
    main()
