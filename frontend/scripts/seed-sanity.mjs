/**
 * Seed Sanity with data from site-content.json
 *
 * Usage:
 *   SANITY_PROJECT_ID=xxx SANITY_DATASET=production SANITY_API_TOKEN=xxx node scripts/seed-sanity.mjs
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

// ─── Helper: strip HTML tags ───
function stripHtml(html) {
  if (!html) return '';
  return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').trim();
}

// ─── Helper: HTML to portable text blocks ───
function htmlToBlocks(html) {
  if (!html) return [];
  // Simple conversion: split by <p> tags, each becomes a block
  const paragraphs = html
    .replace(/<br\s*\/?>/gi, '\n')
    .split(/<\/?p[^>]*>/gi)
    .map((p) => stripHtml(p))
    .filter((p) => p.length > 0);

  return paragraphs.map((text, i) => ({
    _type: 'block',
    _key: `block_${i}`,
    style: 'normal',
    markDefs: [],
    children: [
      {
        _type: 'span',
        _key: `span_${i}`,
        text,
        marks: [],
      },
    ],
  }));
}

// ─── Upload image from URL ───
async function uploadImageUrl(url) {
  if (!url || url === '/images/no_picture.jpg') return null;
  try {
    const response = await fetch(url);
    if (!response.ok) return null;
    const buffer = Buffer.from(await response.arrayBuffer());
    const contentType = response.headers.get('content-type') || 'image/jpeg';
    const asset = await client.assets.upload('image', buffer, { contentType });
    console.log(`  ✓ Uploaded image: ${url.split('/').pop()}`);
    return {
      _type: 'image',
      asset: { _type: 'reference', _ref: asset._id },
    };
  } catch (err) {
    console.error(`  ✗ Failed to upload: ${url}`, err.message);
    return null;
  }
}

// ─── Seed Site Settings ───
async function seedSiteSettings() {
  console.log('\n── Seeding Site Settings ──');
  const webInfo = data.globalData.web_info;
  const logoUrl = data.globalData.pic_info?.[0]?.path;

  const doc = {
    _id: 'siteSettings',
    _type: 'siteSettings',
    companyName: webInfo.company || 'Beacon Stone Realty',
    address: webInfo.address || '',
    phone: webInfo.phone || '',
    email: webInfo.email || 'info@beacon-stone.com',
    heroVideoPlaybackId: 'YOUR_MUX_PLAYBACK_ID',
  };

  if (logoUrl) {
    const logoImage = await uploadImageUrl(logoUrl);
    if (logoImage) doc.logo = logoImage;
  }

  await client.createOrReplace(doc);
  console.log('✓ Site settings created');
}

// ─── Seed Agents ───
async function seedAgents() {
  console.log('\n── Seeding Agents ──');
  // ClassId 6 = Agents
  const agentIds = (data.newsListsByClassId['6'] || []).map((item) => item.id);
  const agents = [];

  for (let order = 0; order < agentIds.length; order++) {
    const id = agentIds[order];
    const item = data.newsById[String(id)];
    if (!item) continue;

    const name = item.title?.trim();
    if (!name) continue;

    const slug = name
      .toLowerCase()
      .replace(/[()]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');

    console.log(`  Seeding agent: ${name}`);

    const doc = {
      _id: `agent-${id}`,
      _type: 'agent',
      name,
      slug: { _type: 'slug', current: slug },
      title: item.keywords || 'Real Estate Advisor',
      phone: item.field?.phone || '',
      email: item.field?.real_estate_broker_email || '',
      bio: htmlToBlocks(item.field?.real_estate_broker_desc || item.content || ''),
      order,
    };

    if (item.thumbnail) {
      const photo = await uploadImageUrl(item.thumbnail);
      if (photo) doc.photo = photo;
    }

    await client.createOrReplace(doc);
    agents.push({ _id: doc._id, name, legacyId: id });
    console.log(`  ✓ Agent created: ${name}`);
  }

  return agents;
}

// ─── Seed Listings ───
async function seedListings(agents) {
  console.log('\n── Seeding Listings ──');
  // ClassId 5 = Properties
  const listingIds = (data.newsListsByClassId['5'] || []).map((item) => item.id);

  for (let order = 0; order < listingIds.length; order++) {
    const id = listingIds[order];
    const item = data.newsById[String(id)];
    if (!item) continue;

    const title = item.title?.trim();
    if (!title) continue;

    const slug = title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');

    console.log(`  Seeding listing: ${title}`);

    const doc = {
      _id: `listing-${id}`,
      _type: 'listing',
      title,
      slug: { _type: 'slug', current: slug },
      address: item.keywords || '',
      price: item.description || '',
      propertyType: item.field?.development_type?.toLowerCase() || 'condominium',
      totalResidences: item.field?.total_residences ? Number(item.field.total_residences) : undefined,
      description: htmlToBlocks(item.content || ''),
      highlights: stripHtml(item.field?.house_introduction || ''),
      developmentDetails: stripHtml(item.field?.development_details || ''),
      status: 'active',
      order,
    };

    // Try to match agent by real_estate_agent_id
    if (item.field?.real_estate_agent_id) {
      const matchedAgent = agents.find(
        (a) => String(a.legacyId) === String(item.field.real_estate_agent_id)
      );
      if (matchedAgent) {
        doc.agent = { _type: 'reference', _ref: matchedAgent._id };
      }
    }

    if (item.thumbnail) {
      const featuredImage = await uploadImageUrl(item.thumbnail);
      if (featuredImage) doc.featuredImage = featuredImage;
    }

    // Upload gallery images
    if (item.photo_album && item.photo_album.length > 0) {
      const gallery = [];
      for (const photoUrl of item.photo_album) {
        const img = await uploadImageUrl(photoUrl);
        if (img) gallery.push({ ...img, _key: `gallery_${gallery.length}` });
      }
      if (gallery.length > 0) doc.gallery = gallery;
    }

    await client.createOrReplace(doc);
    console.log(`  ✓ Listing created: ${title}`);
  }
}

// ─── Main ───
async function main() {
  console.log('Seeding Sanity CMS from site-content.json...');
  console.log(`Project: ${client.config().projectId} / ${client.config().dataset}`);

  await seedSiteSettings();
  const agents = await seedAgents();
  await seedListings(agents);

  console.log('\n✓ Seeding complete!');
  console.log('\nNext steps:');
  console.log('  1. Go to your Sanity Studio at /studio to review the data');
  console.log('  2. Update the Mux playback ID in Site Settings');
  console.log('  3. Review and enrich agent bios and listing descriptions');
}

main().catch((err) => {
  console.error('Seeding failed:', err);
  process.exit(1);
});
