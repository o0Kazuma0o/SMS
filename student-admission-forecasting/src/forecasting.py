import pandas as pd
from sklearn.externals import joblib
from datetime import datetime, timedelta

def load_model(model_path):
    return joblib.load(model_path)

def predict_daily_admissions(model, start_date, end_date):
    date_range = pd.date_range(start=start_date, end=end_date)
    predictions = model.predict(date_range.to_frame(index=False, name='date'))
    return predictions

def forecast_admissions(model_path, start_date='2023-07-01', end_date='2023-08-31'):
    model = load_model(model_path)
    daily_predictions = predict_daily_admissions(model, start_date, end_date)
    
    total_admissions = daily_predictions.sum()
    
    return daily_predictions, total_admissions

if __name__ == "__main__":
    model_path = 'path/to/your/trained_model.pkl'  # Update with the actual model path
    daily_admissions, total_admissions = forecast_admissions(model_path)
    
    print("Daily Admissions Predictions:")
    print(daily_admissions)
    print(f"Total Admissions from {start_date} to {end_date}: {total_admissions}")