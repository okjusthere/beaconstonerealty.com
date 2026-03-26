import { createClient, type SanityClient } from '@sanity/client';
import imageUrlBuilder from '@sanity/image-url';
import { sanityConfig } from './env';

function createSanityClient(token?: string): SanityClient {
  if (!sanityConfig.projectId) {
    // Return a dummy client that won't make real requests
    // This allows the build to succeed without Sanity credentials
    return createClient({
      ...sanityConfig,
      projectId: 'placeholder',
      token,
    });
  }
  return createClient({ ...sanityConfig, token });
}

// Write client (with API token for form submissions)
export const sanityClient = createSanityClient(process.env.SANITY_API_TOKEN);

// Read-only client for public data fetching
export const publicClient = createSanityClient();

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function urlFor(source: any) {
  return imageUrlBuilder(publicClient).image(source);
}
