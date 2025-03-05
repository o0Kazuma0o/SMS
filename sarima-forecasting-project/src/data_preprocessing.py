import pandas as pd

def load_data(file_path):
    """Load the admissions data from a CSV file."""
    data = pd.read_csv(file_path)
    return data

def clean_data(data):
    """Clean the admissions data by handling missing values and duplicates."""
    # Drop duplicates
    data = data.drop_duplicates()
    
    # Fill missing values (example: fill with the mean or median)
    data.fillna(method='ffill', inplace=True)
    
    return data

def preprocess_data(file_path, output_path):
    """Load, clean, and save the processed admissions data."""
    data = load_data(file_path)
    cleaned_data = clean_data(data)
    cleaned_data.to_csv(output_path, index=False)

if __name__ == "__main__":
    raw_data_path = '../data/raw/admissions_data.csv'
    processed_data_path = '../data/processed/admissions_data_processed.csv'
    preprocess_data(raw_data_path, processed_data_path)