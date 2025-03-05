import pandas as pd
import numpy as np
import statsmodels.api as sm
from statsmodels.tsa.statespace.sarimax import SARIMAX
from sklearn.metrics import mean_squared_error
import matplotlib.pyplot as plt

def load_processed_data(file_path):
    data = pd.read_csv(file_path, parse_dates=['date'], index_col='date')
    return data

def fit_sarima_model(data, order, seasonal_order):
    model = SARIMAX(data, order=order, seasonal_order=seasonal_order)
    results = model.fit(disp=False)
    return results

def make_forecast(model, start_date, end_date):
    forecast = model.get_forecast(steps=(end_date - start_date).days + 1)
    forecast_index = pd.date_range(start=start_date, end=end_date)
    forecast_values = forecast.predicted_mean
    return pd.Series(forecast_values, index=forecast_index)

def plot_forecast(data, forecast):
    plt.figure(figsize=(12, 6))
    plt.plot(data, label='Historical Data')
    plt.plot(forecast, label='Forecast', color='red')
    plt.title('SARIMA Forecast')
    plt.xlabel('Date')
    plt.ylabel('Number of Applications')
    plt.legend()
    plt.show()

def evaluate_model(model, data):
    predictions = model.predict(start=data.index[0], end=data.index[-1])
    mse = mean_squared_error(data, predictions)
    return mse

if __name__ == "__main__":
    processed_data_path = '../data/processed/admissions_data_processed.csv'
    data = load_processed_data(processed_data_path)

    # Define SARIMA parameters
    order = (1, 1, 1)  # (p, d, q)
    seasonal_order = (1, 1, 1, 12)  # (P, D, Q, s)

    # Fit the model
    sarima_model = fit_sarima_model(data, order, seasonal_order)

    # Make forecast for next July
    next_year = data.index[-1].year + 1
    start_date = pd.Timestamp(f'{next_year}-07-01')
    end_date = pd.Timestamp(f'{next_year}-07-31')
    forecast = make_forecast(sarima_model, start_date, end_date)

    # Plot the forecast
    plot_forecast(data, forecast)

    # Evaluate the model
    mse = evaluate_model(sarima_model, data)
    print(f'Mean Squared Error: {mse}')