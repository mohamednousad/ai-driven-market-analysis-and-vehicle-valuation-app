import json
import math


class Node:
    __slots__ = ("feature", "threshold", "left", "right", "value", "std", "count")

    def __init__(self):
        self.feature = None
        self.threshold = None
        self.left = None
        self.right = None
        self.value = 0.0
        self.std = 0.0
        self.count = 0


class DecisionTreeRegressor:
    def __init__(self, max_depth=12, min_samples_split=20, min_samples_leaf=8):
        self.max_depth = max_depth
        self.min_samples_split = min_samples_split
        self.min_samples_leaf = min_samples_leaf
        self.root = None
        self.n_features = 0

    def fit(self, X, y):
        self.n_features = len(X[0])
        indices = list(range(len(y)))
        self.root = self._build(X, y, indices, 0)
        return self

    def _build(self, X, y, indices, depth):
        node = Node()
        n = len(indices)
        node.count = n
        total = sum(y[i] for i in indices)
        mean = total / n
        node.value = mean
        var = sum((y[i] - mean) ** 2 for i in indices) / n
        node.std = math.sqrt(var)

        if depth >= self.max_depth or n < self.min_samples_split or var == 0.0:
            return node

        split = self._best_split(X, y, indices, var * n)
        if split is None:
            return node

        feature, threshold = split
        left_idx = [i for i in indices if X[i][feature] <= threshold]
        right_idx = [i for i in indices if X[i][feature] > threshold]
        if len(left_idx) < self.min_samples_leaf or len(right_idx) < self.min_samples_leaf:
            return node

        node.feature = feature
        node.threshold = threshold
        node.left = self._build(X, y, left_idx, depth + 1)
        node.right = self._build(X, y, right_idx, depth + 1)
        return node

    def _best_split(self, X, y, indices, parent_sse):
        n = len(indices)
        best_gain = 1e-9
        best = None

        for f in range(self.n_features):
            order = sorted(indices, key=lambda i: X[i][f])
            values = [X[i][f] for i in order]
            targets = [y[i] for i in order]

            left_sum = 0.0
            left_sq = 0.0
            total_sum = sum(targets)
            total_sq = sum(t * t for t in targets)

            for k in range(n - 1):
                t = targets[k]
                left_sum += t
                left_sq += t * t
                if values[k] == values[k + 1]:
                    continue
                left_n = k + 1
                right_n = n - left_n
                if left_n < self.min_samples_leaf or right_n < self.min_samples_leaf:
                    continue
                right_sum = total_sum - left_sum
                right_sq = total_sq - left_sq
                left_sse = left_sq - (left_sum * left_sum) / left_n
                right_sse = right_sq - (right_sum * right_sum) / right_n
                gain = parent_sse - (left_sse + right_sse)
                if gain > best_gain:
                    best_gain = gain
                    best = (f, (values[k] + values[k + 1]) / 2.0)

        return best

    def predict_one(self, x):
        node = self.root
        while node.feature is not None:
            node = node.left if x[node.feature] <= node.threshold else node.right
        return node.value, node.std, node.count

    def predict(self, X):
        return [self.predict_one(x)[0] for x in X]

    def depth(self):
        def d(node):
            if node is None or node.feature is None:
                return 0
            return 1 + max(d(node.left), d(node.right))
        return d(self.root)

    def leaf_count(self):
        def c(node):
            if node is None:
                return 0
            if node.feature is None:
                return 1
            return c(node.left) + c(node.right)
        return c(self.root)

    def to_dict(self):
        def encode(node):
            if node is None:
                return None
            return {
                "f": node.feature,
                "t": node.threshold,
                "v": round(node.value, 4),
                "s": round(node.std, 4),
                "n": node.count,
                "l": encode(node.left),
                "r": encode(node.right),
            }
        return {
            "max_depth": self.max_depth,
            "min_samples_split": self.min_samples_split,
            "min_samples_leaf": self.min_samples_leaf,
            "n_features": self.n_features,
            "root": encode(self.root),
        }

    @classmethod
    def from_dict(cls, data):
        tree = cls(data["max_depth"], data["min_samples_split"], data["min_samples_leaf"])
        tree.n_features = data["n_features"]

        def decode(d):
            if d is None:
                return None
            node = Node()
            node.feature = d["f"]
            node.threshold = d["t"]
            node.value = d["v"]
            node.std = d["s"]
            node.count = d["n"]
            node.left = decode(d["l"])
            node.right = decode(d["r"])
            return node

        tree.root = decode(data["root"])
        return tree

    def save(self, path):
        with open(path, "w") as fh:
            json.dump(self.to_dict(), fh)

    @classmethod
    def load(cls, path):
        with open(path) as fh:
            return cls.from_dict(json.load(fh))
