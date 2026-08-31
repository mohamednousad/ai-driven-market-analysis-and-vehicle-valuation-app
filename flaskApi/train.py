import tree


def main():
    rows = tree.load_rows()
    print(f"[AutoValue AI] Loaded {len(rows)} clean rows from dataset.")
    model = tree.train_model(rows)
    x, y = tree.make_dataset(rows, model["encoders"], model["medians"], model["global_median"])
    correct = 0
    for features, label in zip(x, y):
        pred, _prob = tree.predict_tree(model["tree"], features)
        if pred == label:
            correct += 1
    accuracy = correct / len(y) if y else 0.0
    tree.save_model(model)
    print(f"[AutoValue AI] Decision tree trained on {len(rows)} rows.")
    print(f"[AutoValue AI] Training accuracy: {accuracy * 100:.2f}%")
    print(f"[AutoValue AI] Model saved to {tree.MODEL_PATH}")


if __name__ == "__main__":
    main()
