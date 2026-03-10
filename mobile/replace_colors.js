const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'src', 'screens');
const files = fs.readdirSync(dir).filter(f => f.endsWith('.js'));

files.forEach(file => {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    let updated = false;
    if (content.includes('#4318FF')) {
        content = content.replace(/#4318FF/g, '#1877F2');
        updated = true;
    }
    if (content.includes('#05CD99')) {
        content = content.replace(/#05CD99/g, '#00D563');
        updated = true;
    }
    if (content.includes('#FFB300')) {
        content = content.replace(/#FFB300/g, '#ea580c');
        updated = true;
    }

    if (updated) {
        fs.writeFileSync(filePath, content);
        console.log(`Updated colors in ${file}`);
    }
});
