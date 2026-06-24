#!/usr/bin/env node

import { execFileSync } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');
const outputPath = path.join(projectRoot, 'data', 'site-content.json');
const legacySiteUrl = (process.env.LEGACY_SITE_URL || 'https://beaconstonerealty.com').replace(/\/$/, '');
const apiBase = `${legacySiteUrl}/application/index`;
const detailIdCeiling = Number(process.env.LEGACY_DETAIL_ID_CEILING || '100');

function runCurl(args) {
  return execFileSync('curl', ['-sS', ...args], {
    encoding: 'utf8',
    maxBuffer: 1024 * 1024 * 32,
  });
}

function fetchEnvelope(endpoint, options = {}) {
  const args = ['-H', 'X-Requested-With: XMLHttpRequest'];

  if (options.method === 'POST') {
    args.push(
      '-X',
      'POST',
      '-H',
      'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
      '--data',
      new URLSearchParams(options.data || {}).toString(),
    );
  }

  args.push(`${apiBase}/${endpoint}.php`);
  return JSON.parse(runCurl(args));
}

function unwrap(endpoint, envelope) {
  if (envelope?.code !== 200) {
    throw new Error(`${endpoint} failed: ${envelope?.message || 'unknown error'}`);
  }
  return envelope?.obj?.data ?? null;
}

function tryFetchNewsDetail(id) {
  const envelope = fetchEnvelope('news_detail', {
    method: 'POST',
    data: { id: String(id) },
  });

  if (envelope?.code !== 200) {
    return null;
  }

  const item = envelope?.obj?.data;
  if (!item || typeof item !== 'object' || Number(item.id) !== id) {
    return null;
  }

  return item;
}

function collectMenuLinkIds(menuItems, result = new Set()) {
  for (const item of menuItems || []) {
    if (item?.link_id) {
      result.add(Number(item.link_id));
    }
    if (item?.children?.length) {
      collectMenuLinkIds(item.children, result);
    }
  }
  return result;
}

function collectNewsIds(newsList, result = new Set()) {
  for (const item of newsList || []) {
    if (item?.id) {
      result.add(Number(item.id));
    }
  }
  return result;
}

async function main() {
  const globalData = unwrap('global', fetchEnvelope('global'));

  const classIds = Array.from(
    new Set((globalData?.news_class_info || []).map((item) => Number(item.id)).filter(Boolean)),
  ).sort((a, b) => a - b);

  const newsListsByClassId = {};
  const seedIds = collectMenuLinkIds(globalData?.menu_info);

  for (const classId of classIds) {
    const newsList = unwrap(
      'news_list',
      fetchEnvelope('news_list', {
        method: 'POST',
        data: { id: String(classId), top: '-1' },
      }),
    ) || [];

    newsListsByClassId[String(classId)] = newsList;
    collectNewsIds(newsList, seedIds);
  }

  const explicitIds = [1, 2, 3, 11, 13, 18, 19, 38, 39, 40, 41, 42, 50, 52, 53, 57, 58, 61];
  for (const id of explicitIds) {
    seedIds.add(id);
  }

  for (let id = 1; id <= detailIdCeiling; id += 1) {
    seedIds.add(id);
  }

  const newsById = {};
  const orderedIds = Array.from(seedIds).sort((a, b) => a - b);

  for (const id of orderedIds) {
    const item = tryFetchNewsDetail(id);
    if (item) {
      newsById[String(id)] = item;
    }
  }

  const productListsByClassId = {};
  const productList = unwrap(
    'product_list',
    fetchEnvelope('product_list', {
      method: 'POST',
      data: { id: '0', top: '-1' },
    }),
  ) || [];
  productListsByClassId.all = productList;

  const output = {
    generatedAt: new Date().toISOString(),
    sourceSiteUrl: legacySiteUrl,
    globalData,
    newsById,
    newsListsByClassId,
    productListsByClassId,
  };

  await mkdir(path.dirname(outputPath), { recursive: true });
  await writeFile(outputPath, JSON.stringify(output, null, 2));

  process.stdout.write(
    JSON.stringify(
      {
        outputPath,
        newsCount: Object.keys(newsById).length,
        newsClassCount: classIds.length,
        productCount: productList.length,
      },
      null,
      2,
    ) + '\n',
  );
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
