from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score
import pandas as pd
import matplotlib.pyplot as plt

def evaluate_model(y_true, y_pred):
    """
    Evaluate the performance of the model using various metrics.
    
    Parameters:
    y_true (pd.Series): Actual values
    y_pred (pd.Series): Predicted values
    
    Returns:
    dict: A dictionary containing evaluation metrics
    """
    mse = mean_squared_error(y_true, y_pred)
    mae = mean_absolute_error(y_true, y_pred)
    r2 = r2_score(y_true, y_pred)

    evaluation_results = {
        'Mean Squared Error': mse,
        'Mean Absolute Error': mae,
        'R-squared': r2
    }

    return evaluation_results

def plot_predictions(y_true, y_pred):
    """
    Plot the actual vs predicted values.
    
    Parameters:
    y_true (pd.Series): Actual values
    y_pred (pd.Series): Predicted values
    """
    plt.figure(figsize=(10, 5))
    plt.plot(y_true.index, y_true, label='Actual', color='blue')
    plt.plot(y_pred.index, y_pred, label='Predicted', color='orange')
    plt.title('Actual vs Predicted Admissions')
    plt.xlabel('Date')
    plt.ylabel('Number of Admissions')
    plt.legend()
    plt.show()