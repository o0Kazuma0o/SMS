import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error
import joblib

def load_data(file_path):
    data = pd.read_csv(file_path)
    return data

def preprocess_data(data):
    data['created_at'] = pd.to_datetime(data['created_at'])
    data['date'] = data['created_at'].dt.date
    daily_admissions = data.groupby('date').size().reset_index(name='daily_admissions')
    return daily_admissions

def train_model(daily_admissions):
    X = daily_admissions.index.values.reshape(-1, 1)  # Using index as feature
    y = daily_admissions['daily_admissions'].values

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    model = RandomForestRegressor(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)

    predictions = model.predict(X_test)
    mse = mean_squared_error(y_test, predictions)
    print(f'Model Mean Squared Error: {mse}')

    return model

def save_model(model, model_path):
    joblib.dump(model, model_path)

if __name__ == "__main__":
    raw_data_path = '../data/raw/admissions.csv'
    model_path = '../models/admission_forecasting_model.pkl'

    data = load_data(raw_data_path)
    daily_admissions = preprocess_data(data)
    model = train_model(daily_admissions)
    save_model(model, model_path)