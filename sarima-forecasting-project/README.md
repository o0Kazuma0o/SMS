# SARIMA Forecasting Project

This project aims to predict the daily and total number of online admission applications from July to August based on historical data from previous years. The forecasting is performed using the Seasonal Autoregressive Integrated Moving Average (SARIMA) model.

## Project Structure

- **data/**: Contains the datasets used in the project.
  - **raw/**: Contains the raw historical data of student admissions.
    - `admissions_data.csv`: The raw admissions data.
  - **processed/**: Contains the processed version of the admissions data.
    - `admissions_data_processed.csv`: The cleaned and transformed admissions data ready for modeling.

- **notebooks/**: Contains Jupyter notebooks for various stages of the project.
  - `data_preprocessing.ipynb`: Notebook for data preprocessing tasks.
  - `exploratory_analysis.ipynb`: Notebook for exploratory data analysis (EDA).
  - `model_training.ipynb`: Notebook for training the SARIMA model.

- **src/**: Contains Python scripts for data processing and modeling.
  - `data_preprocessing.py`: Functions for loading and cleaning the admissions data.
  - `exploratory_analysis.py`: Functions for performing EDA on the admissions data.
  - `model_training.py`: Implementation of the SARIMA model for predictions.
  - `utils.py`: Utility functions used across the project.

- **requirements.txt**: Lists the dependencies required for the project.

## Setup Instructions

1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Install the required dependencies using:
   ```
   pip install -r requirements.txt
   ```

## Usage Guidelines

- Use the `data_preprocessing.ipynb` notebook to preprocess the raw admissions data.
- Perform exploratory data analysis using the `exploratory_analysis.ipynb` notebook to visualize trends and patterns.
- Train the SARIMA model using the `model_training.ipynb` notebook to make predictions on future admissions.

## License

This project is licensed under the MIT License.