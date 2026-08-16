import tree


def evaluate(model, rows):
    x, y = tree.make_dataset(rows, model["encoders"], model["medians"], model["global_median"])
    tp = tn = fp = fn = 0
    for features, label in zip(x, y):
        pred, _prob = tree.predict_tree(model["tree"], features)
        if pred == 1 and label == 1:
            tp += 1
        elif pred == 0 and label == 0:
            tn += 1
        elif pred == 1 and label == 0:
            fp += 1
        else:
            fn += 1
    total = tp + tn + fp + fn
    accuracy = (tp + tn) / total if total else 0.0
    precision = tp / (tp + fp) if (tp + fp) else 0.0
    recall = tp / (tp + fn) if (tp + fn) else 0.0
    f1 = 2 * precision * recall / (precision + recall) if (precision + recall) else 0.0
    return accuracy, precision, recall, f1, (tp, tn, fp, fn)


def main():
    rows = tree.load_rows()
    train_rows, test_rows = tree.split_rows(rows, test_ratio=0.2, seed=42)
    print(f"[AutoValue AI] Train split: {len(train_rows)} rows | Test split: {len(test_rows)} rows")
    model = tree.train_model(train_rows)
    train_acc, _p, _r, _f, _c = evaluate(model, train_rows)
    accuracy, precision, recall, f1, (tp, tn, fp, fn) = evaluate(model, test_rows)
    print(f"[AutoValue AI] Train accuracy: {train_acc * 100:.2f}%")
    print(f"[AutoValue AI] Test accuracy:  {accuracy * 100:.2f}%")
    print(f"[AutoValue AI] Precision:      {precision * 100:.2f}%")
    print(f"[AutoValue AI] Recall:         {recall * 100:.2f}%")
    print(f"[AutoValue AI] F1 score:       {f1 * 100:.2f}%")
    print(f"[AutoValue AI] Confusion matrix -> TP: {tp}  TN: {tn}  FP: {fp}  FN: {fn}")

    samples = [
        {"make": "TOYOTA", "model": "AQUA", "manufacture_year": 2018, "engine_cc": 1500, "transmission": "Automatic", "fuel_type": "Hybrid", "mileage": 65000, "price": 5850000},
        {"make": "SUZUKI", "model": "WAGON R", "manufacture_year": 2019, "engine_cc": 660, "transmission": "Automatic", "fuel_type": "Petrol", "mileage": 42000, "price": 12500000},
        {"make": "HONDA", "model": "VEZEL", "manufacture_year": 2017, "engine_cc": 1500, "transmission": "Automatic", "fuel_type": "Hybrid", "mileage": 78000, "price": 2000000},
    ]
    print("[AutoValue AI] Sample predictions:")
    for s in samples:
        features, med = tree.features_from_payload(
            model,
            s["make"].upper(), s["model"].upper(), int(s["manufacture_year"]),
            float(s["engine_cc"]), s["transmission"].lower(), s["fuel_type"].lower(),
            float(s["mileage"]), s["price"] / 100000.0,
        )
        pred, prob = tree.predict_tree(model["tree"], features)
        verdict = "FAIR" if pred == 1 else "NOT FAIR"
        print(f"  {s['make']} {s['model']} {s['manufacture_year']} @ LKR {s['price']:,} -> {verdict} (confidence {prob:.2f}, group median {med:.1f} lakhs)")


if __name__ == "__main__":
    main()
