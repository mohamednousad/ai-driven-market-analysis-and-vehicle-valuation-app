import os

from flask import Flask, jsonify, request

import tree


def get_model():
    if os.path.exists(tree.MODEL_PATH):
        return tree.load_model()
    rows = tree.load_rows()
    model = tree.train_model(rows)
    tree.save_model(model)
    saved = tree.load_model()
    return saved


app = Flask(__name__)
service = get_model()
print(f"[AutoValue AI] Decision tree ready ({service['trained_rows']} training rows).")


def fmt(value_lkr):
    return f"LKR {value_lkr / 1_000_000.0:.1f}M"


@app.route("/health")
def health():
    return jsonify({"ok": True, "model": "DecisionTree (built from scratch)", "trained_rows": service["trained_rows"]})


@app.route("/predict", methods=["POST"])
def predict():
    payload = request.get_json(silent=True) or {}
    try:
        brand = str(payload.get("make", "")).strip().upper()
        model_name = str(payload.get("model", "")).strip().upper()
        year = int(payload.get("manufacture_year", 2015) or 2015)
        engine_cc = float(payload.get("engine_cc", 1500) or 1500)
        transmission = str(payload.get("transmission", "automatic")).strip().lower()
        fuel = str(payload.get("fuel_type", "petrol")).strip().lower()
        mileage = float(payload.get("mileage", 80000) or 80000)
        price_lkr = float(payload.get("price", 0) or 0)
        price_lakhs = price_lkr / 100000.0

        features, median_lakhs = tree.features_from_payload(
            service, brand, model_name, year, engine_cc, transmission, fuel, mileage, price_lakhs
        )
        prediction, confidence = tree.predict_tree(service["tree"], features)

        band = service["fair_band_pct"]
        low_lkr = median_lakhs * (1 - band) * 100000.0
        high_lkr = median_lakhs * (1 + band) * 100000.0
        in_band = low_lkr <= price_lkr <= high_lkr
        is_fair = prediction == 1 and in_band
        status = "fair" if is_fair else "not_fair"

        if is_fair:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is within the market fair range."
        elif price_lkr > high_lkr:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is above the fair market range for this vehicle."
        else:
            result = f"Predicted fair range: {fmt(low_lkr)} - {fmt(high_lkr)}. Submitted price {fmt(price_lkr)} is below the fair market range for this vehicle."

        return jsonify({
            "fair_price_status": status,
            "result": result,
            "predicted_low": round(low_lkr, 2),
            "predicted_high": round(high_lkr, 2),
            "predicted_median": round(median_lakhs * 100000.0, 2),
            "submitted_price": price_lkr,
            "confidence": round(confidence, 4),
            "model": "DecisionTree (built from scratch)",
        })
    except Exception as exc:
        return jsonify({"error": str(exc)}), 500


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
