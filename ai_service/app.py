import json
import math
import os

from flask import Flask, jsonify, request

from decision_tree import DecisionTreeRegressor

MODEL_PATH = os.path.join(os.path.dirname(__file__), "model", "model.json")


class PriceModelService:
    def __init__(self, model_path):
        self.model_path = model_path
        self.tree = None
        self.encoder = None
        self.meta = {}
        self.load()

    def load(self):
        with open(self.model_path) as fh:
            bundle = json.load(fh)
        self.tree = DecisionTreeRegressor.from_dict(bundle["tree"])
        self.encoder = bundle["encoder"]
        self.meta = {
            "model_type": bundle["model_type"],
            "trained_rows": bundle["trained_rows"],
            "test_r2": bundle["test_r2"],
            "test_mae": bundle["test_mae"],
            "feature_order": bundle["feature_order"],
        }

    def is_ready(self):
        return self.tree is not None

    def _encode(self, data):
        enc = self.encoder
        brand = str(data.get("brand", "")).strip().upper()
        model = str(data.get("model", "")).strip().upper()
        gear = str(data.get("transmission", "")).strip().title()
        fuel = str(data.get("fuel_type", "")).strip().title()
        yom = int(data.get("manufacture_year"))
        engine = float(data.get("engine_cc", 0) or 0)
        mileage = float(data.get("mileage_km", 0) or 0)
        feature_count = int(data.get("feature_count", 0) or 0)

        brand_val = enc["brand_means"].get(brand, enc["global_mean"])
        model_val = enc["model_means"].get(model, brand_val)
        gear_val = enc["gear_map"].get(gear, 0.5)
        fuel_val = enc["fuel_map"].get(fuel, enc["global_mean"])
        age = float(enc["current_year"] - yom)

        return [
            brand_val,
            model_val,
            age,
            engine,
            gear_val,
            fuel_val,
            math.log(mileage + 1.0),
            float(feature_count),
        ]

    def predict(self, data):
        x = self._encode(data)
        predicted, leaf_std, leaf_n = self.tree.predict_one(x)

        band = max(leaf_std, 0.08 * predicted)
        lower = max(predicted - band, 0.0)
        upper = predicted + band

        spread_ratio = leaf_std / predicted if predicted > 0 else 1.0
        confidence = max(0.30, min(0.99, 1.0 - spread_ratio))

        return {
            "predicted_price": round(predicted, 2),
            "lower_bound": round(lower, 2),
            "upper_bound": round(upper, 2),
            "confidence_score": round(confidence, 4),
            "leaf_samples": leaf_n,
        }


app = Flask(__name__)
service = PriceModelService(MODEL_PATH)


@app.after_request
def allow_cors(response):
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Access-Control-Allow-Headers"] = "Content-Type"
    response.headers["Access-Control-Allow-Methods"] = "GET, POST, OPTIONS"
    return response


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "running", "model_ready": service.is_ready(), "meta": service.meta})


@app.route("/predict", methods=["POST", "OPTIONS"])
def predict():
    if request.method == "OPTIONS":
        return ("", 204)
    try:
        payload = request.get_json(force=True)
        inputs = payload.get("inputs", payload)
        required = ("brand", "model", "manufacture_year", "transmission", "fuel_type")
        missing = [k for k in required if not str(inputs.get(k, "")).strip()]
        if missing:
            return jsonify({"success": False, "message": "Missing fields: " + ", ".join(missing)}), 400
        result = service.predict(inputs)
        return jsonify({"success": True, "prediction": result})
    except Exception as error:
        return jsonify({"success": False, "message": str(error)}), 400


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)
