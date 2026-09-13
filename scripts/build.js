const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');
const publicDir = path.join(rootDir, 'public');

console.log('--- Running Vercel Build Prep ---');

if (!fs.existsSync(publicDir)) {
    fs.mkdirSync(publicDir, { recursive: true });
}

// 1. Create public/index.html
const indexHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>INNOVATIONX</title></head><body></body></html>';
fs.writeFileSync(path.join(publicDir, 'index.html'), indexHtml, 'utf8');

// 2. Create public/robots.txt
fs.writeFileSync(path.join(publicDir, 'robots.txt'), 'User-agent: *\nAllow: /\n', 'utf8');

// 3. Helper to recursively copy directories
function copyFolderSync(from, to) {
    if (!fs.existsSync(from)) return;
    if (!fs.existsSync(to)) fs.mkdirSync(to, { recursive: true });
    fs.readdirSync(from).forEach(element => {
        const fromPath = path.join(from, element);
        const toPath = path.join(to, element);
        if (fs.lstatSync(fromPath).isDirectory()) {
            copyFolderSync(fromPath, toPath);
        } else {
            fs.copyFileSync(fromPath, toPath);
        }
    });
}

// Copy static assets into public
copyFolderSync(path.join(rootDir, 'css'), path.join(publicDir, 'css'));
copyFolderSync(path.join(rootDir, 'js'), path.join(publicDir, 'js'));
if (fs.existsSync(path.join(rootDir, 'images'))) {
    copyFolderSync(path.join(rootDir, 'images'), path.join(publicDir, 'images'));
}
if (fs.existsSync(path.join(rootDir, 'assets'))) {
    copyFolderSync(path.join(rootDir, 'assets'), path.join(publicDir, 'assets'));
}

console.log('Populated public directory successfully.');
