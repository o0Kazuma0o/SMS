import pandas as pd

def load_data(file_path):
    """Load raw admissions data from a CSV file."""
    data = pd.read_csv(file_path)
    return data

def clean_data(data):
    """Clean the admissions data by handling missing values and duplicates."""
    # Drop duplicates
    data = data.drop_duplicates()
    
    # Handle missing values
    data = data.fillna(method='ffill')  # Forward fill for simplicity; adjust as needed
    
    return data

def preprocess_data(file_path, output_path):
    """Load, clean, and save the processed admissions data."""
    # Load data
    data = load_data(file_path)
    
    # Clean data
    cleaned_data = clean_data(data)
    
    # Save processed data
    cleaned_data.to_csv(output_path, index=False)

if __name__ == "__main__":
    raw_data_path = '../data/raw/admissions.csv'
    processed_data_path = '../data/processed/admissions_processed.csv'
    
    preprocess_data(raw_data_path, processed_data_path)