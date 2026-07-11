from flask import Flask, request, jsonify
from flask_cors import CORS

from model_loader import ModelService

app = Flask(__name__)
CORS(app)
service = ModelService("model/vehicle_price_model.pkl", "model/feature_schema.json")


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "running", "model_ready": service.is_ready()})


@app.route("/schema", methods=["GET"])
def schema():
    return jsonify(service.get_schema())


@app.route("/reload", methods=["POST"])
def reload_model():
    service.reload()
    return jsonify({"status": "reloaded", "model_ready": service.is_ready()})


@app.route("/predict", methods=["POST"])
def predict():
    try:
        payload = request.get_json(force=True)
        inputs = payload.get("inputs", payload)
        prediction = service.predict(inputs)
        return jsonify({"success": True, "prediction": prediction})
    except Exception as error:
        return jsonify({"success": False, "message": str(error)}), 400


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)
