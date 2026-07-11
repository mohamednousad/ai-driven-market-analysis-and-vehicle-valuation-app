import io
import json
import os
import pickle

import numpy as np


class SafeUnpickler(pickle.Unpickler):
    def find_class(self, module, name):
        try:
            return super().find_class(module, name)
        except Exception:
            try:
                import custom_regressor
                if hasattr(custom_regressor, name):
                    return getattr(custom_regressor, name)
            except Exception:
                pass
            raise


def load_pickle(path):
    with open(path, "rb") as file:
        data = file.read()
    return SafeUnpickler(io.BytesIO(data)).load()


class ModelService:
    def __init__(self, model_path, schema_path):
        self.model_path = model_path
        self.schema_path = schema_path
        self.bundle = None
        self.model = None
        self.encoders = None
        self.means = None
        self.stds = None
        self.feature_order = None
        self.schema = self.load_schema_file()
        self.load_model()

    def load_schema_file(self):
        if os.path.exists(self.schema_path):
            with open(self.schema_path, "r", encoding="utf-8") as file:
                return json.load(file)
        return {"project_name": "Vehicle Price Valuation", "target": "price", "features": []}

    def load_model(self):
        if not os.path.exists(self.model_path):
            return
        loaded = load_pickle(self.model_path)
        self.bundle = loaded if isinstance(loaded, dict) else {"model": loaded}
        self.model = self.bundle.get("model", loaded)
        self.encoders = self.bundle.get("encoders") or self.bundle.get("label_encoders")
        self.means = self.bundle.get("means") or self.bundle.get("mean")
        self.stds = self.bundle.get("stds") or self.bundle.get("std")
        self.feature_order = (
            self.bundle.get("feature_order")
            or self.bundle.get("features")
            or self.bundle.get("columns")
        )
        if isinstance(self.bundle, dict) and self.bundle.get("feature_schema"):
            self.schema = self.bundle.get("feature_schema")

    def reload(self):
        self.bundle = None
        self.model = None
        self.encoders = None
        self.means = None
        self.stds = None
        self.feature_order = None
        self.schema = self.load_schema_file()
        self.load_model()

    def is_ready(self):
        return self.model is not None

    def get_schema(self):
        return self.schema

    def schema_feature_names(self):
        return [field.get("name") for field in self.schema.get("features", [])]

    def ordered_feature_names(self):
        if self.feature_order:
            return list(self.feature_order)
        return self.schema_feature_names()

    def encode_value(self, field, value):
        name = field.get("name")
        if self.encoders and name in self.encoders:
            mapping = self.encoders[name]
            if isinstance(mapping, dict) and value in mapping:
                return float(mapping[value])
        if field.get("type") == "select":
            encoding = field.get("encoding", {})
            if value in encoding:
                return float(encoding[value])
        try:
            return float(value)
        except Exception:
            return 0.0

    def field_by_name(self, name):
        for field in self.schema.get("features", []):
            if field.get("name") == name:
                return field
        return {"name": name, "type": "number"}

    def vector_from_inputs(self, inputs):
        names = self.ordered_feature_names()
        values = []
        for name in names:
            field = self.field_by_name(name)
            values.append(self.encode_value(field, inputs.get(name)))
        x = np.array(values, dtype=float)
        if self.means is not None and self.stds is not None:
            means = np.array(self.means, dtype=float)
            stds = np.array(self.stds, dtype=float)
            if means.shape == x.shape and stds.shape == x.shape:
                stds = np.where(stds == 0, 1, stds)
                x = (x - means) / stds
        return x

    def call_model(self, x):
        model = self.model
        matrix = np.array([x], dtype=float)
        if hasattr(model, "predict"):
            output = model.predict(matrix)
            return np.array(output, dtype=float).reshape(-1)
        if hasattr(model, "forward"):
            output = model.forward(matrix)
            return np.array(output, dtype=float).reshape(-1)
        raise ValueError("The loaded model does not expose a predict or forward method.")

    def predict(self, inputs):
        if not self.is_ready():
            raise FileNotFoundError(
                "Model file not found. Place vehicle_price_model.pkl inside ai_service/model."
            )
        x = self.vector_from_inputs(inputs)
        result = self.call_model(x)
        predicted = float(result[0])
        transform = self.bundle.get("target_transform") if isinstance(self.bundle, dict) else None
        if transform in ("log", "log1p"):
            predicted = float(np.expm1(predicted))
        elif transform == "log10":
            predicted = float(10 ** predicted)
        predicted = max(predicted, 0.0)
        return {"predicted_price": round(predicted, 2)}
