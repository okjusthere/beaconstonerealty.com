import { defineConfig } from 'sanity';
import { structureTool } from 'sanity/structure';
import { visionTool } from '@sanity/vision';
import { schemaTypes } from './schemaTypes';
import { structure } from './lib/structure';
import { singletonActions, singletonTypes } from './lib/singletons';

const projectId = process.env.SANITY_STUDIO_PROJECT_ID || '3zakg65j';
const dataset = process.env.SANITY_STUDIO_DATASET || 'production';

export default defineConfig({
  name: 'beaconstone',
  title: 'Beacon Stone Realty',
  projectId,
  dataset,
  plugins: [
    structureTool({ structure }),
    visionTool(),
  ],
  schema: {
    types: schemaTypes,
  },
  document: {
    newDocumentOptions: (prev, { creationContext }) =>
      creationContext.type === 'global'
        ? prev.filter((templateItem) => !singletonTypes.has(templateItem.templateId))
        : prev,
    actions: (prev, context) =>
      singletonTypes.has(context.schemaType)
        ? prev.filter((action) => action.action && singletonActions.has(action.action))
        : prev,
  },
});
