import tkinter as tk
from tkinter import filedialog, simpledialog, messagebox
import csv
import requests
import pandas as pd

class CSVAnnotatorApp:
    def __init__(self, root):
        self.root = root
        self.root.title("CSV Annotator")

        self.load_button = tk.Button(root, text="Load CSV", command=self.load_csv)
        self.load_button.pack(pady=20)

        self.csv_data = None
        self.key_column = None

    def load_csv(self):
        file_path = filedialog.askopenfilename(filetypes=[("CSV files", "*.csv")])
        if file_path:
            self.csv_data = pd.read_csv(file_path)
            self.ask_key_column()

    def ask_key_column(self):
        if self.csv_data is not None:
            columns = self.csv_data.columns.tolist()
            self.key_column = simpledialog.askstring("Input", f"Enter the key column from {columns}:")
            if self.key_column in columns:
                self.query_api_and_annotate()
            else:
                messagebox.showerror("Error", "Invalid column name")

    def query_api_and_annotate(self):
        if self.csv_data is not None and self.key_column is not None:
            for index, row in self.csv_data.iterrows():
                key_value = row[self.key_column]
                response = requests.get(f"https://api.example.com/data?key={key_value}")
                if response.status_code == 200:
                    data = response.json()
                    for key, value in data.items():
                        self.csv_data.at[index, key] = value
                else:
                    messagebox.showerror("Error", f"API request failed for key: {key_value}")
            self.save_csv()

    def save_csv(self):
        save_path = filedialog.asksaveasfilename(defaultextension=".csv", filetypes=[("CSV files", "*.csv")])
        if save_path:
            self.csv_data.to_csv(save_path, index=False)
            messagebox.showinfo("Success", "CSV file saved successfully")

if __name__ == "__main__":
    root = tk.Tk()
    app = CSVAnnotatorApp(root)
    root.mainloop()
