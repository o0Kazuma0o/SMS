import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

def load_data(file_path):
    data = pd.read_csv(file_path)
    return data

def plot_applications_over_time(data):
    plt.figure(figsize=(12, 6))
    sns.lineplot(data=data, x='date', y='applications', marker='o')
    plt.title('Daily Online Admission Applications Over Time')
    plt.xlabel('Date')
    plt.ylabel('Number of Applications')
    plt.xticks(rotation=45)
    plt.tight_layout()
    plt.show()

def summary_statistics(data):
    return data.describe()

def plot_seasonal_decomposition(data, column='applications'):
    from statsmodels.tsa.seasonal import seasonal_decompose
    data.set_index('date', inplace=True)
    decomposition = seasonal_decompose(data[column], model='additive')
    decomposition.plot()
    plt.show()