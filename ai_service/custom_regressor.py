import numpy as np


class CustomLinearRegressor:
    def __init__(self, weights=None, bias=0.0):
        self.weights = np.array(weights, dtype=float) if weights is not None else None
        self.bias = float(bias)

    def predict(self, x):
        matrix = np.array(x, dtype=float)
        if matrix.ndim == 1:
            matrix = matrix.reshape(1, -1)
        return matrix.dot(self.weights) + self.bias


class CustomANNRegressor:
    def __init__(self, layer_dimensions=None, learning_rate=0.001):
        self.layer_dimensions = layer_dimensions or []
        self.learning_rate = learning_rate
        self.n_layers = len(self.layer_dimensions)

    def relu(self, z):
        return np.maximum(0, z)

    def forward(self, x):
        a = np.array(x, dtype=float)
        if a.ndim == 1:
            a = a.reshape(-1, 1)
        else:
            a = a.T
        last_layer = self.n_layers - 1
        for layer in range(1, last_layer):
            w = getattr(self, f"W{layer}")
            b = getattr(self, f"b{layer}")
            a = self.relu(np.dot(w, a) + b)
        w = getattr(self, f"W{last_layer}")
        b = getattr(self, f"b{last_layer}")
        z = np.dot(w, a) + b
        return z.reshape(-1)

    def predict(self, x):
        return self.forward(x)
