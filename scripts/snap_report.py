import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager

try:
    html_path = os.path.abspath('report_temp.html')
    print(f"Loading HTML from: {html_path}")

    options = Options()
    options.add_argument('--headless')
    options.add_argument('--window-size=1200,1000')

    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    driver.get(f"file:///{html_path}")
    
    # Wait for rendering
    time.sleep(2)

    table = driver.find_element(By.TAG_NAME, 'table')
    
    # Create reports directory if it doesn't exist
    os.makedirs('reports', exist_ok=True)
    
    # take screenshot
    screenshot_path = os.path.abspath(os.path.join('reports', 'test_report.png'))
    table.screenshot(screenshot_path)
    
    print(f"Screenshot successfully saved to: {screenshot_path}")

finally:
    if 'driver' in locals():
        driver.quit()
