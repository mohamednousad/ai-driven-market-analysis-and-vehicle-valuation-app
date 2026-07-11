import pickle

feature_schema = {
    "project_name": "AI-Driven Vehicle Price Valuation",
    "target": "price",
    "currency": "LKR",
    "features": [
        {"name": "brand", "label": "Brand", "type": "select", "options": ["Toyota", "Honda", "Nissan", "Suzuki", "Mitsubishi", "Mazda", "BMW", "Mercedes-Benz", "Audi", "Other"], "encoding": {"Toyota": 0, "Honda": 1, "Nissan": 2, "Suzuki": 3, "Mitsubishi": 4, "Mazda": 5, "BMW": 6, "Mercedes-Benz": 7, "Audi": 8, "Other": 9}, "required": True},
        {"name": "model_year", "label": "Model Year", "type": "number", "min": 1990, "max": 2026, "step": 1, "required": True},
        {"name": "mileage", "label": "Mileage (km)", "type": "number", "min": 0, "max": 500000, "step": 1000, "required": True},
        {"name": "engine_capacity", "label": "Engine Capacity (cc)", "type": "number", "min": 600, "max": 6000, "step": 50, "required": True},
        {"name": "fuel_type", "label": "Fuel Type", "type": "select", "options": ["Petrol", "Diesel", "Hybrid", "Electric"], "encoding": {"Petrol": 0, "Diesel": 1, "Hybrid": 2, "Electric": 3}, "required": True},
        {"name": "transmission", "label": "Transmission", "type": "select", "options": ["Automatic", "Manual"], "encoding": {"Automatic": 0, "Manual": 1}, "required": True},
        {"name": "condition", "label": "Condition", "type": "select", "options": ["Excellent", "Good", "Fair"], "encoding": {"Excellent": 2, "Good": 1, "Fair": 0}, "required": True},
    ],
}

model_bundle = {
    "model": final_model,
    "feature_schema": feature_schema,
    "feature_order": ["brand", "model_year", "mileage", "engine_capacity", "fuel_type", "transmission", "condition"],
    "encoders": {
        "brand": {"Toyota": 0, "Honda": 1, "Nissan": 2, "Suzuki": 3, "Mitsubishi": 4, "Mazda": 5, "BMW": 6, "Mercedes-Benz": 7, "Audi": 8, "Other": 9},
        "fuel_type": {"Petrol": 0, "Diesel": 1, "Hybrid": 2, "Electric": 3},
        "transmission": {"Automatic": 0, "Manual": 1},
        "condition": {"Excellent": 2, "Good": 1, "Fair": 0},
    },
    "means": mean.tolist(),
    "stds": std.tolist(),
    "target_transform": "log1p",
}

with open("vehicle_price_model.pkl", "wb") as file:
    pickle.dump(model_bundle, file)
