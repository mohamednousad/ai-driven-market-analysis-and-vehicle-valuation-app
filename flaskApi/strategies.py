"""
Strategy pattern (Gang of Four - Behavioral) for the valuation logic.

Each valuation approach is a self-contained strategy exposing the same
evaluate() interface, so the /predict endpoint can run them interchangeably
or combine them without knowing their internal details.
"""


class ValuationStrategy:
    """Common interface all valuation strategies implement."""

    name = "base"

    def evaluate(self, context):
        """
        Return a dict with at least {"fair": bool}.
        `context` carries everything a strategy might need.
        """
        raise NotImplementedError


class MedianBandStrategy(ValuationStrategy):
    """Fair if the submitted price sits inside the median +/- band range."""

    name = "median_band"

    def evaluate(self, context):
        price_lkr = context["price_lkr"]
        low_lkr = context["low_lkr"]
        high_lkr = context["high_lkr"]
        return {"fair": low_lkr <= price_lkr <= high_lkr}


class DecisionTreeStrategy(ValuationStrategy):
    """Fair if the from-scratch decision tree classifies the vehicle as fair (1)."""

    name = "decision_tree"

    def evaluate(self, context):
        prediction = context["prediction"]
        confidence = context["confidence"]
        return {"fair": prediction == 1, "confidence": confidence}


class CombinedValuationStrategy(ValuationStrategy):
    """
    Combines the two strategies: a price is only fair when BOTH the decision
    tree and the median band agree. This keeps the original behaviour while
    making each check independent and swappable.
    """

    name = "combined"

    def __init__(self, strategies):
        self.strategies = strategies

    def evaluate(self, context):
        results = {s.name: s.evaluate(context) for s in self.strategies}
        fair = all(r["fair"] for r in results.values())
        return {"fair": fair, "parts": results}
