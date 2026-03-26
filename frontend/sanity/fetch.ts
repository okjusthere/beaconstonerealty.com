import { publicClient } from './client';
import { sanityConfig } from './env';
import {
  siteSettingsQuery,
  allAgentsQuery,
  agentBySlugQuery,
  agentSlugsQuery,
  allListingsQuery,
  listingBySlugQuery,
  listingSlugsQuery,
  pageBySlugQuery,
} from './queries';

const isSanityConfigured = Boolean(sanityConfig.projectId);

async function fetchOrNull<T>(query: string, params?: Record<string, unknown>): Promise<T | null> {
  if (!isSanityConfigured) return null;
  try {
    return await publicClient.fetch<T>(query, params ?? {});
  } catch (err) {
    console.error('[Sanity] Fetch error:', err);
    return null;
  }
}

// ─── Site Settings ───
export async function getSiteSettings() {
  return fetchOrNull(siteSettingsQuery);
}

// ─── Agents ───
export async function getAllAgents() {
  return fetchOrNull<Array<Record<string, unknown>>>(allAgentsQuery);
}

export async function getAgentBySlug(slug: string) {
  return fetchOrNull(agentBySlugQuery, { slug });
}

export async function getAgentSlugs() {
  return fetchOrNull<Array<{ slug: string }>>(agentSlugsQuery);
}

// ─── Listings ───
export async function getAllListings() {
  return fetchOrNull<Array<Record<string, unknown>>>(allListingsQuery);
}

export async function getListingBySlug(slug: string) {
  return fetchOrNull(listingBySlugQuery, { slug });
}

export async function getListingSlugs() {
  return fetchOrNull<Array<{ slug: string }>>(listingSlugsQuery);
}

// ─── Pages ───
export async function getPageBySlug(slug: string) {
  return fetchOrNull(pageBySlugQuery, { slug });
}
