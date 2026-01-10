# Quick Product Images Setup

## Using Free Stock Images

Since I've hit the image generation quota, here's the fastest way to get product images:

### Option 1: Download from Unsplash (Free, High Quality)

Visit these direct links and save images to `C:\xampp\htdocs\Mini Project\images\products\`:

**Vegetables:**
- Cabbage: https://unsplash.com/photos/green-vegetable-on-brown-wooden-table-8gWEAAXJjtI/download?force=true&w=640
- Tomatoes: https://unsplash.com/photos/red-tomatoes-on-white-surface-lSOWJ6dLego/download?force=true&w=640
- Avocado: https://unsplash.com/photos/sliced-avocado-fruit-on-brown-wooden-table-08bOYnH_r_E/download?force=true&w=640

**Fruits:**
- Bananas: https://unsplash.com/photos/yellow-banana-fruits-on-brown-surface-JKMnm3CIncw/download?force=true&w=640
- Lemons: https://unsplash.com/photos/sliced-lemon-on-white-surface-CpsTAUPoScw/download?force=true&w=640
- Apples: https://unsplash.com/photos/red-apple-fruit-on-four-pyle-books-Wb63zqJ5gnE/download?force=true&w=640

**Dairy:**
- Milk: https://unsplash.com/photos/white-and-blue-plastic-bottle-8EzNkvLQosk/download?force=true&w=640
- Cheese: https://unsplash.com/photos/sliced-cheese-on-white-surface-qom5MPOER-I/download?force=true&w=640

**Bakery:**
- Bread: https://unsplash.com/photos/baked-bread-on-table-vMWbaKN0O7Y/download?force=true&w=640
- Croissants: https://unsplash.com/photos/brown-bread-on-white-ceramic-plate-0JYgd2QuMfw/download?force=true&w=640

**Beverages:**
- Orange Juice: https://unsplash.com/photos/orange-juice-in-clear-drinking-glass-L7en7Lb-Ovc/download?force=true&w=640
- Water: https://unsplash.com/photos/clear-glass-bottle-with-water-1DDol8rgUH8/download?force=true&w=640

### Option 2: Use Existing Generated Images

I already generated 6 images earlier. They're in:
```
C:\Users\Savio\.gemini\antigravity\brain\3c8360d0-a20c-478c-b1b1-66f48381cae2\
```

And copied to:
```
C:\xampp\htdocs\Mini Project\images\products\
```

These are ready to use!

### Quick PowerShell Script to Download

Run this in PowerShell from `C:\xampp\htdocs\Mini Project\images\products\`:

```powershell
# Download product images
$images = @{
    'milk.jpg' = 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400'
    'cheese.jpg' = 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400'
    'bread.jpg' = 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400'
    'croissants.jpg' = 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400'
    'orange-juice.jpg' = 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400'
    'water.jpg' = 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400'
}

foreach ($name in $images.Keys) {
    Invoke-WebRequest -Uri $images[$name] -OutFile $name
    Write-Host "Downloaded $name"
}
```

All images are free to use from Unsplash!
