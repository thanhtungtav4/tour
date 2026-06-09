// Asset download script for newtrip.com.vn
// Downloads all images, favicons, and other assets

import fs from 'fs';
import path from 'path';
import https from 'https';
import http from 'http';

const OUTPUT_DIR = '/Volumes/Manager Data/Tool/newtrip.com.vn/public';
const DOCS_DIR = '/Volumes/Manager Data/Tool/newtrip.com.vn/docs/research';

// Image URLs from global-extraction.json
const images = [
  // Logo
  'https://i.postimg.cc/pr3jTYMs/582549528-122103437163116307-4161394095605531748-n.jpg',

  // Tour images
  'https://i.postimg.cc/vBwF4WW6/LICH-THANG-(6).png',
  'https://i.postimg.cc/0QY6j59P/langbiang4-35.jpg',
  'https://i.postimg.cc/zB3S7rT6/CT04_01_2026_369.jpg',
  'https://i.postimg.cc/B6QpLFhB/MD-28-12-2025-408.jpg',
  'https://i.postimg.cc/FHJpkMnZ/DSC_0032.jpg',
  'https://i.postimg.cc/MKsVVWv5/Yandoane-038.jpg',
  'https://i.postimg.cc/Njrsn19H/DSC07358.jpg',
  'https://i.postimg.cc/TwpRJMjX/z7907053911333-6513645fd78fdd8a23a05adb85297fbe.jpg',
  'https://i.postimg.cc/pL3vQgy7/NLNN-2497.jpg',
  'https://i.postimg.cc/xT487pc3/DSC06595.jpg',
  'https://i.postimg.cc/DwsWCTQj/IMG_2096.avif',
  'https://i.postimg.cc/sxmLBLnV/thao_nguyen_pal_sol_ivivu_4.jpg',

  // Unsplash image
  'https://images.unsplash.com/photo-1551632811-561732d1e306?q=80&w=2070',

  // Blog images
  'https://i.postimg.cc/26jyMVj2/49.jpg',
  'https://i.postimg.cc/TP9cM5xB/62.jpg',
];

// Favicon URLs
const favicons = [
  'https://i.postimg.cc/pr3jTYMs/582549528-122103437163116307-4161394095605531748-n.jpg',
];

// Ensure directories exist
const imagesDir = path.join(OUTPUT_DIR, 'images');
const seoDir = path.join(OUTPUT_DIR, 'seo');

if (!fs.existsSync(imagesDir)) {
  fs.mkdirSync(imagesDir, { recursive: true });
}
if (!fs.existsSync(seoDir)) {
  fs.mkdirSync(seoDir, { recursive: true });
}

// Download a single file
function downloadFile(url, destPath) {
  return new Promise((resolve, reject) => {
    const file = fs.createWriteStream(destPath);
    const protocol = url.startsWith('https') ? https : http;

    console.log(`📥 Downloading: ${url}`);
    console.log(`   → ${destPath}`);

    protocol.get(url, (response) => {
      // Handle redirects
      if (response.statusCode === 301 || response.statusCode === 302) {
        const redirectUrl = response.headers.location;
        console.log(`   ↪️ Redirect to: ${redirectUrl}`);
        file.close();
        downloadFile(redirectUrl, destPath).then(resolve).catch(reject);
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
        console.log(`   ✅ Done`);
        resolve();
      });
    }).on('error', (err) => {
      file.close();
      fs.unlink(destPath, () => {}); // Clean up
      reject(err);
    });
  });
}

// Extract filename from URL
function getFilename(url) {
  const urlObj = new URL(url);
  const pathname = urlObj.pathname;
  const basename = path.basename(pathname);
  // Handle query strings and special characters
  return decodeURIComponent(basename).replace(/[^a-zA-Z0-9.-]/g, '_');
}

// Download with retry
async function downloadWithRetry(url, destPath, retries = 3) {
  for (let i = 0; i < retries; i++) {
    try {
      await downloadFile(url, destPath);
      return true;
    } catch (err) {
      console.log(`   ❌ Attempt ${i + 1} failed: ${err.message}`);
      if (i< retries - 1) {
        console.log(`   🔄 Retrying in 1 second...`);
        await new Promise(r => setTimeout(r, 1000));
      }
    }
  }
  console.log(`   ⚠️ Skipping after ${retries} attempts`);
  return false;
}

// Main download function
async function downloadAssets() {
  console.log('🚀 Starting asset download...\n');

  const results = { successful: 0, failed: 0, skipped: 0 };
  const failedUrls = [];

  // Download images
  console.log('📸 Downloading images...\n');
  for (const url of images) {
    const filename = getFilename(url);
    const destPath = path.join(imagesDir, filename);

    // Check if already exists
    if (fs.existsSync(destPath)) {
      console.log(`   ⏭️ Already exists: ${filename}`);
      results.skipped++;
      continue;
    }

    const success = await downloadWithRetry(url, destPath);
    if (success) {
      results.successful++;
    } else {
      results.failed++;
      failedUrls.push(url);
    }
  }

  // Download favicons
  console.log('\n🔖 Downloading favicons...\n');
  for (const url of favicons) {
    const filename = 'favicon.png';
    const destPath = path.join(seoDir, filename);

    if (fs.existsSync(destPath)) {
      console.log(`   ⏭️ Already exists: ${filename}`);
      results.skipped++;
      continue;
    }

    const success = await downloadWithRetry(url, destPath);
    if (success) {
      results.successful++;
    } else {
      results.failed++;
      failedUrls.push(url);
    }
  }

  // Summary
  console.log('\n========================================');
  console.log('📊 Download Summary');
  console.log('========================================');
  console.log(`✅ Successful: ${results.successful}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`⏭️ Skipped: ${results.skipped}`);

  if (failedUrls.length > 0) {
    console.log('\n⚠️ Failed URLs:');
    failedUrls.forEach(url => console.log(`   - ${url}`));
  }

  // Save download manifest
  const manifest = {
    timestamp: new Date().toISOString(),
    totalImages: images.length,
    totalFavicons: favicons.length,
    results,
    downloadedFiles: fs.readdirSync(imagesDir),
 };

  fs.writeFileSync(
    path.join(DOCS_DIR, 'download-manifest.json'),
    JSON.stringify(manifest, null, 2)
  );

  console.log('\n🎉 Asset download complete!');
}

downloadAssets().catch(console.error);
