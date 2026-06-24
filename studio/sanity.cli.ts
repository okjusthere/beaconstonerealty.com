import { defineCliConfig } from 'sanity/cli';

const projectId = process.env.SANITY_STUDIO_PROJECT_ID || '3zakg65j';
const dataset = process.env.SANITY_STUDIO_DATASET || 'production';
const studioHost = process.env.SANITY_STUDIO_HOSTNAME;

export default defineCliConfig({
  api: {
    projectId,
    dataset,
  },
  ...(studioHost ? { studioHost } : {}),
  deployment: {
    appId: 'bquvqv5emaid6imcp706kmfy',
  },
});
