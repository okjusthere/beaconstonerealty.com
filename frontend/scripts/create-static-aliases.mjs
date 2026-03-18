#!/usr/bin/env node

import { access, copyFile, mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import siteContent from '../data/site-content.json' with { type: 'json' };

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const outDir = path.join(projectRoot, 'out');

function routeToIndexPath(route) {
  const cleanRoute = route.replace(/^\/+|\/+$/g, '');
  return cleanRoute ? path.join(outDir, cleanRoute, 'index.html') : path.join(outDir, 'index.html');
}

async function fileExists(filePath) {
  try {
    await access(filePath);
    return true;
  } catch {
    return false;
  }
}

async function copyAlias(sourceRoute, aliasRoute) {
  const sourcePath = routeToIndexPath(sourceRoute);
  const aliasPath = routeToIndexPath(aliasRoute);

  if (!(await fileExists(sourcePath))) {
    throw new Error(`Source route "${sourceRoute}" was not exported at ${sourcePath}`);
  }

  await mkdir(path.dirname(aliasPath), { recursive: true });
  await copyFile(sourcePath, aliasPath);
}

async function main() {
  const aliases = [
    ['/properties', '/propertyCenter/5'],
    ['/brokers', '/realEstateBrokerCenter/6'],
    ['/contact', '/contact/57'],
    ['/sell-with-us', '/sale/38'],
  ];

  for (const item of siteContent.newsListsByClassId['5'] || []) {
    aliases.push([`/properties/${item.id}`, `/propertyCenterDetail/${item.id}`]);
  }

  for (const item of siteContent.newsListsByClassId['6'] || []) {
    aliases.push([`/brokers/${item.id}`, `/realEstateBrokerDetail/${item.id}`]);
  }

  for (const [sourceRoute, aliasRoute] of aliases) {
    await copyAlias(sourceRoute, aliasRoute);
  }

  process.stdout.write(`${aliases.length} static aliases created.\n`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
