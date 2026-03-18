import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

# --- Configuration ---
BASE_URL = "http://localhost/Mini%20Project/"
ADMIN_EMAIL = "admin@grabandgo.com"
ADMIN_PASS = "admin123"
TARGET_ORDER_ID = "ORD-3FEA59C9"

def run_order_search_test():
    chrome_options = Options()
    # chrome_options.add_argument("--headless") # Comment out to see the browser
    print("🚀 Initializing Chrome Driver...")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
    wait = WebDriverWait(driver, 20)

    try:
        # 1. Login
        print("🌐 Navigating to login...")
        driver.get(BASE_URL + "auth/login.php") # Directly to login
        time.sleep(2)

        print("✍️ Entering Admin Credentials...")
        email_input = wait.until(EC.presence_of_element_located((By.ID, "email")))
        email_input.send_keys(ADMIN_EMAIL)
        time.sleep(1)
        
        pass_input = driver.find_element(By.ID, "loginPassword")
        pass_input.send_keys(ADMIN_PASS)
        time.sleep(1)
        
        print("🖱️ Clicking Login button...")
        submit_btn = driver.find_element(By.ID, "submitBtn")
        submit_btn.click()
        
        # 2. Verify Login and Navigate to Orders
        print("🏠 waiting for dashboard...")
        # Check if we are redirected to dashboard
        wait.until(EC.url_contains("admin/index.php"))
        print("✅ Login successful.")
        time.sleep(2)
        
        print("📂 Navigating directly to Orders page...")
        driver.get(BASE_URL + "admin/orders.php")
        time.sleep(2)

        # 3. Search for Order
        print(f"🔍 Locating search input and entering: {TARGET_ORDER_ID}")
        
        # The input field has name="search"
        search_input = wait.until(EC.element_to_be_clickable((By.NAME, "search")))
        search_input.clear()
        
        for char in TARGET_ORDER_ID:
            search_input.send_keys(char)
            time.sleep(0.1)
        
        time.sleep(1)

        print("🖱️ Pressing ENTER to search...")
        from selenium.webdriver.common.keys import Keys
        search_input.send_keys(Keys.RETURN)
        
        # Wait for page to reload with search results
        time.sleep(3)

        # 4. Verification
        print("🔍 Scanning table for results...")
        
        # Look for the order number in the table
        try:
            # The order number is likely inside a <tr> -> <td> -> <strong>
            order_strong = wait.until(EC.presence_of_element_located((By.XPATH, f"//strong[contains(text(), '{TARGET_ORDER_ID}')]")))
            print(f"✅ SUCCESS! Order '{TARGET_ORDER_ID}' was found in the search results.")
            
            # Highlight the row
            driver.execute_script("arguments[0].style.backgroundColor = 'yellow'; arguments[0].style.border = '2px solid red';", order_strong)
            time.sleep(5)
        except:
            if TARGET_ORDER_ID in driver.page_source:
                 print(f"✅ SUCCESS! Order '{TARGET_ORDER_ID}' found in page source.")
            else:
                 print(f"❌ FAILED! Could not find Order '{TARGET_ORDER_ID}' in results.")
                 # Print the text within the table to debug
                 try:
                     table_text = driver.find_element(By.CLASS_NAME, "data-table").text
                     print("Table content visible to driver:")
                     print("-" * 20)
                     print(table_text)
                     print("-" * 20)
                 except:
                     print("Could not find the data-table element.")
            
            driver.save_screenshot("order_search_failed_view.png")

    except Exception as e:
        print(f"❌ TEST ERROR: {str(e)}")
        driver.save_screenshot("order_search_crash.png")
    
    finally:
        print("🏁 Closing browser in 5 seconds...")
        time.sleep(5)
        driver.quit()

if __name__ == "__main__":
    run_order_search_test()
