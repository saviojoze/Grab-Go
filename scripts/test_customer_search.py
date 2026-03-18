import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
from webdriver_manager.chrome import ChromeDriverManager

# --- Configuration ---
BASE_URL = "http://localhost/Mini%20Project/"
ADMIN_EMAIL = "admin@grabandgo.com"
ADMIN_PASS = "admin123"
TARGET_CUSTOMER = "Abner sam"

def run_customer_search_test():
    print("🚀 Initializing Chrome Driver...")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
    wait = WebDriverWait(driver, 20)

    try:
        # 1. Login
        print("🌐 Navigating to login...")
        driver.get(BASE_URL + "auth/login.php")
        time.sleep(2)

        print("✍️ Entering Admin Credentials...")
        wait.until(EC.presence_of_element_located((By.ID, "email"))).send_keys(ADMIN_EMAIL)
        driver.find_element(By.ID, "loginPassword").send_keys(ADMIN_PASS)
        
        print("🖱️ Clicking Login button...")
        driver.find_element(By.ID, "submitBtn").click()
        
        # 2. Navigate to Customers
        wait.until(EC.url_contains("admin/index.php"))
        print("✅ Login successful. Navigating to Customers...")
        driver.get(BASE_URL + "admin/customers.php")
        time.sleep(2)

        # 3. Search for Customer
        print(f"🔍 Searching for Customer: {TARGET_CUSTOMER}")
        
        # The input field has name="search"
        search_input = wait.until(EC.element_to_be_clickable((By.NAME, "search")))
        search_input.clear()
        
        # Type the name and hit Enter
        search_input.send_keys(TARGET_CUSTOMER)
        time.sleep(1)
        search_input.send_keys(Keys.RETURN)
        
        time.sleep(3) # Wait for page to reload with search results

        # 4. Verification
        print("🔍 Verifying results...")
        
        # Check if the name appears in the table results
        # Customers are listed in <h4> within the <tr>
        try:
            customer_h4 = wait.until(EC.presence_of_element_located(
                (By.XPATH, f"//h4[contains(text(), '{TARGET_CUSTOMER}')]")
            ))
            print(f"✅ SUCCESS! Customer '{TARGET_CUSTOMER}' was found in the search results.")
            
            # Highlight the result for visual confirmation
            driver.execute_script("arguments[0].style.border = '3px solid green'; arguments[0].parentNode.style.backgroundColor = '#e6fffa';", customer_h4)
            time.sleep(5)
        except:
            if TARGET_CUSTOMER in driver.page_source:
                 print(f"✅ SUCCESS! Customer '{TARGET_CUSTOMER}' found in page source.")
            else:
                 print(f"❌ FAILED! Could not find Customer '{TARGET_CUSTOMER}' in search results.")
                 # Print total results found badge if visible
                 try:
                     badge = driver.find_element(By.CLASS_NAME, "badge-neutral").text
                     print(f"Search result badge: {badge}")
                 except:
                     pass
            
            driver.save_screenshot("customer_search_failed.png")

    except Exception as e:
        print(f"❌ ERROR: {str(e)}")
        driver.save_screenshot("customer_search_crash.png")
    
    finally:
        print("🏁 Closing browser in 5 seconds...")
        time.sleep(5)
        driver.quit()

if __name__ == "__main__":
    run_customer_search_test()
