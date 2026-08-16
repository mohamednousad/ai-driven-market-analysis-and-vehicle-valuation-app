import csv
import json
import math
import os
import random

DATASET_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "car_price_dataset.csv")
MODEL_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "model.json")
FAIR_BAND_PCT = 0.18
MAX_DEPTH = 10
MIN_SAMPLES_LEAF = 15
MAX_THRESHOLDS = 32


def load_rows(csv_path=DATASET_PATH):
    rows = []
    with open(csv_path, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        for r in reader:
            try:
                brand = str(r["Brand"]).strip().upper()
                model = str(r["Model"]).strip().upper()
                year = int(float(r["YOM"]))
                engine_cc = float(r["Engine (cc)"])
                transmission = str(r["Gear"]).strip().lower()
                fuel = str(r["Fuel Type"]).strip().lower()
                mileage = float(r["Millage(KM)"])
                price_lakhs = float(r["Price"])
            except (KeyError, TypeError, ValueError):
                continue
            if not brand or not model:
                continue
            if price_lakhs <= 5 or price_lakhs >= 5000:
                continue
            rows.append({
                "brand": brand,
                "model": model,
                "year": year,
                "engine_cc": engine_cc,
                "transmission": transmission,
                "fuel": fuel,
                "mileage": mileage,
                "price_lakhs": price_lakhs,
            })
    return rows


def build_medians(rows):
    grouped = {}
    for r in rows:
        grouped.setdefault((r["brand"], r["model"], r["year"]), []).append(r["price_lakhs"])
    medians = {}
    for key, prices in grouped.items():
        prices.sort()
        n = len(prices)
        mid = n // 2
        medians[key] = prices[mid] if n % 2 else (prices[mid - 1] + prices[mid]) / 2.0
    all_prices = sorted(r["price_lakhs"] for r in rows)
    n = len(all_prices)
    mid = n // 2
    global_median = all_prices[mid] if n % 2 else (all_prices[mid - 1] + all_prices[mid]) / 2.0
    return medians, global_median


def lookup_median(medians, global_median, brand, model, year):
    key = (brand, model, year)
    if key in medians:
        return medians[key]
    model_matches = [v for (b, m, _y), v in medians.items() if b == brand and m == model]
    if model_matches:
        return sum(model_matches) / len(model_matches)
    brand_matches = [v for (b, _m, _y), v in medians.items() if b == brand]
    if brand_matches:
        return sum(brand_matches) / len(brand_matches)
    return global_median


def build_encoders(rows):
    def enc(values):
        return {v: i + 1 for i, v in enumerate(sorted(set(values)))}
    return {
        "brand": enc(r["brand"] for r in rows),
        "model": enc(r["model"] for r in rows),
        "transmission": enc(r["transmission"] for r in rows),
        "fuel": enc(r["fuel"] for r in rows),
    }


def encode_value(encoder, value):
    return encoder.get(value, 0)


def make_dataset(rows, encoders, medians, global_median):
    x, y = [], []
    for r in rows:
        med = lookup_median(medians, global_median, r["brand"], r["model"], r["year"])
        low, high = med * (1 - FAIR_BAND_PCT), med * (1 + FAIR_BAND_PCT)
        label = 1 if low <= r["price_lakhs"] <= high else 0
        ratio = r["price_lakhs"] / med if med > 0 else 0.0
        x.append([
            encode_value(encoders["brand"], r["brand"]),
            encode_value(encoders["model"], r["model"]),
            float(r["year"]),
            float(r["engine_cc"]),
            encode_value(encoders["transmission"], r["transmission"]),
            encode_value(encoders["fuel"], r["fuel"]),
            float(r["mileage"]),
            float(r["price_lakhs"]),
            ratio,
        ])
        y.append(label)
    return x, y


def gini(counts, total):
    if total == 0:
        return 0.0
    p1 = counts / total
    p0 = 1.0 - p1
    return 1.0 - p1 * p1 - p0 * p0


def candidate_thresholds(values):
    unique = sorted(set(values))
    if len(unique) <= 1:
        return []
    if len(unique) <= MAX_THRESHOLDS:
        return [(unique[i] + unique[i + 1]) / 2.0 for i in range(len(unique) - 1)]
    step = len(unique) / (MAX_THRESHOLDS + 1)
    picks = []
    for i in range(1, MAX_THRESHOLDS + 1):
        idx = int(i * step)
        if 0 < idx < len(unique):
            picks.append((unique[idx - 1] + unique[idx]) / 2.0)
    return sorted(set(picks))


def best_split(x, y, indices):
    n = len(indices)
    total_pos = sum(y[i] for i in indices)
    parent_gini = gini(total_pos, n)
    best = None
    n_features = len(x[0])
    for f in range(n_features):
        values = [x[i][f] for i in indices]
        for t in candidate_thresholds(values):
            left_n = left_pos = 0
            for i in indices:
                if x[i][f] <= t:
                    left_n += 1
                    left_pos += y[i]
            right_n = n - left_n
            if left_n < MIN_SAMPLES_LEAF or right_n < MIN_SAMPLES_LEAF:
                continue
            right_pos = total_pos - left_pos
            weighted = (left_n / n) * gini(left_pos, left_n) + (right_n / n) * gini(right_pos, right_n)
            gain = parent_gini - weighted
            if gain > 1e-7 and (best is None or gain > best[0]):
                best = (gain, f, t)
    return best


def build_tree(x, y, indices, depth=0):
    total_pos = sum(y[i] for i in indices)
    n = len(indices)
    prob = total_pos / n if n else 0.0
    if depth >= MAX_DEPTH or n < 2 * MIN_SAMPLES_LEAF or total_pos == 0 or total_pos == n:
        return {"leaf": True, "prob": prob, "n": n}
    split = best_split(x, y, indices)
    if split is None:
        return {"leaf": True, "prob": prob, "n": n}
    _gain, f, t = split
    left_idx = [i for i in indices if x[i][f] <= t]
    right_idx = [i for i in indices if x[i][f] > t]
    return {
        "leaf": False,
        "feature": f,
        "threshold": t,
        "left": build_tree(x, y, left_idx, depth + 1),
        "right": build_tree(x, y, right_idx, depth + 1),
    }


def predict_tree(node, features):
    while not node["leaf"]:
        node = node["left"] if features[node["feature"]] <= node["threshold"] else node["right"]
    return 1 if node["prob"] >= 0.5 else 0, node["prob"]


def train_model(rows):
    encoders = build_encoders(rows)
    medians, global_median = build_medians(rows)
    x, y = make_dataset(rows, encoders, medians, global_median)
    root = build_tree(x, y, list(range(len(x))))
    return {
        "tree": root,
        "encoders": encoders,
        "medians": medians,
        "global_median": global_median,
        "fair_band_pct": FAIR_BAND_PCT,
        "trained_rows": len(rows),
    }


def save_model(model, path=MODEL_PATH):
    serializable = dict(model)
    serializable["medians"] = {"|".join([b, m, str(yr)]): v for (b, m, yr), v in model["medians"].items()}
    with open(path, "w", encoding="utf-8") as f:
        json.dump(serializable, f)


def load_model(path=MODEL_PATH):
    with open(path, encoding="utf-8") as f:
        model = json.load(f)
    medians = {}
    for key, v in model["medians"].items():
        b, m, yr = key.rsplit("|", 2)
        medians[(b, m, int(yr))] = v
    model["medians"] = medians
    return model


def features_from_payload(model, brand, model_name, year, engine_cc, transmission, fuel, mileage, price_lakhs):
    med = lookup_median(model["medians"], model["global_median"], brand, model_name, year)
    ratio = price_lakhs / med if med > 0 else 0.0
    return [
        encode_value(model["encoders"]["brand"], brand),
        encode_value(model["encoders"]["model"], model_name),
        float(year),
        float(engine_cc),
        encode_value(model["encoders"]["transmission"], transmission),
        encode_value(model["encoders"]["fuel"], fuel),
        float(mileage),
        float(price_lakhs),
        ratio,
    ], med


def split_rows(rows, test_ratio=0.2, seed=42):
    shuffled = rows[:]
    random.Random(seed).shuffle(shuffled)
    cut = int(len(shuffled) * (1 - test_ratio))
    return shuffled[:cut], shuffled[cut:]
