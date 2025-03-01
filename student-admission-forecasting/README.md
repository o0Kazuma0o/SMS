# Student Admission Forecasting Project

This project aims to predict the daily number of students submitting their online admission applications and the total number of online admission applications for the months of July and August. The predictions are based on historical data extracted from the `sms3_pending_admission` database table.

## Project Structure

```
student-admission-forecasting
├── data
│   ├── raw
│   │   └── admissions.csv          # Raw data of student admission applications
│   └── processed
│       └── admissions_processed.csv  # Processed version of the admissions data
├── notebooks
│   └── data_exploration.ipynb       # Jupyter notebook for exploratory data analysis
├── src
│   ├── data_preprocessing.py         # Script for data preprocessing
│   ├── model_training.py             # Script for training the machine learning model
│   ├── model_evaluation.py           # Script for evaluating the model's performance
│   └── forecasting.py                 # Script for making predictions
├── requirements.txt                   # List of required Python dependencies
├── README.md                          # Project documentation
└── .gitignore                         # Files and directories to ignore by Git
```

## Setup Instructions

1. **Clone the repository**:
   ```
   git clone <repository-url>
   cd student-admission-forecasting
   ```

2. **Install the required dependencies**:
   It is recommended to create a virtual environment before installing the dependencies.
   ```
   pip install -r requirements.txt
   ```

## Usage

1. **Data Preprocessing**:
   Run the `data_preprocessing.py` script to clean and prepare the data for modeling.

2. **Model Training**:
   Execute the `model_training.py` script to train the machine learning model on the processed data.

3. **Model Evaluation**:
   Use the `model_evaluation.py` script to evaluate the performance of the trained model.

4. **Forecasting**:
   Run the `forecasting.py` script to make predictions for the daily and total number of admissions for July and August.

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue for any suggestions or improvements.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.