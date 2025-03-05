def load_data(file_path):
    import pandas as pd
    return pd.read_csv(file_path)

def save_data(data, file_path):
    import pandas as pd
    data.to_csv(file_path, index=False)

def plot_time_series(data, x_col, y_col, title='Time Series Plot', xlabel='Date', ylabel='Value'):
    import matplotlib.pyplot as plt
    plt.figure(figsize=(10, 5))
    plt.plot(data[x_col], data[y_col])
    plt.title(title)
    plt.xlabel(xlabel)
    plt.ylabel(ylabel)
    plt.grid()
    plt.show()