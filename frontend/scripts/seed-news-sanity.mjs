/**
 * Seed Sanity news articles from legacy /news/* content in site-content.json
 *
 * Usage:
 *   SANITY_PROJECT_ID=xxx SANITY_DATASET=production SANITY_API_TOKEN=xxx node scripts/seed-news-sanity.mjs
 */

import { createClient } from '@sanity/client';
import { readFileSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const dataPath = resolve(__dirname, '../data/site-content.json');

const client = createClient({
  projectId: process.env.SANITY_PROJECT_ID || process.env.NEXT_PUBLIC_SANITY_PROJECT_ID,
  dataset: process.env.SANITY_DATASET || process.env.NEXT_PUBLIC_SANITY_DATASET || 'production',
  apiVersion: '2024-01-01',
  token: process.env.SANITY_API_TOKEN,
  useCdn: false,
});

if (!client.config().projectId || !client.config().token) {
  console.error('Missing SANITY_PROJECT_ID or SANITY_API_TOKEN environment variables');
  process.exit(1);
}

const data = JSON.parse(readFileSync(dataPath, 'utf8'));

function stripHtml(html) {
  if (!html) return '';
  return html
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/li>/gi, '\n')
    .replace(/<\/p>/gi, '\n')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/\s+\n/g, '\n')
    .replace(/\n\s+/g, '\n')
    .replace(/[ \t]+/g, ' ')
    .replace(/\n{2,}/g, '\n\n')
    .trim();
}

function slugify(value) {
  return value
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

function plainTextToBlocks(text) {
  if (!text) return [];

  return text
    .split(/\n{2,}/)
    .map((paragraph) => paragraph.trim())
    .filter(Boolean)
    .map((paragraph, index) => ({
      _type: 'block',
      _key: `block_${index}`,
      style: 'normal',
      markDefs: [],
      children: [
        {
          _type: 'span',
          _key: `span_${index}`,
          text: paragraph,
          marks: [],
        },
      ],
    }));
}

function buildExcerpt(item, bodyText) {
  const fromDescription = (item.description || '').trim();
  if (fromDescription) return fromDescription.slice(0, 220);

  const fromKeywords = (item.keywords || '').trim();
  if (fromKeywords) return fromKeywords.slice(0, 220);

  return bodyText.slice(0, 220);
}

function resolveLegacyAssetUrl(url) {
  if (!url) return '';
  if (/^https?:\/\//i.test(url)) return url;
  if (url.startsWith('/')) return new URL(url, 'https://beaconstonerealty.com').toString();
  return url;
}

async function uploadImageUrl(url) {
  if (!url || url === '/images/no_picture.jpg') return null;

  try {
    const resolvedUrl = resolveLegacyAssetUrl(url);
    const response = await fetch(resolvedUrl);
    if (!response.ok) return null;

    const buffer = Buffer.from(await response.arrayBuffer());
    const contentType = response.headers.get('content-type') || 'image/jpeg';
    const asset = await client.assets.upload('image', buffer, { contentType });
    console.log(`  ✓ Uploaded image: ${resolvedUrl.split('/').pop()}`);
    return {
      _type: 'image',
      asset: { _type: 'reference', _ref: asset._id },
    };
  } catch (err) {
    console.error(`  ✗ Failed to upload: ${url}`, err.message);
    return null;
  }
}

function collectLegacyNews() {
  return Object.values(data.newsById)
    .filter((item) => typeof item.url === 'string' && /^\/news\/\d+$/i.test(item.url))
    .filter((item) => (item.title || '').trim())
    .sort((left, right) => Number(right.id) - Number(left.id));
}

async function seedNewsArticles() {
  console.log('\n── Seeding Real Estate News ──');
  const items = collectLegacyNews();

  if (items.length === 0) {
    console.log('No legacy /news/* articles found');
    return;
  }

  const baseDate = new Date(Date.UTC(2025, 0, 1));

  for (let index = 0; index < items.length; index++) {
    const item = items[index];
    const bodyText = stripHtml(item.content || [item.keywords, item.description].filter(Boolean).join('\n\n'));
    const excerpt = buildExcerpt(item, bodyText);
    const slug = slugify(item.title) || String(item.id);
    const publishedAt = item.add_time
      ? new Date(item.add_time * 1000).toISOString()
      : new Date(baseDate.getTime() + (items.length - index) * 86400000).toISOString();

    console.log(`  Seeding article: ${item.title}`);

    const doc = {
      _id: `newsArticle-${item.id}`,
      _type: 'newsArticle',
      legacyId: Number(item.id),
      title: item.title.trim(),
      slug: { _type: 'slug', current: slug },
      excerpt,
      coverImageAlt: item.title.trim(),
      publishedAt,
      featured: index < 3,
      body: plainTextToBlocks(bodyText),
      seoTitle: item.title.trim(),
      seoDescription: excerpt,
    };

    if (item.thumbnail && item.thumbnail !== '/images/no_picture.jpg') {
      const coverImage = await uploadImageUrl(item.thumbnail);
      if (coverImage) doc.coverImage = coverImage;
    }

    await client.createOrReplace(doc);
    console.log(`  ✓ News article created: ${item.title}`);
  }
}

async function main() {
  console.log('Seeding Sanity news articles from site-content.json...');
  console.log(`Project: ${client.config().projectId} / ${client.config().dataset}`);
  await seedNewsArticles();
  console.log('\n✓ News seeding complete!');
}

main().catch((err) => {
  console.error('News seeding failed:', err);
  process.exit(1);
});
