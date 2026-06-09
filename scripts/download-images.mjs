// Download images from postimg.cc and save to public/images
// Run with: node scripts/download-images.mjs

import fs from 'fs';
import path from 'path';
import https from 'https';
import http from 'http';

const OUTPUT_DIR = '/Volumes/Manager Data/Tool/newtrip.com.vn/public/images';

// Ensure directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

// Image URLs from newtrip.com.vn
const images = [
  { url: 'https://i.postimg.cc/pr3jTYMs/582549528-122103437163116307-4161394095605531748-n.jpg', filename: 'logo.jpg' },
  { url: 'https://i.postimg.cc/0QY6j59P/langbiang4-35.jpg', filename: 'langbiang.jpg' },
  { url: 'https://i.postimg.cc/zB3S7rT6/CT04_01_2026_369.jpg', filename: 'rung-cat-tien.jpg' },
  { url: 'https://i.postimg.cc/B6QpLFhB/MD-28-12-2025-408.jpg', filename: 'bu-gia-map.jpg' },
  { url: 'https://i.postimg.cc/FHJpkMnZ/DSC_0032.jpg', filename: 'nui-dinh.jpg' },
  { url: 'https://i.postimg.cc/MKsVVWv5/Yandoane-038.jpg', filename: 'yangdoan.jpg' },
  { url: 'https://i.postimg.cc/Njrsn19H/DSC07358.jpg', filename: 'nui-chua-chan.jpg' },
  { url: 'https://i.postimg.cc/TwpRJMjX/z7907053911333-6513645fd78fdd8a23a05adb85297fbe.jpg', filename: 'brahyang.jpg' },
  { url: 'https://i.postimg.cc/pL3vQgy7/NLNN-2497.jpg', filename: 'thac-lieng-ai.jpg' },
  { url: 'https://i.postimg.cc/xT487pc3/DSC06595.jpg', filename: 'ta-cu-ke-ga.jpg' },
  { url: 'https://i.postimg.cc/DwsWCTQj/IMG_2096.avif', filename: 'nui-minh-dam.jpg' },
  { url: 'https://i.postimg.cc/sxmLBLnV/thao_nguyen_pal_sol_ivivu_4.jpg', filename: 'thao-nguyen-palsol.jpg' },
  { url: 'https://i.postimg.cc/26jyMVj2/49.jpg', filename: 'blog-1.jpg' },
  { url: 'https://i.postimg.cc/TP9cM5xB/62.jpg', filename: 'blog-2.jpg' },
  { url: 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&q=80', filename: 'about-adventure.jpg' },
];

function downloadFile(url, destPath) {
  return new Promise((resolve, reject) => {
    const file = fs.createWriteStream(destPath);
    const protocol = url.startsWith('https') ? https : http;

    console.log(`📥 ${url.substring(0, 60)}...`);
    
    protocol.get(url, (response) => {
      if (response.statusCode === 301 || response.statusCode === 302) {
        file.close();
        downloadFile(response.headers.location, destPath).then(resolve).catch(reject);
        return;
      }

      if (response.statusCode !== 200) {
        file.close();
        reject(new Error(`HTTP ${response.statusCode}`));
        return;
      }

      response.pipe(file);
      file.on('finish', () => {
        file.close();
        console.log(`   ✅ → ${path.basename(destPath)}`);
        resolve();
      });
    }).on('error', (err) => {
      file.close();
      fs.unlink(destPath, () => {});
      reject(err);
    });
  });
}

async function main() {
  console.log('🚀 Downloading images...\n');
  let success = 0, failed = 0;

  for (const img of images) {
    const destPath = path.join(OUTPUT_DIR, img.filename);
    
    if (fs.existsSync(destPath)) {
      console.log(`   ⏭️ Already exists: ${img.filename}`);
      success++;
      continue;
    }

    try {
      await downloadFile(img.url, destPath);
      success++;
    } catch (err) {
      console.log(`   ❌ Failed: ${err.message}`);
      failed++;
    }
  }

  console.log(`\n📊 Done: ${success} success, ${failed} failed`);
}

main().catch(console.error);