import math
import os

import pandas as pd
from flask import Flask, jsonify, request
from sklearn.tree import DecisionTreeClassifier
from sklearn.preprocessing import LabelEncoder

DATASET_PATH = os.path.join(os.path.dirname(__file__), "car_price_dataset.csv")
FAIR_BAND_PCT = 0.18


class FairPriceModel:
    def __init__(self, csv_path):
        self.csv_path = csv_path
        self.classifier = None
        self.brand_encoder = LabelEncoder()
        self.model_encoder = LabelEncoder()
        self.trans_encoder = LabelEncoder()
        self.fuel_encoder = LabelEncoder()
        self.feature_cols = ["brand", "model", "year", "engine_cc", "transmission", "fuel", "mileage", "price_lakhs"]
        self.median_lookup = {}
        self.global_median_lakhs = 0.0
        self.train()

    def train(self):
        df = pd.read_csv(self.csv_path)
        df = df.rename(columns={
            "Brand": "brand", "Model": "model", "YOM": "year",
            "Engine (cc)": "engine_cc", "Gear": "transmission",
            "Fuel Type": "fuel", "Millage(KM)": "mileage", "Price": "price_lakhs",
        })
        df = df[["brand", "model", "year", "engine_cc", "transmission", "fuel", "mileage", "price_lakhs"]].dropna()
        df = df[(df["price_lakhs"] > 5) & (df["price_lakhs"] < 5000)]
        df["brand"] = df["brand"].astype(str).str.strip().str.upper()
        df["model"] = df["model"].astype(str).str.strip().str.upper()
        df["transmission"] = df["transmission"].astype(str).str.strip().str.lower()
        df["fuel"] = df["fuel"].astype(str).str.strip().str.lower()

        grouped_median = df.groupby(["brand", "model", "year"])["price_lakhs"].median().reset_index()
        grouped_median.columns = ["brand", "model", "year", "median_price"]
        self.median_lookup = {(r["brand"], r["model"], int(r["year"])): float(r["median_price"]) for _, r in grouped_median.iterrows()}
        self.global_median_lakhs = float(df["price_lakhs"].median())

        df = df.merge(grouped_median, on=["brand", "model", "year"], how="left")
        low = df["median_price"] * (1 - FAIR_BAND_PCT)
        high = df["median_price"] * (1 + FAIR_BAND_PCT)
        df["label"] = ((df["price_lakhs"] >= low) & (df["price_lakhs"] <= high)).astype(int)

        self.brand_encoder.fit(df["brand"].unique())
        self.model_encoder.fit(df["model"].unique())
        self.trans_encoder.fit(df["transmission"].unique())
        self.fuel_encoder.fit(df["fuel"].unique())

        df["brand_e"] = self.brand_encoder.transform(df["brand"])
        df["model_e"] = self.model_encoder.transform(df["model"])
        df["trans_e"] = self.trans_encoder.transform(df["transmission"])
        df["fuel_e"] = self.fuel_encoder.transform(df["fuel"])

        x = df[["brand_e", "model_e", "year", "engine_cc", "trans_e", "fuel_e", "mileage", "price_lakhs"]]
        y = df["label"]

        self.classifier = DecisionTreeClassifier(max_depth=10, min_samples_leaf=15, random_state=42)
        self.classifier.fit(x, y)
        print(f"[AutoValue AI] Model trained on {len(df)} rows.")

    def encode(self, encoder, value, default_value):
        v = str(value).strip()
        try:
            return int(encoder.transform([v.upper() if encoder is self.brand_encoder or encoder is self.model_encoder else v.lower()])[0])
        except Exception:
            return default_value

    def lookup_median(self, brand, model_name, year):
        key = (brand.upper(), model_name.upper(), int(year))
        if key in self.median_lookup:
            return self.median_lookup[key]
        matches = [v for (b, m, _y), v in self.median_lookup.items() if b == brand.upper() and m == model_name.upper()]
        if matches:
            return sum(matches) / len(matches)
        brand_matches = [v for (b, _m, _y), v in self.median_lookup.items() if b == brand.upper()]
        if brand_matches:
            return sum(brand_matches) / len(brand_matches)
        return self.global_median_lakhs

    def predict(self, payload):
        brand = str(payload.get("make", "")).strip()
        model_name = str(payload.get("model", "")).strip()
        year = int(payload.get("manufacture_year", 2015) or 2015)
        engine_cc = float(payload.get("engine_cc", 1500) or 1500)
        transmission = str(payload.get("transmission", "automatic")).strip().lower()
        fuel = str(payload.get("fuel_type", "petrol")).strip().lower()
        mileage = float(payload.get("mileage", 80000) or 80000)
        price_lkr = float(payload.get("price", 0) or 0)
        price_lakhs = price_lkr / 100000.0

        brand_e = self.encode(self.brand_encoder, brand, 0)
        model_e = self.encode(self.model_encoder, model_name, 0)
        trans_e = self.encode(self.trans_encoder, transmission, 0)
        fuel_e = self.encode(self.fuel_encoder, fuel, 0)

        prediction = int(self.classifier.predict([[brand_e, model_e, year, engine_cc, trans_e, fuel_e, mileage, price_lakhs]])[0])
        median_lakhs = self.lookup_median(brand, model_name, year)
        low_lakhs = median_lakhs * (1 - FAIR_BAND_PCT)
        high_lakhs = median_lakhs * (1 + FAIR_BAND_PCT)
        low_lkr = low_lakhs * 100000.0
        high_lkr = high_lakhs * 100000.0

        is_fair = prediction == 1 and low_lkr <= price_lkr <= high_lkr
        status = "fair" if is_fair else "not_fair"

        def fmt(v):
            m = v / 1_000_000.0
            return f"LKR {m:.1f}M"

        if is_fair:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is within the market fair range."
        elif price_lkr > high_lkr:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is above the fair market range for this vehicle."
        else:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is below the fair market range for this vehicle."

        return {
            "fair_price_status": status,
            "result": result,
            "predicted_low": round(low_lkr, 2),
            "predicted_high": round(high_lkr, 2),
            "predicted_median": round(median_lakhs * 100000.0, 2),
            "submitted_price": price_lkr,
            "model": "DecisionTreeClassifier (temporary)",
        }


app = Flask(__name__)
service = FairPriceModel(DATASET_PATH)


@app.route("/health")
def health():
    return jsonify({"ok": True, "model": "DecisionTreeClassifier"})


@app.route("/predict", methods=["POST"])
def predict():
    payload = request.get_json(silent=True) or {}
    try:
        return jsonify(service.predict(payload))
    except Exception as exc:
        return jsonify({"error": str(exc)}), 500


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
