import { publicClient } from './client';
import { sanityConfig } from './env';
import {
  siteSettingsQuery,
  allAgentsQuery,
  allAgentsWithBioQuery,
  agentBySlugQuery,
  agentByIdQuery,
  agentSlugsQuery,
  agentIdsQuery,
  allListingsQuery,
  listingBySlugQuery,
  listingByIdQuery,
  listingSlugsQuery,
  listingIdsQuery,
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

export async function getAllAgentsWithBio() {
  return fetchOrNull<Array<Record<string, unknown>>>(allAgentsWithBioQuery);
}

export async function getAgentBySlug(slug: string) {
  return fetchOrNull<Record<string, unknown>>(agentBySlugQuery, { slug });
}

export async function getAgentById(id: string) {
  return fetchOrNull<Record<string, unknown>>(agentByIdQuery, { id });
}

export async function getAgentSlugs() {
  return fetchOrNull<Array<{ slug: string }>>(agentSlugsQuery);
}

export async function getAgentIds() {
  return fetchOrNull<Array<{ _id: string }>>(agentIdsQuery);
}

// ─── Listings ───
export async function getAllListings() {
  return fetchOrNull<Array<Record<string, unknown>>>(allListingsQuery);
}

export async function getListingBySlug(slug: string) {
  return fetchOrNull<Record<string, unknown>>(listingBySlugQuery, { slug });
}

export async function getListingById(id: string) {
  return fetchOrNull<Record<string, unknown>>(listingByIdQuery, { id });
}

export async function getListingSlugs() {
  return fetchOrNull<Array<{ slug: string }>>(listingSlugsQuery);
}

export async function getListingIds() {
  return fetchOrNull<Array<{ _id: string }>>(listingIdsQuery);
}

// ─── Pages ───
export async function getPageBySlug(slug: string) {
  return fetchOrNull(pageBySlugQuery, { slug });
}
